<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FineCalculationService;

class ApplyInstallmentFines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:apply-fines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply fines to overdue installments based on contract configuration';

    /**
     * Execute the console command.
     */
    public function handle(FineCalculationService $fineService)
    {
        $this->info('Starting fine application process...');

        $stats = $fineService->applyFinesToAllOverdueInstallments();

        $this->info("Fine application completed:");
        $this->info("  - Total installments processed: {$stats['total_processed']}");
        $this->info("  - Fines applied: {$stats['fines_applied']}");
        $this->info("  - Total fine amount: " . number_format($stats['total_fine_amount'], 2));

        return Command::SUCCESS;
    }
}
