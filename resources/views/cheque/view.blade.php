@extends('layouts.admin')

@section('page-title')
    {{ __('View Cheque') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cheque.index') }}">{{ __('Cheque Management') }}</a></li>
    <li class="breadcrumb-item">{{ __('View') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('cheque.print', $cheque->id) }}" class="btn btn-sm btn-primary" target="_blank"
            data-bs-toggle="tooltip" title="{{ __('Print Minute Sheet') }}">
            <i class="ti ti-printer"></i> {{ __('Print') }}
        </a>
        <a href="{{ route('cheque.index') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $pay = $cheque->invoice_payment_id ? $cheque->payment : $cheque->billPayment;
        $bankAcc = optional($pay)->bankAccount ?: $cheque->bankAccount;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Cheque Details') }}</h5>
                    <div>
                        @if ($cheque->status == 'Approved')
                            <span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>{{ __('Approved') }}</span>
                        @elseif($cheque->status == 'Rejected')
                            <span class="badge bg-danger fs-6"><i class="ti ti-x me-1"></i>{{ __('Rejected') }}</span>
                        @else
                            <span class="badge bg-warning fs-6"><i class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="40%">{{ __('Date') }}:</th>
                                        <td>{{ Auth::user()->dateFormat($cheque->date) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Cheque Number') }}:</th>
                                        <td>
                                            <span class="badge bg-primary">{{ $cheque->cheque_number ?: '—' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Amount') }}:</th>
                                        <td>
                                            <strong
                                                class="text-success fs-5">{{ Auth::user()->priceFormat($cheque->amount) }}</strong>
                                        </td>
                                    </tr>
                                    @if ($cheque->payee_name)
                                        <tr>
                                            <th>{{ __('Payee Name') }}:</th>
                                            <td>{{ $cheque->payee_name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{ __('Title of Account') }}:</th>
                                        <td>{{ $cheque->account_title ?: '—' }}</td>
                                    </tr>
                                    @if ($cheque->account_number)
                                        <tr>
                                            <th>{{ __('Account Number') }}:</th>
                                            <td>{{ $cheque->account_number }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{ __('Bank Name') }}:</th>
                                        <td>{{ optional($bankAcc)->bank_name ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Account Holder') }}:</th>
                                        <td>{{ optional($bankAcc)->holder_name ?: '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="40%">{{ __('BPV No / Reference') }}:</th>
                                        <td>{{ $cheque->journal_entry_id ? 'BPV-' . $cheque->cheque_number : (optional($pay)->reference ?: '—') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Payment Type') }}:</th>
                                        <td>
                                            <span class="badge bg-info">{{ __('Cheque') }}</span>
                                        </td>
                                    </tr>
                                    @if ($cheque->minutes_sheet_id)
                                        <tr>
                                            <th>{{ __('Minutes Sheet') }}:</th>
                                            <td>
                                                <a href="{{ route('minutes-sheet.show', $cheque->minutes_sheet_id) }}">
                                                    {{ optional($cheque->minutesSheet)->reference_no }} -
                                                    {{ optional($cheque->minutesSheet)->subject }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if (optional($pay)->bill_id)
                                        <tr>
                                            <th>{{ __('Related Bill') }}:</th>
                                            <td>
                                                <a href="{{ route('bill.show', $pay->bill_id) }}">
                                                    {{ optional($pay->bill)->bill_id }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if (optional($pay)->vender_id)
                                        <tr>
                                            <th>{{ __('Vendor') }}:</th>
                                            <td>{{ optional($pay->vender)->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{ __('Created By') }}:</th>
                                        <td>{{ optional($cheque->creator)->name ?: '—' }}</td>
                                    </tr>
                                    @if ($cheque->approved_by)
                                        <tr>
                                            <th>{{ __('Approved By') }}:</th>
                                            <td>
                                                <span class="text-success">
                                                    <i class="ti ti-user-check me-1"></i>
                                                    {{ optional(\App\Models\User::find($cheque->approved_by))->name ?: '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{ __('Created At') }}:</th>
                                        <td>{{ $cheque->created_at ? $cheque->created_at->format('d M Y, h:i A') : '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Description Section -->
                    @if (optional($pay)->description || $cheque->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <hr>
                                <h6>{{ __('Description') }}:</h6>
                                <p class="text-muted">
                                    {{ optional($pay)->description ?: $cheque->notes }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Receipt Section -->
                    @if (optional($pay)->add_receipt)
                        <div class="row mt-3">
                            <div class="col-12">
                                <hr>
                                <h6>{{ __('Payment Receipt') }}:</h6>
                                <a href="{{ asset('uploads/payment/' . $pay->add_receipt) }}" target="_blank"
                                    class="btn btn-sm btn-primary">
                                    <i class="ti ti-download"></i> {{ __('Download Receipt') }}
                                </a>

                                @php
                                    $extension = pathinfo($pay->add_receipt, PATHINFO_EXTENSION);
                                @endphp

                                @if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                    <div class="mt-3">
                                        <img src="{{ asset('uploads/payment/' . $pay->add_receipt) }}" alt="Receipt"
                                            class="img-fluid"
                                            style="max-width: 500px; border: 1px solid #ddd; padding: 10px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Card Footer with Actions -->
                <div class="card-footer text-end">
                    <a href="{{ route('cheque.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> {{ __('Back to List') }}
                    </a>
                    @if ($cheque->status == 'Pending')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#approveModal">
                            <i class="ti ti-check"></i> {{ __('Approve') }}
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="ti ti-x"></i> {{ __('Reject') }}
                        </button>
                        <a href="{{ route('cheque.edit', $cheque->id) }}" class="btn btn-primary">
                            <i class="ti ti-pencil"></i> {{ __('Edit Cheque') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('cheque.approve') }}">
                @csrf
                <input type="hidden" name="cheque_id" value="{{ $cheque->id }}">
                <input type="hidden" name="status" value="Approved">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="ti ti-check me-2"></i>{{ __('Approve Cheque') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="ti ti-check-circle text-success" style="font-size: 4rem;"></i>
                        <p class="mt-3 fs-5">{{ __('Are you sure you want to approve this cheque?') }}</p>
                        <p class="text-muted">{{ __('A Bank Payment Voucher will be created upon approval.') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i>{{ __('Yes, Approve') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('cheque.approve') }}">
                @csrf
                <input type="hidden" name="cheque_id" value="{{ $cheque->id }}">
                <input type="hidden" name="status" value="Rejected">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ti ti-x me-2"></i>{{ __('Reject Cheque') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="ti ti-x-circle text-danger" style="font-size: 4rem;"></i>
                        <p class="mt-3 fs-5">{{ __('Are you sure you want to reject this cheque?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-x me-1"></i>{{ __('Yes, Reject') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
