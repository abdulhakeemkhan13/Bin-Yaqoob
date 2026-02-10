<?php

namespace App\Services;

use App\Models\ContractInstallment;
use App\Models\Contract;
use Carbon\Carbon;

/**
 * Service class for calculating and applying fines to overdue installments
 */
class FineCalculationService
{
    /**
     * Calculate fine for a specific installment based on contract settings
     *
     * @param ContractInstallment $installment
     * @return float The calculated fine amount
     */
    public function calculateFineForInstallment(ContractInstallment $installment): float
    {
        // Only calculate if installment is not paid
        if ($installment->status === 'paid') {
            return 0;
        }

        $contract = $installment->contract;
        
        // Check if contract has fine configuration
        if (!$contract || !$contract->fine_percentage || !$contract->fine_apply_after_due_date) {
            return 0;
        }

        // Calculate days overdue
        $daysOverdue = $this->getDaysOverdue($installment);
        
        // Check if installment is past the grace period
        if ($daysOverdue < $contract->fine_apply_after_due_date) {
            return 0;
        }

        // Calculate fine based on frequency
        if ($contract->fine_frequency === Contract::FINE_FREQUENCY_ONE_TIME) {
            return $this->calculateOneTimeFine($installment, $contract);
        } else {
            return $this->calculateMonthlyRecurringFine($installment, $contract);
        }
    }

    /**
     * Apply fines to an installment and save it
     *
     * @param ContractInstallment $installment
     * @return bool Whether fine was applied
     */
    public function applyFineToInstallment(ContractInstallment $installment): bool
    {
        $fine = $this->calculateFineForInstallment($installment);

        if ($fine > 0) {
            $installment->fine_amount = $fine;
            
            if (!$installment->fine_applied_date) {
                $installment->fine_applied_date = now();
            }
            
            $installment->last_fine_calculation_date = now();
            $installment->save();
            
            return true;
        }

        return false;
    }

    /**
     * Apply fines to all overdue installments
     *
     * @return array Statistics about fine application
     */
    public function applyFinesToAllOverdueInstallments(): array
    {
        $overdueInstallments = ContractInstallment::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->with('contract')
            ->get();

        $stats = [
            'total_processed' => 0,
            'fines_applied' => 0,
            'total_fine_amount' => 0,
        ];

        foreach ($overdueInstallments as $installment) {
            $stats['total_processed']++;
            
            if ($this->applyFineToInstallment($installment)) {
                $stats['fines_applied']++;
                $stats['total_fine_amount'] += $installment->fine_amount;
            }
        }

        return $stats;
    }

    /**
     * Calculate one-time fine
     *
     * @param ContractInstallment $installment
     * @param Contract $contract
     * @return float
     */
    private function calculateOneTimeFine(ContractInstallment $installment, Contract $contract): float
    {
        // Apply fine only if not already applied
        if ($installment->fine_applied_date) {
            return $installment->fine_amount;
        }

        $fine = ($installment->amount * $contract->fine_percentage) / 100;
        return round($fine, 2);
    }

    /**
     * Calculate monthly recurring fine
     *
     * @param ContractInstallment $installment
     * @param Contract $contract
     * @return float
     */
    private function calculateMonthlyRecurringFine(ContractInstallment $installment, Contract $contract): float
    {
        // Calculate how many months overdue since last calculation
        $lastCalculation = $installment->last_fine_calculation_date 
            ? Carbon::parse($installment->last_fine_calculation_date)
            : Carbon::parse($installment->due_date)->addDays($contract->fine_apply_after_due_date);

        $monthsOverdue = $this->getMonthsDifference($lastCalculation, now());

        if ($monthsOverdue > 0) {
            $monthlyFine = ($installment->amount * $contract->fine_percentage) / 100;
            $additionalFine = $monthlyFine * $monthsOverdue;
            
            return round($installment->fine_amount + $additionalFine, 2);
        }

        return $installment->fine_amount;
    }

    /**
     * Get number of days overdue for an installment
     *
     * @param ContractInstallment $installment
     * @return int
     */
    private function getDaysOverdue(ContractInstallment $installment): int
    {
        if (!$installment->due_date) {
            return 0;
        }

        $dueDate = Carbon::parse($installment->due_date);
        $now = now();

        if ($now->lte($dueDate)) {
            return 0;
        }

        return $now->diffInDays($dueDate);
    }

    /**
     * Get number of complete months between two dates
     *
     * @param Carbon $from
     * @param Carbon $to
     * @return int
     */
    private function getMonthsDifference(Carbon $from, Carbon $to): int
    {
        if ($to->lt($from)) {
            return 0;
        }

        return $from->diffInMonths($to);
    }

    /**
     * Get total fine for a contract (all installments)
     *
     * @param Contract $contract
     * @return float
     */
    public function getTotalFineForContract(Contract $contract): float
    {
        return $contract->installments()->sum('fine_amount');
    }
}
