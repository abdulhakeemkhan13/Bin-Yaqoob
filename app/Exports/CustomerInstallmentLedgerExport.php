<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class CustomerInstallmentLedgerExport implements FromView
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function view(): View
    {
        return view('re_reports.customer_ledger_excel', [
            'customers' => $this->customers
        ]);
    }
}
