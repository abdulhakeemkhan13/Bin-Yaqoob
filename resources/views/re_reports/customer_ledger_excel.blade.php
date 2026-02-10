<table>
    @foreach ($customers as $customer)
        @foreach ($customer->contracts as $contract)
            <thead>
                <tr>
                    <th colspan="8" style="background-color: #f8f9fa; font-weight: bold;">
                        {{ __('Customer') }}: {{ $customer->name }} |
                        {{ __('Contract #') }}: {{ \Auth::user()->contractNumberFormat($contract->id) }} |
                        {{ __('Project') }}: {{ $contract->re_project->name ?? '-' }} |
                        {{ __('Unit') }}: {{ $contract->unit->unit_number ?? '-' }}
                    </th>
                </tr>
                <tr>
                    <th style="font-weight: bold;">{{ __('S No.') }}</th>
                    <th style="font-weight: bold;">{{ __('Narration') }}</th>
                    <th style="font-weight: bold;">{{ __('Due Date') }}</th>
                    <th style="font-weight: bold;">{{ __('Due Amount') }}</th>
                    <th style="font-weight: bold;">{{ __('Paid Date') }}</th>
                    <th style="font-weight: bold;">{{ __('Paid Amount') }}</th>
                    <th style="font-weight: bold;">{{ __('Receipt No') }}</th>
                    <th style="font-weight: bold;">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sno = 1;
                    $totalContractDue = 0;
                    $totalContractPaid = 0;
                @endphp
                @foreach ($contract->installments as $installment)
                    @php
                        $payments = $installment->invoice ? $installment->invoice->payments : collect();
                        $installmentPaid = $payments->sum('amount');
                        $totalContractDue += $installment->amount;
                        $totalContractPaid += $installmentPaid;
                        $rowspan = $payments->count() > 0 ? $payments->count() : 1;
                    @endphp
                    @if ($payments->count() > 0)
                        @foreach ($payments as $index => $payment)
                            <tr>
                                @if ($index == 0)
                                    <td rowspan="{{ $rowspan }}">{{ $sno++ }}</td>
                                    <td rowspan="{{ $rowspan }}">
                                        {{ $installment->description ?? ($installment->installment_type == 'down_payment' ? __('Booking Charges') : __('Installment') . ' ' . $installment->installment_number) }}
                                    </td>
                                    <td rowspan="{{ $rowspan }}">
                                        {{ $installment->due_date ? $installment->due_date->format('d M, Y') : '-' }}
                                    </td>
                                    <td rowspan="{{ $rowspan }}">{{ $installment->amount }}</td>
                                @endif
                                <td>{{ $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d M, Y') : '-' }}
                                </td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ $payment->voucher->journal_id ?? ($payment->reference ?? '-') }}</td>
                                @if ($index == 0)
                                    <td rowspan="{{ $rowspan }}">{{ $installment->amount - $installmentPaid }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $sno++ }}</td>
                            <td>{{ $installment->description ?? ($installment->installment_type == 'down_payment' ? __('Booking Charges') : __('Installment') . ' ' . $installment->installment_number) }}
                            </td>
                            <td>{{ $installment->due_date ? $installment->due_date->format('d M, Y') : '-' }}</td>
                            <td>{{ $installment->amount }}</td>
                            <td>-</td>
                            <td>0</td>
                            <td>-</td>
                            <td>{{ $installment->amount }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">{{ __('Total') }}</td>
                    <td style="font-weight: bold;">{{ $totalContractDue }}</td>
                    <td></td>
                    <td style="font-weight: bold;">{{ $totalContractPaid }}</td>
                    <td></td>
                    <td style="font-weight: bold;">{{ $totalContractDue - $totalContractPaid }}</td>
                </tr>
                <tr>
                    <td colspan="8"></td>
                </tr> <!-- Spacer -->
            </tbody>
        @endforeach
    @endforeach
</table>
