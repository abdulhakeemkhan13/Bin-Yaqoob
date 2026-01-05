@extends('layouts.admin')

@section('page-title')
    {{ __('Cheque Management') }}
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

        $(document).ready(function() {
            $('.datatable').DataTable();
        });

        function deleteCheque(id) {
            if (confirm('{{ __('Are you sure you want to delete this cheque?') }}')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>




    <script>
        $(document).ready(function() {
            // Open approve modal and set cheque id
            $('.openApproveModal').click(function() {
                let id = $(this).data('id');
                $('#cheque_id').val(id);
                $('#approveModal').modal('show');
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Cheque Management') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('cheque.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Create Cheque') }}">
            <i class="ti ti-plus"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
            title="{{ __('Download PDF') }}">
            <i class="ti ti-download"></i>
        </a>
    </div>
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['cheque.index'], 'method' => 'get', 'id' => 'cheque_filter_form']) }}
                    <div class="row align-items-center">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
                            <div class="btn-box">
                                {{ Form::label('start_month', __('Start Month'), ['class' => 'form-label']) }}
                                {{ Form::month('start_month', request('start_month', date('Y-m', strtotime('-5 month'))), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
                            <div class="btn-box">
                                {{ Form::label('end_month', __('End Month'), ['class' => 'form-label']) }}
                                {{ Form::month('end_month', request('end_month', date('Y-m')), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
                            <div class="btn-box">
                                {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                {{ Form::select('account', $account, request('account', ''), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
                            <div class="btn-box">
                                {{ Form::label('cheque_no', __('Cheque No'), ['class' => 'form-label']) }}
                                {{ Form::text('cheque_no', request('cheque_no', ''), ['class' => 'form-control', 'placeholder' => __('Search Cheque No')]) }}
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                title="{{ __('Apply') }}">
                                <i class="ti ti-search"></i> {{ __('Apply') }}
                            </button>
                            <a href="{{ route('cheque.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                title="{{ __('Reset') }}">
                                <i class="ti ti-trash-off"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cheque List Table -->
    <div class="row" id="printableArea">
        <div class="col-md-12">
            <input type="hidden" id="filename"
                value="Cheque_List_{{ $filter['startDateRange'] }}_to_{{ $filter['endDateRange'] }}">
            <div class="card">
                <div class="card-body">
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
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    {{-- @dd($rows); --}}
                                    @php
                                        $pay = $row->invoice_payment_id ? $row->payment : $row->billPayment;
                                        $bankAcc = optional($pay)->bankAccount ?: $row->bankAccount;
                                        $titleAcct = optional($bankAcc)->holder_name ?: __('(n/a)');
                                        $bankName = optional($bankAcc)->bank_name ?: '—';
                                        $desc = optional($pay)->description ?: ($row->notes ?: '—');
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
                                        <td>
                                            @if ($row->status === 'Pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($row->status === 'Approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @elseif ($row->status === 'Rejected')
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $row->status ?? '-' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row->status === 'Pending')
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="javascript:void(0);"
                                                        class="mx-3 btn btn-sm align-items-center openApproveModal"
                                                        data-id="{{ $row->id }}" data-bs-toggle="tooltip"
                                                        title="Approve / Reject">
                                                        <i class="far fa-thumbs-up text-white"></i>
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('cheque.show', $row->id) }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @if ($row->status === 'Pending')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route('cheque.edit', $row->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                        onclick="deleteCheque({{ $row->id }})">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                </div>
                                                {{ Form::open(['route' => ['cheque.destroy', $row->id], 'method' => 'delete', 'id' => 'delete-form-' . $row->id, 'style' => 'display:none;']) }}
                                                {{ Form::close() }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            {{ __('No cheques found for the selected filters.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Approve/Reject Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="approveForm" method="POST" action="{{ route('cheque.approve') }}">
                @csrf
                <input type="hidden" name="cheque_id" id="cheque_id">
                <input type="hidden" name="status" id="modal_status" value="Approved">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Approve / Reject Check') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-center mb-4">{{ __('Select an action for this check:') }}
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success btn-lg"
                                onclick="$('#modal_status').val('Approved');">
                                <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                            </button>
                            <button type="submit" class="btn btn-danger btn-lg"
                                onclick="$('#modal_status').val('Rejected');">
                                <i class="ti ti-x me-2"></i>{{ __('Reject') }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-arrow-left me-1"></i>{{ __('Cancel') }}
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
