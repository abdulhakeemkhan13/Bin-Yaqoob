@extends('layouts.admin')

@section('page-title')
    {{ __('Cheque List Report') }}
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var filename = ($('#filename').val() || 'cheque-list') + '.pdf';
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4'
                }
            };
            html2pdf().set(opt).from(element).save();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // If you’re using DataTables, make sure selector matches:
            $('.datatable').DataTable && $('.datatable').DataTable();
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Cheque List Report') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('accountstatement.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"
            class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
            title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.cheque.list'], 'method' => 'get', 'id' => 'cheque_list_report']) }}
                        <div class="row align-items-center justify-content-start">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_month', __('Start Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('start_month', request('start_month', date('Y-m', strtotime('-5 month'))), ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_month', __('End Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('end_month', request('end_month', date('Y-m')), ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                            {{ Form::select('account', $account, request('account', ''), ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                        <div class="btn-box">
                                            {{ Form::label('cheque_no', __('Cheque No'), ['class' => 'form-label']) }}
                                            {{ Form::text('cheque_no', request('cheque_no', ''), ['class' => 'form-control', 'placeholder' => __('Search Cheque No')]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('cheque_list_report').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.cheque.list') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="printableArea">
        <div class="col-md-12">
            <input type="hidden" id="filename"
                value="Cheque_List_{{ $filter['startDateRange'] }}_to_{{ $filter['endDateRange'] }}">
            <div class="card">
                <div class="card-body table-border-style" id="printableArea">
                    <input type="hidden" id="filename"
                        value="Cheque_List_{{ $filter['startDateRange'] }}_to_{{ $filter['endDateRange'] }}">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Cheque No') }}</th>
                                    <th>{{ __('Title of Account') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('BPV No') }}</th>
                                    <th>{{ __('Bank Name') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    @php
                                        // Priority: if revenue (invoice side), prefer $row->payment; if payment (bill side), prefer $row->billPayment
                                        $pay = $row->invoice_payment_id ? $row->payment : $row->billPayment;

                                        // Title of Account + Bank Name from related payment's bankAccount; fallback to checklist->bankAccount
$bankAcc = optional($pay)->bankAccount ?: $row->bankAccount;
$titleAcct = optional($bankAcc)->holder_name ?: __('(n/a)');
$bankName = optional($bankAcc)->bank_name ?: '—';

// Description: payment/billPayment description → fallback to checklist.notes
$desc = optional($pay)->description ?: ($row->notes ?: '—');

// BPV No: use payment/billPayment reference (common “voucher no / BPV no” field). Fallback to '—'
$bpv = optional($pay)->reference ?: '—';
                                    @endphp
                                    <tr>
                                        <td>{{ Auth::user()->dateFormat($row->date) }}</td>
                                        <td>{{ $row->cheque_number ?: '—' }}</td>
                                        <td>{{ $row->account_title ?: '—' }}</td>
                                        <td>{{ $desc }}</td>
                                        <td class="text-end">{{ Auth::user()->priceFormat($row->amount) }}</td>
                                        <td>{{ $bpv }}</td>
                                        <td>{{ $bankName }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            {{ __('No cheques found for the selected filters.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
