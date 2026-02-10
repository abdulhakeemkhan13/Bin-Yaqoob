<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Account Statement') }} - {{ $customer->name ?? '' }}</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
                margin: 0;
            }
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            padding: 40px;
            max-width: 1000px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .report-title h2 {
            margin: 0;
            font-size: 20px;
            color: #666;
        }

        .section-header {
            background-color: #222;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 25px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 3px 5px;
            font-size: 13px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 140px;
        }

        .colon {
            width: 10px;
        }

        .installment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .installment-table th {
            background-color: #f2f2f2;
            border: 1px solid #999;
            padding: 6px 4px;
            font-size: 11px;
            text-align: center;
        }

        .installment-table td {
            border: 1px solid #999;
            padding: 4px;
            font-size: 11px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 10px;
            text-align: right;
            color: #888;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">{{ __('Print') }}</button>

    <div class="header">
        <div class="company-info">
            <h1>{{ Utility::getValByName('company_name') }}</h1>
            <p>{{ $contract->re_project->name ?? '' }}</p>
        </div>
        <div class="report-title">
            <h2>Account Statement</h2>
        </div>
    </div>

    <div class="section-header">Member Details</div>
    <div class="details-grid">
        <table class="details-table">
            <tr>
                <td class="label">Name</td>
                <td class="colon">:</td>
                <td>{{ $customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Phone No</td>
                <td class="colon">:</td>
                <td>{{ $customer->contact ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">CNIC</td>
                <td class="colon">:</td>
                <td>{{ $customer->cnic_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Permanent Address</td>
                <td class="colon">:</td>
                <td>{{ $customer->permanent_address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Shares</td>
                <td class="colon">:</td>
                <td>100%</td>
            </tr>
        </table>
        <table class="details-table">
            <tr>
                <td class="label">S/O</td>
                <td class="colon">:</td>
                <td>{{ $customer->father_or_spouse_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td>{{ $customer->email ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Present Address</td>
                <td class="colon">:</td>
                <td>{{ $customer->current_address ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div
        style="display: flex; gap: 20px; background-color: #222; color: #fff; padding: 5px 10px; font-weight: bold; font-size: 14px; margin-bottom: 15px;">
        <div style="flex: 1;">File Details</div>
        <div style="flex: 1;">Financial Details</div>
    </div>
    <div class="details-grid">
        <table class="details-table">
            <tr>
                <td class="label">Project Name</td>
                <td class="colon">:</td>
                <td>{{ $contract->re_project->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Membership No</td>
                <td class="colon">:</td>
                <td>{{ $contract->contract_no ?? $contract->id }}</td>
            </tr>
            <tr>
                <td class="label">Size</td>
                <td class="colon">:</td>
                <td>{{ $contract->unit->covered_area ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tower</td>
                <td class="colon">:</td>
                <td>{{ $contract->tower->tower_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Floor No</td>
                <td class="colon">:</td>
                <td>{{ $contract->floor->floor_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Appartment No</td>
                <td class="colon">:</td>
                <td>{{ $contract->unit->unit_number ?? '-' }}</td>
            </tr>
        </table>
        <table class="details-table">
            <tr>
                <td class="label">Cost of Unit</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['cost_of_unit'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount Price</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['discount_price'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount on Installments</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['discount_installments'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Net Price</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['net_price'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Other Charges</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['other_charges'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Paid Amount</td>
                <td class="colon">:</td>
                <td style="font-weight: bold;">{{ number_format($financials['paid_amount'], 2) }}
                    @if ($financials['net_price'] > 0)
                        ({{ round(($financials['paid_amount'] / $financials['net_price']) * 100) }}%)
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Remaining Amount</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['remaining_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Overdue Amount</td>
                <td class="colon">:</td>
                <td style="color: red;">{{ number_format($financials['overdue_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Adjusted Amount</td>
                <td class="colon">:</td>
                <td>{{ number_format($financials['adjusted_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label" style="font-weight: 800; border-top: 1px solid #000;">Remaining Amount</td>
                <td class="colon" style="border-top: 1px solid #000;">:</td>
                <td style="font-weight: 800; border-top: 1px solid #000;">
                    {{ number_format($financials['remaining_amount'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section-header">Installment Details</div>
    <table class="installment-table">
        <thead>
            <tr>
                <th width="30">S No.</th>
                <th>Narration</th>
                <th width="80">Due Date</th>
                <th width="100">Due Amount</th>
                <th width="80">Paid Date</th>
                <th width="100">Paid Amount</th>
                <th width="120">Receipt No</th>
                <th width="100">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sno = 1;
                $totalDue = 0;
                $totalPaid = 0;
            @endphp
            @foreach ($contract->installments as $installment)
                @php
                    $payments = $installment->invoice ? $installment->invoice->payments : collect();
                    $installmentPaid = $payments->sum('amount');
                    $rowspan = $payments->count() > 0 ? $payments->count() : 1;
                    $totalDue += $installment->amount;
                    $totalPaid += $installmentPaid;
                @endphp
                <tr>
                    <td rowspan="{{ $rowspan }}" class="text-center">{{ $sno++ }}</td>
                    <td rowspan="{{ $rowspan }}">
                        {{ $installment->description ?? ($installment->installment_type == 'down_payment' ? __('Booking Charges') : __('Installment') . ' ' . $installment->installment_number) }}
                    </td>
                    <td rowspan="{{ $rowspan }}" class="text-center">
                        {{ $installment->due_date ? $installment->due_date->format('d-m-Y') : '-' }}</td>
                    <td rowspan="{{ $rowspan }}" class="text-end">{{ number_format($installment->amount, 2) }}
                    </td>

                    @if ($payments->count() > 0)
                        @foreach ($payments as $index => $payment)
                            @if ($index > 0)
                </tr>
                <tr>
            @endif
            <td class="text-center">{{ $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d-m-Y') : '-' }}
            </td>
            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
            <td class="text-center">{{ $payment->voucher->journal_id ?? ($payment->reference ?? '-') }}</td>
            @if ($index == 0)
                <td rowspan="{{ $rowspan }}" class="text-end" style="vertical-align: middle;">
                    {{ number_format($installment->amount - $installmentPaid, 2) }}</td>
            @endif
            @endforeach
        @else
            <td class="text-center"></td>
            <td class="text-end"></td>
            <td class="text-center"></td>
            <td class="text-end" style="vertical-align: middle;">{{ number_format($installment->amount, 2) }}</td>
            @endif
            </tr>
            @endforeach
            @if ($contract->other_charges > 0)
                <tr style="background-color: #f8f9fa;">
                    <td rowspan="{{ $rowspan }}" class="text-center">{{ $sno++ }}</td>
                    <td colspan="2" style="font-weight: bold; text-align: left;"> Possession Charge</td>
                    <td class="text-end" style="font-weight: bold; ">
                        {{ number_format($contract->other_charges, 2) }}
                    </td>
                    <td colspan="3" style="font-size: 10px; ">
                        {{ $contract->possession_charge_percentage }}% possession charge
                    </td>
                    <td rowspan="{{ $rowspan }}" class="text-end" style="vertical-align: middle;">
                        {{ number_format($contract->possession_charge_percentage, 2) }}</td>
                    @php
                        $totalDue += $contract->other_charges;
                    @endphp
                </tr>
            @endif
            @if ($contract->discount_amount > 0)
                <tr style="background-color: #f8f9fa;">
                    <td rowspan="{{ $rowspan }}" class="text-center">{{ $sno++ }}</td>
                    <td colspan="2" style="font-weight: bold; text-align: left;"> Discount</td>
                    <td class="text-end" style="font-weight: bold; ">
                        {{ number_format($contract->discount_amount, 2) }}
                    </td>
                    <td colspan="3" style="font-size: 10px; ">
                        @if ($contract->discount_type == 'percentage')
                            {{ $contract->discount_value }}% percentage discount
                        @else
                            Fixed amount discount
                        @endif
                    </td>
                    <td rowspan="{{ $rowspan }}" class="text-end" style="vertical-align: middle;">
                        {{ number_format(0, 2) }}</td>
                </tr>
            @endif

        </tbody>
        <tfoot>
            @php
                // Subtract discount from total instead of adding possession charge
                if ($contract->discount_amount > 0) {
                    $totalDue ;
                }
            @endphp
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="3" class="text-end">TOTAL</td>
                <td class="text-end">{{ number_format($totalDue, 2) }}</td>
                <td></td>
                <td class="text-end">{{ number_format($totalPaid, 2) }}</td>
                <td></td>
                <td class="text-end">{{ number_format($totalDue - $totalPaid, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
        page 1 / 1
    </div>
</body>

</html>
