@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Cheque') }}
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            // Form validation before submit
            $('form').on('submit', function(e) {
                var amount = parseFloat($('#amount').val());

                if (amount <= 0) {
                    e.preventDefault();
                    alert('{{ __('Amount must be greater than 0') }}');
                    return false;
                }

                // Validate required fields for minutes sheet checks
                @if ($cheque->minutes_sheet_id)
                    var payeeName = $('#payee_name').val().trim();
                    var titleOfAccount = $('#title_of_account').val().trim();

                    if (!payeeName) {
                        e.preventDefault();
                        alert('{{ __('Payee Name is required') }}');
                        $('#payee_name').focus();
                        return false;
                    }

                    if (!titleOfAccount) {
                        e.preventDefault();
                        alert('{{ __('Title of Account is required') }}');
                        $('#title_of_account').focus();
                        return false;
                    }
                @endif
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cheque.index') }}">{{ __('Cheque Management') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    {{-- Only allow editing if status is Pending --}}
    @if ($cheque->status != 'Pending')
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    {{ __('This cheque cannot be edited because it has already been') }}
                    <strong>{{ $cheque->status == 'Approve' ? __('Approved') : __('Rejected') }}</strong>.
                </div>
                <a href="{{ route('cheque.show', $cheque->id) }}" class="btn btn-primary">
                    <i class="ti ti-eye me-1"></i>{{ __('View Cheque Details') }}
                </a>
                <a href="{{ route('cheque.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>{{ __('Back to List') }}
                </a>
            </div>
        </div>
    @else
        @php
            $pay = $cheque->invoice_payment_id ? $cheque->payment : $cheque->billPayment;
            $minutesSheet = $cheque->minutesSheet;
            $remainingAmount = $minutesSheet ? $minutesSheet->getRemainingAmount() + $cheque->amount : null;
        @endphp

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">{{ __('Edit Cheque') }} #{{ $cheque->cheque_number }}</h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-warning fs-6"><i
                                        class="ti ti-clock me-1"></i>{{ __('Pending') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Minutes Sheet Info --}}
                        @if ($minutesSheet)
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="ti ti-file-check me-2 fs-4"></i>
                                    <div>
                                        <strong>{{ __('Minutes Sheet Information') }}</strong><br>
                                        <strong>{{ __('Reference') }}:</strong> {{ $minutesSheet->reference_no }}<br>
                                        <strong>{{ __('Subject') }}:</strong> {{ $minutesSheet->subject }}<br>
                                        <strong>{{ __('Total Amount') }}:</strong>
                                        {{ Auth::user()->priceFormat($minutesSheet->amount) }}<br>
                                        <strong>{{ __('Available for this check') }}:</strong>
                                        <span
                                            class="badge bg-success">{{ Auth::user()->priceFormat($remainingAmount) }}</span>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{ Form::model($cheque, ['route' => ['cheque.update', $cheque->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'cheque-form']) }}

                        <div class="row">
                            {{-- Payee Name Field (for Minutes Sheet checks) --}}
                            @if ($cheque->minutes_sheet_id)
                                <div class="form-group col-md-6 mb-3">
                                    {{ Form::label('payee_name', __('Payee Name') . ' *', ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        {{ Form::text('payee_name', old('payee_name', $cheque->payee_name), ['class' => 'form-control', 'required' => 'required', 'id' => 'payee_name', 'placeholder' => __('Enter payee name')]) }}
                                    </div>
                                    @error('payee_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 mb-3">
                                    {{ Form::label('title_of_account', __('Title of Account') . ' *', ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        {{ Form::text('title_of_account', old('title_of_account', $cheque->account_title), ['class' => 'form-control', 'required' => 'required', 'id' => 'title_of_account', 'placeholder' => __('Enter account title')]) }}
                                    </div>
                                    @error('title_of_account')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 mb-3">
                                    {{ Form::label('account_number', __('Account Number (Optional)'), ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        {{ Form::text('account_number', old('account_number', $cheque->account_number), ['class' => 'form-control', 'id' => 'account_number', 'placeholder' => __('Enter account number')]) }}
                                    </div>
                                    @error('account_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 mb-3">
                                    {{ Form::label('other_coa', __('COA Account') . ' *', ['class' => 'form-label']) }}
                                    {{ Form::select('other_coa', $coaAccounts ?? [], old('other_coa', $cheque->other_coa), ['class' => 'form-control select', 'required' => 'required', 'id' => 'other_coa']) }}
                                    <small
                                        class="text-muted">{{ __('Select the Chart of Account for this check') }}</small>
                                    @error('other_coa')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endif

                            <!-- Date Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('date', __('Date') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">
                                    {{ Form::date('date', old('date', $cheque->date), ['class' => 'form-control', 'required' => 'required', 'id' => 'date']) }}
                                </div>
                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Amount Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('amount', __('Amount') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">
                                    {{ Form::number('amount', old('amount', $cheque->amount), ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'max' => $remainingAmount, 'id' => 'amount', 'placeholder' => $remainingAmount ? __('Max: ') . number_format($remainingAmount, 2) : __('Enter amount')]) }}
                                </div>
                                @if ($remainingAmount)
                                    <small class="text-muted">{{ __('Maximum available:') }}
                                        {{ Auth::user()->priceFormat($remainingAmount) }}</small>
                                @endif
                                @error('amount')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Cheque Number Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('cheque_number', __('Cheque Number'), ['class' => 'form-label']) }}
                                <div class="input-group">
                                    {{ Form::text('cheque_number', old('cheque_number', $cheque->cheque_number), ['id' => 'cheque_number', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                                </div>
                                <small class="text-muted">{{ __('Auto-generated, cannot be changed') }}</small>
                            </div>

                            <!-- Account Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('account_id', __('Bank Account') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">
                                    {{ Form::select('account_id', $accounts, old('account_id', $cheque->account_id), ['class' => 'form-control select', 'required' => 'required', 'id' => 'account_id']) }}
                                </div>
                                @error('account_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Description Field -->
                            <div class="form-group col-md-12 mb-3">
                                {{ Form::label('description', __('Description / Notes'), ['class' => 'form-label']) }}
                                <div class="input-group">
                                    {{ Form::textarea('description', old('description', $cheque->notes), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter notes or remarks')]) }}
                                </div>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div></div>
                                    <!-- Action Buttons (Right) -->
                                    <div>
                                        <a href="{{ route('cheque.index') }}" class="btn btn-secondary">
                                            <i class="ti ti-arrow-left me-1"></i> {{ __('Cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-check me-1"></i> {{ __('Save Changes') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>

                    <!-- Additional Info Footer -->
                    <div class="card-footer text-muted">
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <i class="ti ti-user me-1"></i> {{ __('Created by') }}:
                                    {{ optional($cheque->creator)->name ?: __('Unknown') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small>
                                    <i class="ti ti-clock me-1"></i> {{ __('Last updated') }}:
                                    {{ $cheque->updated_at ? $cheque->updated_at->diffForHumans() : __('Never') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
