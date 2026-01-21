<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractInstallment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateContractInstallments extends Command
{
    protected $signature = 'contracts:recalculate-installments {--contract= : Specific contract ID to recalculate}';
    protected $description = 'Recalculate installment schedules for existing contracts';

    public function handle()
    {
        $contractId = $this->option('contract');
        
        $query = Contract::with('installment_plan')
            ->whereHas('installment_plan')
            ->where('payment_plan_type', 'Installment');
        
        if ($contractId) {
            $query->where('id', $contractId);
        }
        
        $contracts = $query->get();
        
        if ($contracts->isEmpty()) {
            $this->info('No contracts with installment plans found.');
            return 0;
        }
        
        $this->info("Found {$contracts->count()} contract(s) to recalculate.");
        
        foreach ($contracts as $contract) {
            $this->recalculateInstallments($contract);
        }
        
        $this->info('Done! All installments have been recalculated.');
        return 0;
    }
    
    private function recalculateInstallments(Contract $contract)
    {
        $plan = $contract->installment_plan;
        
        if (!$plan) {
            $this->warn("Contract #{$contract->id} has no installment plan. Skipping.");
            return;
        }
        
        // Delete existing installments
        $deleted = ContractInstallment::where('contract_id', $contract->id)->delete();
        $this->line("Contract #{$contract->id}: Deleted {$deleted} old installments.");
        
        // Determine frequency in months
        $frequency = $plan->installment_frequency ?? 'Monthly';
        $frequencyMonths = match($frequency) {
            'Monthly' => 1,
            'Quarterly' => 3,
            'Half-Yearly', '6 Months' => 6,
            'Yearly' => 12,
            default => 1,
        };
        
        $startDate = $plan->plan_start_date 
            ? Carbon::parse($plan->plan_start_date)
            : Carbon::now();
        
        $installmentNumber = 1;
        $totalPayable = $plan->total_payable ?? $contract->sale_price ?? $contract->value;
        $downPayment = $plan->down_payment_amount ?? 0;
        
        // 1. Create Down Payment installment
        if ($downPayment > 0) {
            ContractInstallment::create([
                'contract_id' => $contract->id,
                'unit_id' => $contract->unit_id,
                'installment_number' => $installmentNumber,
                'installment_type' => 'down_payment',
                'amount' => $downPayment,
                'issue_date' => $startDate->copy(),
                'due_date' => $plan->down_payment_due_date 
                    ? Carbon::parse($plan->down_payment_due_date)
                    : $startDate->copy()->addDays(7),
                'status' => 'pending',
                'description' => 'Down Payment',
                'created_by' => $contract->created_by,
                'owned_by' => $contract->owned_by,
            ]);
            $installmentNumber++;
        }
        
        // 2. Generate remaining installments
        $numInstallments = $plan->installment_count ?? 0;
        $remainingAmount = $totalPayable - $downPayment;
        $installmentAmount = $numInstallments > 0 ? round($remainingAmount / $numInstallments, 2) : 0;
        $totalDisbursed = $downPayment;
        
        for ($i = 0; $i < $numInstallments; $i++) {
            $issueDate = $startDate->copy()->addMonths($frequencyMonths * $i);
            $dueDate = $issueDate->copy()->addDays(15);
            
            // Last installment absorbs rounding difference
            $amount = $installmentAmount;
            if ($i == $numInstallments - 1) {
                $amount = round($totalPayable - $totalDisbursed, 2);
            }
            $totalDisbursed += $amount;
            
            ContractInstallment::create([
                'contract_id' => $contract->id,
                'unit_id' => $contract->unit_id,
                'installment_number' => $installmentNumber,
                'installment_type' => 'installment',
                'amount' => $amount,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'pending',
                'description' => 'Installment #' . ($i + 1),
                'created_by' => $contract->created_by,
                'owned_by' => $contract->owned_by,
            ]);
            $installmentNumber++;
        }
        
        $this->info("Contract #{$contract->id}: Created " . ($installmentNumber - 1) . " new installments. Total: {$totalPayable}");
    }
}
