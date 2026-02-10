@extends('re_reports.layout', ['title' => __('Customer Installment Ledger Report')])

@section('report-table')
    <form action="" method="GET" class="mb-4 no-print" style="margin-left: 20px;">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Project') }}</label>
                <select name="project_id" class="form-control select2">
                    <option value="all" {{ request('project_id') == 'all' ? 'selected' : '' }}>{{ __('All Projects') }}
                    </option>
                    @foreach ($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Customer') }}</label>
                <select name="customer_id" class="form-control select2">
                    <option value="all" {{ request('customer_id') == 'all' ? 'selected' : '' }}>{{ __('All Customers') }}
                    </option>
                    @foreach ($customers_list as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <button type="submit" name="export" value="excel" class="btn btn-success"><i
                        class="ti ti-file-export"></i> {{ __('Excel') }}</button>
                <a href="{{ route('re-reports.customer-ledger') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>

    @foreach ($customers as $customer)
        @foreach ($customer->contracts as $contract)
            <div class="card mb-4 border shadow-none">
                <div class="card-header bg-light py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0"><strong>{{ __('Customer') }}:</strong> {{ $customer->name }}</h6>
                            <small><strong>{{ __('Contract #') }}:</strong>
                                {{ \Auth::user()->contractNumberFormat($contract->id) }}</small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('re-reports.customer-statement-print', $contract->id) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary no-print mb-2">
                                <i class="ti ti-printer"></i> {{ __('Statement') }}
                            </a>
                            <h6 class="mb-0"><strong>{{ __('Project') }}:</strong>
                                {{ $contract->re_project->name ?? '-' }}</h6>
                            <small><strong>{{ __('Unit') }}:</strong> {{ $contract->unit->unit_number ?? '-' }}
                                ({{ $contract->tower->tower_name ?? '-' }} /
                                {{ $contract->floor->floor_name ?? '-' }})
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr class="bg-gray-100 text-center">
                                    <th width="50">{{ __('S No.') }}</th>
                                    <th>{{ __('Narration') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th class="text-end">{{ __('Due Amount') }}</th>
                                    <th>{{ __('Paid Date') }}</th>
                                    <th class="text-end">{{ __('Paid Amount') }}</th>
                                    <th>{{ __('Receipt No') }}</th>
                                    <th class="text-end">{{ __('Balance') }}</th>
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
                                    <tr>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $sno++ }}</td>
                                        <td rowspan="{{ $rowspan }}">
                                            {{ $installment->description ?? ($installment->installment_type == 'down_payment' ? __('Booking Charges') : __('Installment') . ' ' . $installment->installment_number) }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">
                                            {{ $installment->due_date ? $installment->due_date->format('d M, Y') : '-' }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="text-end font-weight-bold">
                                            {{ \Auth::user()->priceFormat($installment->amount) }}</td>

                                        @if ($payments->count() > 0)
                                            @foreach ($payments as $index => $payment)
                                                @if ($index > 0)
                                    </tr>
                                    <tr>
                                @endif
                                <td class="text-center">
                                    {{ $payment->date ? \Carbon\Carbon::parse($payment->date)->format('d M, Y') : '-' }}
                                </td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($payment->amount) }}</td>
                                <td class="text-center">{{ $payment->voucher->journal_id ?? ($payment->reference ?? '-') }}
                                </td>
                                @if ($index == 0)
                                    <td rowspan="{{ $rowspan }}" class="text-end">
                                        {{ \Auth::user()->priceFormat($installment->amount - $installmentPaid) }}</td>
                                @endif
        @endforeach
    @else
        <td class="text-center">-</td>
        <td class="text-end">{{ \Auth::user()->priceFormat(0) }}</td>
        <td class="text-center">-</td>
        <td class="text-end font-weight-bold">{{ \Auth::user()->priceFormat($installment->amount) }}</td>
    @endif
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="3" class="text-end"><strong>{{ __('Total') }}</strong></td>
            <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalContractDue) }}</strong></td>
            <td></td>
            <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalContractPaid) }}</strong></td>
            <td></td>
            <td class="text-end text-danger">
                <strong>{{ \Auth::user()->priceFormat($totalContractDue - $totalContractPaid) }}</strong>
            </td>
        </tr>
    </tfoot>
    </table>
    </div>
    </div>
    </div>
    @endforeach
    @endforeach
@endsection
