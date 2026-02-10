@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Manage Customer-Detail') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">{{ __('Customer') }}</a></li>
    <li class="breadcrumb-item">{{ $customer['name'] }}</li>
@endsection
@push('script-page')
    <script>
        function copyToClipboard(element) {

            var copyText = element.id;
            navigator.clipboard.writeText(copyText);
            // document.addEventListener('copy', function (e) {
            //     e.clipboardData.setData('text/plain', copyText);
            //     e.preventDefault();
            // }, true);
            //
            // document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        @can('create invoice')
            <a href="{{ route('invoice.create', $customer->id) }}" class="btn btn-sm btn-primary">
                {{ __('Create Invoice') }}
            </a>
        @endcan
        {{-- @can('create proposal')
            <a href="{{ route('proposal.create', $customer->id) }}" class="btn btn-sm btn-primary">
                {{ __('Create Proposal') }}
            </a>
        @endcan --}}

        @can('edit customer')
            <a href="#" data-size="lg" data-url="{{ route('customer.edit', $customer['id']) }}" data-ajax-popup="true"
                title="{{ __('Edit Customer') }}" data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan

        {{-- @can('delete customer')
            {!! Form::open([
                'method' => 'DELETE',
                'class' => 'delete-form-btn',
                'route' => ['customer.destroy', $customer['id']],
            ]) !!}
            <a href="#" data-bs-toggle="tooltip" title="{{ __('Delete Customer') }}"
                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                data-confirm-yes="document.getElementById('delete-form-{{ $customer['id'] }}').submit();"
                class="btn btn-sm btn-danger bs-pass-para">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        @endcan --}}
    </div>
@endsection

@section('content')
    {{-- <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4 mb-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Customer Info') }}</h5>
                    <p class="card-text mb-0">{{ $customer['name'] }}</p>
                    <p class="card-text mb-0">{{ $customer['email'] }}</p>
                    <p class="card-text mb-0">{{ $customer['contact'] }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-4 col-xl-4 mb-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Billing Info') }}</h5>
                    <p class="card-text mb-0">{{ $customer['billing_name'] }}</p>
                    <p class="card-text mb-0">{{ $customer['billing_address'] }}</p>
                    <p class="card-text mb-0">
                        {{ $customer['billing_city'] . ', ' . $customer['billing_state'] . ', ' . $customer['billing_zip'] }}
                    </p>
                    <p class="card-text mb-0">{{ $customer['billing_country'] }}</p>
                    <p class="card-text mb-0">{{ $customer['billing_phone'] }}</p>
                </div>
            </div>

        </div>
        <div class="col-md-4 col-lg-4 col-xl-4 mb-4">
            <div class="card customer-detail-box customer_card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Shipping Info') }}</h5>
                    <p class="card-text mb-0">{{ $customer['shipping_name'] }}</p>
                    <p class="card-text mb-0">{{ $customer['shipping_address'] }}</p>
                    <p class="card-text mb-0">
                        {{ $customer['shipping_city'] . ', ' . $customer['shipping_state'] . ', ' . $customer['shipping_zip'] }}
                    </p>
                    <p class="card-text mb-0">{{ $customer['shipping_country'] }}</p>
                    <p class="card-text mb-0">{{ $customer['shipping_phone'] }}</p>
                </div>
            </div>

        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Customer Info') }}</h5>

                    <div class="row">
                        @php
                            $totalInvoiceSum = $customer->customerTotalInvoiceSum($customer['id']);
                            $totalInvoice = $customer->customerTotalInvoice($customer['id']);
                            $averageSale = $totalInvoiceSum != 0 ? $totalInvoiceSum / $totalInvoice : 0;
                        @endphp
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{ __('Customer Id') }}</p>
                                <h6 class="report-text mb-3">
                                    {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}</h6>
                                <p class="card-text mb-0">{{ __('Total Sum of Invoices') }}</p>
                                <h6 class="report-text mb-0">{{ \Auth::user()->priceFormat($totalInvoiceSum) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{ __('Date of Creation') }}</p>
                                <h6 class="report-text mb-3">{{ \Auth::user()->dateFormat($customer['created_at']) }}</h6>
                                <p class="card-text mb-0">{{ __('Quantity of Invoice') }}</p>
                                <h6 class="report-text mb-0">{{ $totalInvoice }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{ __('Balance') }}</p>
                                <h6 class="report-text mb-3">{{ \Auth::user()->priceFormat($customer['balance']) }}</h6>
                                <p class="card-text mb-0">{{ __('Average Amount') }}</p>
                                <h6 class="report-text mb-0">{{ \Auth::user()->priceFormat($averageSale) }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{ __('Overdue') }}</p>
                                <h6 class="report-text mb-3">
                                    {{ \Auth::user()->priceFormat($customer->customerOverdue($customer['id'])) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class="d-inline-block mb-5">{{ __('Proposal') }}</h5>

                    <div class="table-responsive">
                        <table class="table ">
                            <thead>
                                <tr>
                                    <th>{{ __('Proposal') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <th width="10%"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->customerProposal($customer->id) as $proposal)
                                    <tr>
                                        <td class="Id">
                                            <a href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}"
                                                class="btn btn-outline-primary">{{ AUth::user()->proposalNumberFormat($proposal->proposal_id) }}
                                            </a>
                                        </td>
                                        <td>{{ Auth::user()->dateFormat($proposal->issue_date) }}</td>
                                        <td>{{ Auth::user()->priceFormat($proposal->getTotal()) }}</td>
                                        <td>
                                            @if ($proposal->status == 0)
                                                <span
                                                    class="badge bg-primary p-2 px-3 rounded status_badge">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                            @elseif($proposal->status == 1)
                                                <span
                                                    class="badge bg-warning p-2 px-3 rounded status_badge">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                            @elseif($proposal->status == 2)
                                                <span
                                                    class="badge bg-danger p-2 px-3 rounded status_badge">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                            @elseif($proposal->status == 3)
                                                <span
                                                    class="badge bg-info p-2 px-3 rounded status_badge">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                            @elseif($proposal->status == 4)
                                                <span
                                                    class="badge bg-primary p-2 px-3 rounded status_badge">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                            <td class="Action">
                                                <span>
                                                    @if ($proposal->is_convert == 0)
                                                        @can('convert invoice')
                                                            <div class="action-btn bg-warning ms-2">
                                                                {!! Form::open([
                                                                    'method' => 'get',
                                                                    'route' => ['proposal.convert', $proposal->id],
                                                                    'id' => 'proposal-form-' . $proposal->id,
                                                                ]) !!}
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip"
                                                                    data-original-title="{{ __('Convert to Invoice') }}"
                                                                    title="{{ __('Convert to Invoice') }}"
                                                                    data-confirm="You want to confirm convert to invoice. Press Yes to continue or Cancel to go back"
                                                                    data-confirm-yes="document.getElementById('proposal-form-{{ $proposal->id }}').submit();">
                                                                    <i class="ti ti-exchange text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    @else
                                                        @can('convert invoice')
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="{{ route('invoice.show', \Crypt::encrypt($proposal->converted_invoice_id)) }}"
                                                                    class="mx-3 btn btn-sm  align-items-center"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Already convert to Invoice') }}"
                                                                    data-original-title="{{ __('Already convert to Invoice') }}">
                                                                    <i class="ti ti-file text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                    @endif
                                                    @can('duplicate proposal')
                                                        <div class="action-btn bg-primary ms-2">
                                                            {!! Form::open([
                                                                'method' => 'get',
                                                                'route' => ['proposal.duplicate', $proposal->id],
                                                                'id' => 'duplicate-form-' . $proposal->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip"
                                                                data-original-title="{{ __('Duplicate') }}"
                                                                title="{{ __('Duplicate Proposal') }}"
                                                                data-confirm="You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back"
                                                                data-confirm-yes="document.getElementById('duplicate-form-{{ $proposal->id }}').submit();">
                                                                <i class="ti ti-copy text-white text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                    @can('show proposal')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('proposal.show', \Crypt::encrypt($proposal->id)) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                                data-original-title="{{ __('Detail') }}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit proposal')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route('proposal.edit', \Crypt::encrypt($proposal->id)) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                data-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('delete proposal')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['proposal.destroy', $proposal->id],
                                                                'id' => 'delete-form-' . $proposal->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="Delete"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $proposal->id }}').submit();">
                                                                <i class="ti ti-trash text-white text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}


    {{-- Units & Installment Plans Section --}}
    @if ($customer->contracts->count() > 0)
        @foreach ($customer->contracts as $contract)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- Member Details --}}
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center mb-3">
                                    <div class="col-6">
                                        <h5 class="mb-0"><strong>{{ __('Member Details') }}</strong></h5>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="{{ route('re-reports.customer-statement-print', $contract->id) }}"
                                            target="_blank" class="btn btn-sm btn-outline-primary no-print">
                                            <i class="ti ti-printer"></i> {{ __('Statement') }}
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr>
                                                <td width="40%"><strong>{{ __('Name') }} :</strong></td>
                                                <td>{{ $customer->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('Phone No') }} :</strong></td>
                                                <td>{{ $customer->contact ?? ($customer->mobile_primary ?? '-') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('CNIC') }} :</strong></td>
                                                <td>{{ $customer->cnic_number ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('Permanent Address') }} :</strong></td>
                                                <td>{{ $customer->permanent_address ?? ($customer->billing_address ?? '-') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('Shares') }} :</strong></td>
                                                <td>100%</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr>
                                                <td width="40%"><strong>{{ __('S/O') }} :</strong></td>
                                                <td>{{ $customer->father_or_spouse_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('Email') }} :</strong></td>
                                                <td>{{ $customer->email ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('Present Address') }} :</strong></td>
                                                <td>{{ $customer->current_address ?? ($customer->shipping_address ?? '-') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- File Details & Financial Details --}}
                            <div class="row border-bottom pb-3 mb-3">
                                <div class="col-md-5">
                                    <h5 class="mb-3"><strong>{{ __('File Details') }}</strong></h5>
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td width="45%"><strong>{{ __('Project Name') }} :</strong></td>
                                            <td>{{ $contract->re_project->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Contract No') }} :</strong></td>
                                            <td>{{ $contract->contract_no ?? $contract->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Size') }} :</strong></td>
                                            <td>{{ $contract->unit->covered_area ?? '-' }}
                                                {{ $contract->unit ? 'Sq.Ft' : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Tower') }} :</strong></td>
                                            <td>{{ $contract->tower->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Floor No') }} :</strong></td>
                                            <td>{{ $contract->floor->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Apartment No') }} :</strong></td>
                                            <td>{{ $contract->unit->unit_number ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-3"><strong>{{ __('Financial Details') }}</strong></h5>
                                    @php
                                        $totalInstallmentAmount = $contract->installments->sum('amount');
                                        $paidInstallmentAmount = $contract->installments
                                            ->where('status', 'paid')
                                            ->sum('amount');
                                        $overdueAmount = $contract->installments
                                            ->filter(function ($i) {
                                                return $i->status != 'paid' && $i->due_date && $i->due_date < now();
                                            })
                                            ->sum('amount');
                                        $remainingAmount = $totalInstallmentAmount - $paidInstallmentAmount;
                                        $discountAmount =
                                            ($contract->sale_price ?? 0) - ($contract->net_sale_price ?? 0);
                                    @endphp
                                    <div class="row">
                                        <div class="col-6">
                                            <table class="table table-borderless table-sm mb-0">
                                                <tr>
                                                    <td width="60%"><strong>{{ __('Cost of Unit') }} :</strong></td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($contract->sale_price ?? 0) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Discount Price') }} :</strong></td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($discountAmount) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Net Price') }} :</strong></td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($contract->net_sale_price ?? 0) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Other Charges') }} :</strong></td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($contract->other_charges ?? 0) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-6">
                                            <table class="table table-borderless table-sm mb-0">
                                                <tr>
                                                    <td width="60%"><strong>{{ __('Paid Amount') }} :</strong></td>
                                                    <td class="text-end text-success">
                                                        {{ \Auth::user()->priceFormat($paidInstallmentAmount) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Remaining Amount') }} :</strong></td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($remainingAmount) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('Overdue Amount') }} :</strong></td>
                                                    <td class="text-end text-danger">
                                                        {{ \Auth::user()->priceFormat($overdueAmount) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Installment Details --}}
                            @if ($contract->installments->count() > 0)
                                <h5 class="mb-5"><strong>{{ __('Installment Details') }}</strong></h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="5%">{{ __('S No.') }}</th>
                                                <th>{{ __('Narration') }}</th>
                                                <th class="text-center">{{ __('Due Date') }}</th>
                                                <th class="text-end">{{ __('Due Amount') }}</th>
                                                <th class="text-center">{{ __('Paid Date') }}</th>
                                                <th class="text-end">{{ __('Paid Amount') }}</th>
                                                <th class="text-center">{{ __('Receipt No') }}</th>
                                                <th class="text-end">{{ __('Balance') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($contract->installments as $installment)
                                                @php
                                                    // Get payments for this installment
                                                    $payments = $installment->invoice
                                                        ? $installment->invoice->payments->sortBy('date')
                                                        : collect();
                                                    $totalPaidAmount = $payments->sum('amount');
                                                    $installmentBalance = $installment->amount - $totalPaidAmount;
                                                    $paymentCount = $payments->count();
                                                @endphp

                                                @if ($paymentCount > 0)
                                                    {{-- First row with installment details and first payment --}}
                                                    @php
                                                        $firstPayment = $payments->first();
                                                        $runningBalance = $installment->amount - $firstPayment->amount;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center"
                                                            @if ($paymentCount > 1) rowspan="{{ $paymentCount }}" @endif>
                                                            {{ $installment->installment_number }}</td>
                                                        <td
                                                            @if ($paymentCount > 1) rowspan="{{ $paymentCount }}" @endif>
                                                            @if ($installment->installment_type == 'down_payment')
                                                                {{ __('Booking Charges') }}
                                                            @else
                                                                {{ __('Installment') }}
                                                                {{ $installment->installment_number - 1 }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center"
                                                            @if ($paymentCount > 1) rowspan="{{ $paymentCount }}" @endif>
                                                            @if ($installment->due_date)
                                                                @if ($installment->status != 'paid' && $installment->due_date < now())
                                                                    <span
                                                                        class="text-danger">{{ \Auth::user()->dateFormat($installment->due_date) }}</span>
                                                                @else
                                                                    {{ \Auth::user()->dateFormat($installment->due_date) }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-end"
                                                            @if ($paymentCount > 1) rowspan="{{ $paymentCount }}" @endif>
                                                            {{ \Auth::user()->priceFormat($installment->amount) }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{ \Auth::user()->dateFormat($firstPayment->date) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span
                                                                class="text-success">{{ \Auth::user()->priceFormat($firstPayment->amount) }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($firstPayment->voucher)
                                                                @if ($firstPayment->voucher->voucher_type == 'BRV')
                                                                    {{ Auth::user()->BRVNumberFormat($firstPayment->voucher->journal_id) }}
                                                                @elseif($firstPayment->voucher->voucher_type == 'CRV')
                                                                    {{ Auth::user()->CRVNumberFormat($firstPayment->voucher->journal_id) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            @if ($runningBalance > 0)
                                                                <strong
                                                                    class="text-danger">{{ \Auth::user()->priceFormat($runningBalance) }}</strong>
                                                            @else
                                                                <strong
                                                                    class="text-success">{{ \Auth::user()->priceFormat(0) }}</strong>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    {{-- Additional rows for remaining payments --}}
                                                    @foreach ($payments->skip(1) as $payment)
                                                        @php $runningBalance -= $payment->amount; @endphp
                                                        <tr>
                                                            <td class="text-center">
                                                                {{ \Auth::user()->dateFormat($payment->date) }}
                                                            </td>
                                                            <td class="text-end">
                                                                <span
                                                                    class="text-success">{{ \Auth::user()->priceFormat($payment->amount) }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($payment->voucher)
                                                                    @if ($payment->voucher->voucher_type == 'BRV')
                                                                        {{ Auth::user()->BRVNumberFormat($payment->voucher->journal_id) }}
                                                                    @elseif($payment->voucher->voucher_type == 'CRV')
                                                                        {{ Auth::user()->CRVNumberFormat($payment->voucher->journal_id) }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                @if ($runningBalance > 0)
                                                                    <strong
                                                                        class="text-danger">{{ \Auth::user()->priceFormat($runningBalance) }}</strong>
                                                                @else
                                                                    <strong
                                                                        class="text-success">{{ \Auth::user()->priceFormat(0) }}</strong>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    {{-- No payments - show single row --}}
                                                    <tr>
                                                        <td class="text-center">{{ $installment->installment_number }}
                                                        </td>
                                                        <td>
                                                            @if ($installment->installment_type == 'down_payment')
                                                                {{ __('Booking Charges') }}
                                                            @else
                                                                {{ __('Installment') }}
                                                                {{ $installment->installment_number - 1 }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($installment->due_date)
                                                                @if ($installment->status != 'paid' && $installment->due_date < now())
                                                                    <span
                                                                        class="text-danger">{{ \Auth::user()->dateFormat($installment->due_date) }}</span>
                                                                @else
                                                                    {{ \Auth::user()->dateFormat($installment->due_date) }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            {{ \Auth::user()->priceFormat($installment->amount) }}</td>
                                                        <td class="text-center">-</td>
                                                        <td class="text-end">-</td>
                                                        <td class="text-center">-</td>
                                                        <td class="text-end">
                                                            <strong
                                                                class="text-danger">{{ \Auth::user()->priceFormat($installment->amount) }}</strong>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            {{-- Total Row --}}
                                            @php
                                                $totalDueAmount = $contract->installments->sum('amount');
                                                $totalPaidAmount = $contract->installments->sum(function ($inst) {
                                                    return $inst->invoice ? $inst->invoice->payments->sum('amount') : 0;
                                                });
                                                $totalBalance = $totalDueAmount - $totalPaidAmount;
                                            @endphp
                                            <tr class="table-secondary fw-bold">
                                                <td class="text-center"></td>
                                                <td>{{ __('Total') }}</td>
                                                <td class="text-center"></td>
                                                <td class="text-end">{{ \Auth::user()->priceFormat($totalDueAmount) }}
                                                </td>
                                                <td class="text-center"></td>
                                                <td class="text-end text-success">
                                                    {{ \Auth::user()->priceFormat($totalPaidAmount) }}</td>
                                                <td class="text-center"></td>
                                                <td
                                                    class="text-end {{ $totalBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ \Auth::user()->priceFormat($totalBalance) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class="d-inline-block mb-5">{{ __('Invoice') }}</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Due Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th width="10%"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->customerInvoice($customer->id) as $invoice)
                                    <tr>
                                        <td class="Id">
                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                class="btn btn-outline-primary">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                            </a>
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                        <td>
                                            @if ($invoice->due_date < date('Y-m-d'))
                                                <p class="text-danger">
                                                    {{ \Auth::user()->dateFormat($invoice->due_date) }}</p>
                                            @else
                                                {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                            @endif
                                        </td>
                                        <td>{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
                                        <td>
                                            @if ($invoice->status == 0)
                                                <span
                                                    class="badge bg-primary p-2 px-3 rounded status_badge">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 1)
                                                <span
                                                    class="badge bg-warning p-2 px-3 rounded status_badge">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 2)
                                                <span
                                                    class="badge bg-danger p-2 px-3 rounded status_badge">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 3)
                                                <span
                                                    class="badge bg-info p-2 px-3 rounded status_badge">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @elseif($invoice->status == 4)
                                                <span
                                                    class="badge bg-primary p-2 px-3 rounded status_badge">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td class="Action">
                                                <span>
                                                    @can('copy invoice')
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a class="mx-3 btn btn-sm align-items-center"
                                                                id="{{ route('invoice.link.copy', $invoice->id) }}"
                                                                onclick="copyToClipboard(this)" data-bs-toggle="tooltip"
                                                                data-original-title="{{ __('Copy Invoice') }}"><i
                                                                    class="ti ti-link text-white"></i></a>
                                                        </div>
                                                    @endcan
                                                    {{-- @can('duplicate invoice')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip"
                                                                data-original-title="{{ __('Duplicate') }}"
                                                                title="{{ __('Duplicate Invoice') }}"
                                                                data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back"
                                                                data-confirm-yes="document.getElementById('duplicate-form-{{ $invoice->id }}').submit();">
                                                                <i class="ti ti-copy text-white text-white"></i>
                                                                {!! Form::open([
                                                                    'method' => 'get',
                                                                    'route' => ['invoice.duplicate', $invoice->id],
                                                                    'id' => 'duplicate-form-' . $invoice->id,
                                                                ]) !!}
                                                                {!! Form::close() !!}
                                                            </a>
                                                        </div>
                                                    @endcan --}}
                                                    @can('show invoice')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Show') }}"
                                                                data-original-title="{{ __('Detail') }}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit invoice')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                data-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    {{-- @can('delete invoice')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['invoice.destroy', $invoice->id],
                                                                'id' => 'delete-form-' . $invoice->id,
                                                            ]) !!}

                                                            <a href="#"
                                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                data-original-title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $invoice->id }}').submit();">
                                                                <i class="ti ti-trash text-white text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan --}}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
