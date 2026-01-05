@extends('layouts.admin')

@section('page-title')
    {{ __('Create Cheque') }}
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            // Toggle cheque-specific fields based on payment type
            function toggleChequeFields() {
                var type = $('#type').val();
                if (type === 'cheque') {
                    $('#cheque-number-wrap, #title-of-account-wrap').slideDown(300);
                    $('#cheque_number, #title_of_account').attr('required', 'required');
                } else {
                    $('#cheque-number-wrap, #title-of-account-wrap').slideUp(300);
                    $('#cheque_number, #title_of_account').removeAttr('required').val('');
                }
            }

            // Initialize on page load
            toggleChequeFields();

            // Listen to type change
            $('#type').on('change', toggleChequeFields);

            // Handle select2 change if you're using it
            if (typeof $.fn.select2 !== 'undefined') {
                $('#type').on('select2:select', toggleChequeFields);
            }

            // Minutes Sheet Dropdown Handler
            $('#minutes_sheet_id').on('change', function() {
                var selected = $(this).find(':selected');
                var msId = $(this).val();

                if (msId) {
                    // Show minutes sheet info
                    var remaining = parseFloat(selected.data('remaining'));
                    var bankId = selected.data('bank-id');
                    var bankName = selected.data('bank-name');
                    var holderName = selected.data('holder-name');

                    // Display info
                    $('#ms-bank-display').text(bankName + ' - ' + holderName);
                    $('#ms-remaining-display').text('{{ \Auth::user()->currencySymbol() }}' + remaining
                        .toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        }));
                    $('#minutes-sheet-info').slideDown(300);

                    // Show all minutes sheet specific fields
                    $('#payee-name-wrap, #title-account-wrap, #account-number-wrap, #coa-account-wrap')
                        .slideDown(300);
                    $('#payee_name, #title_of_account, #other_coa').attr('required', 'required');

                    // Auto-select bank account
                    if (bankId) {
                        $('#account_id').val(bankId).trigger('change');
                    }

                    // Update amount max
                    $('#amount').attr('max', remaining);
                    $('#amount').attr('placeholder', '{{ __('Max: ') }}' + remaining.toFixed(2));
                } else {
                    // Hide minutes sheet specific fields
                    $('#minutes-sheet-info').slideUp(300);
                    $('#payee-name-wrap, #title-account-wrap, #account-number-wrap, #coa-account-wrap')
                        .slideUp(300);
                    $('#payee_name, #title_of_account, #other_coa').removeAttr('required').val('');
                    $('#account_number').val('');
                    $('#amount').removeAttr('max');
                    $('#amount').attr('placeholder', '{{ __('Enter amount') }}');
                }
            });

            // Preview uploaded file name
            $('#add_receipt').on('change', function(e) {
                var fileName = e.target.files[0]?.name || '';
                if (fileName) {
                    $('.upload_file').text('Selected: ' + fileName).addClass('text-success');
                } else {
                    $('.upload_file').text('').removeClass('text-success');
                }
            });

            // Form validation before submit
            $('form').on('submit', function(e) {
                var type = $('#type').val();
                var amount = parseFloat($('#amount').val());
                var msId = $('#minutes_sheet_id').val();

                if (amount <= 0) {
                    e.preventDefault();
                    alert('{{ __('Amount must be greater than 0') }}');
                    return false;
                }

                // Check if minutes sheet is selected but payee name is empty
                if (msId && !$('#payee_name').val().trim()) {
                    e.preventDefault();
                    alert('{{ __('Payee Name is required for Minutes Sheet checks') }}');
                    $('#payee_name').focus();
                    return false;
                }

                if (type === 'cheque') {
                    var chequeNo = $('#cheque_number').val().trim();
                    var titleAcc = $('#title_of_account').val().trim();

                    if (!chequeNo) {
                        e.preventDefault();
                        alert('{{ __('Cheque Number is required when type is Cheque') }}');
                        $('#cheque_number').focus();
                        return false;
                    }

                    if (!titleAcc) {
                        e.preventDefault();
                        alert('{{ __('Title of Account is required when type is Cheque') }}');
                        $('#title_of_account').focus();
                        return false;
                    }
                }
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cheque.index') }}">{{ __('Cheque Management') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('Create New Cheque Payment') }}</h5>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('cheque.index') }}" class="btn btn-sm btn-secondary">
                                <i class="ti ti-arrow-left"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($bill)
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="ti ti-info-circle me-2 fs-4"></i>
                                <div>
                                    <strong>{{ __('Bill Information') }}</strong><br>
                                    <strong>{{ __('Bill ID') }}:</strong> {{ $bill->bill_id }}<br>
                                    @if ($bill->vender)
                                        <strong>{{ __('Vendor') }}:</strong> {{ $bill->vender->name }}<br>
                                    @endif
                                    <strong>{{ __('Due Amount') }}:</strong>
                                    <span class="badge bg-danger">{{ Auth::user()->priceFormat($bill->getDue()) }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (isset($minutesSheet) && $minutesSheet)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="ti ti-file-check me-2 fs-4"></i>
                                <div>
                                    <strong>{{ __('Minutes Sheet Information') }}</strong><br>
                                    <strong>{{ __('Reference') }}:</strong> {{ $minutesSheet->reference_no }}<br>
                                    <strong>{{ __('Subject') }}:</strong> {{ $minutesSheet->subject }}<br>
                                    <strong>{{ __('Total Amount') }}:</strong>
                                    {{ Auth::user()->priceFormat($minutesSheet->amount) }}<br>
                                    <strong>{{ __('Bank Account') }}:</strong>
                                    {{ $minutesSheet->bankAccount->bank_name ?? '-' }} -
                                    {{ $minutesSheet->bankAccount->holder_name ?? '-' }}<br>
                                    <strong>{{ __('Remaining Amount') }}:</strong>
                                    <span class="badge bg-success">{{ Auth::user()->priceFormat($remainingAmount) }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{ Form::open(['route' => ['cheque.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'cheque-form']) }}

                    @if ($bill)
                        <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                    @endif

                    @if (isset($minutesSheet) && $minutesSheet)
                        <input type="hidden" name="minutes_sheet_id" value="{{ $minutesSheet->id }}">
                    @endif

                    <div class="row">
                        {{-- Minutes Sheet Dropdown (required) --}}
                        @if (!isset($minutesSheet) || !$minutesSheet)
                            <div class="form-group col-md-12 mb-3">
                                {{ Form::label('minutes_sheet_id', __('Minutes Sheet') . ' *', ['class' => 'form-label']) }}
                                <select name="minutes_sheet_id" id="minutes_sheet_id" class="form-control select" required>
                                    <option value="">{{ __('Select Minutes Sheet') }}</option>
                                    @foreach ($approvedMinutesSheets as $ms)
                                        @if ($ms['remaining_amount'] > 0)
                                            <option value="{{ $ms['id'] }}"
                                                data-remaining="{{ $ms['remaining_amount'] }}"
                                                data-bank-id="{{ $ms['bank_account_id'] }}"
                                                data-bank-name="{{ $ms['bank_name'] }}"
                                                data-holder-name="{{ $ms['holder_name'] }}"
                                                data-total="{{ $ms['total_amount'] }}">
                                                {{ $ms['label'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <small
                                    class="text-muted">{{ __('Only showing minutes sheets with remaining balance > 0') }}</small>
                            </div>

                            {{-- Minutes Sheet Info Display (shown when selected) --}}
                            <div class="col-md-12 mb-3" id="minutes-sheet-info" style="display: none;">
                                <div class="alert alert-success">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>{{ __('Bank Account:') }}</strong> <span id="ms-bank-display"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Remaining Amount:') }}</strong> <span id="ms-remaining-display"
                                                class="badge bg-success"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Title of Account Field --}}
                        <div class="form-group col-md-6 mb-3" id="title-account-wrap">
                            {{ Form::label('title_of_account', __('Title of Account') . ' *', ['class' => 'form-label']) }}
                            <div class="input-group">
                                {{ Form::text('title_of_account', old('title_of_account'), ['class' => 'form-control', 'id' => 'title_of_account', 'placeholder' => __('Enter account title')]) }}
                            </div>
                            @error('title_of_account')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Account Number Field (Optional) --}}
                        <div class="form-group col-md-6 mb-3" id="account-number-wrap">
                            {{ Form::label('account_number', __('Payee Account Number (Optional)'), ['class' => 'form-label']) }}
                            <div class="input-group">
                                {{ Form::text('account_number', old('account_number'), ['class' => 'form-control', 'id' => 'account_number', 'placeholder' => __('Enter account number')]) }}
                            </div>
                            @error('account_number')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row">


                            {{-- COA Account Field --}}
                            <div class="form-group col-md-6 mb-3" id="coa-account-wrap"
                                @if (!isset($minutesSheet) || !$minutesSheet) style="display: none;" @endif>
                                {{ Form::label('other_coa', __('COA Account') . ' *', ['class' => 'form-label']) }}
                                {{ Form::select('other_coa', $coaAccounts ?? [], old('other_coa'), ['class' => 'form-control select', 'id' => 'other_coa']) }}
                                <small class="text-muted">{{ __('Select the Chart of Account for this check') }}</small>
                                @error('other_coa')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Date Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('date', __('Date') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">

                                    {{ Form::date('date', old('date', date('Y-m-d')), ['class' => 'form-control', 'required' => 'required', 'id' => 'date']) }}
                                </div>
                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Amount Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('amount', __('Amount') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">
                                    @php
                                        $maxAmount = isset($remainingAmount) ? $remainingAmount : null;
                                        $defaultAmount = $bill ? $bill->getDue() : '';
                                    @endphp
                                    {{ Form::number('amount', old('amount', $defaultAmount), ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'max' => $maxAmount, 'id' => 'amount', 'placeholder' => $maxAmount ? __('Max: ') . number_format($maxAmount, 2) : __('Enter amount')]) }}
                                </div>
                                @if (isset($remainingAmount))
                                    <small class="text-muted">{{ __('Maximum available:') }}
                                        {{ Auth::user()->priceFormat($remainingAmount) }}</small>
                                @endif
                                @error('amount')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Account Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('account_id', __('Account') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">

                                    {{ Form::select('account_id', $accounts, old('account_id'), ['class' => 'form-control select', 'required' => 'required', 'id' => 'account_id']) }}
                                </div>
                                @error('account_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Payment Type Field -->
                            {{-- <div class="form-group col-md-6 mb-3">
                            {{ Form::label('type', __('Payment Type') . ' *', ['class' => 'form-label']) }}
                            <div class="input-group">
                                
                                {{ Form::select('type', $types, old('type', 'cheque'), ['id' => 'type', 'class' => 'form-control select', 'required' => 'required']) }}
                            </div>
                            @error('type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div> --}}

                            <!-- Cheque Number Field (Conditional) -->
                            <div class="form-group col-md-6 mb-3" id="cheque-number-wrap" style="display: none;">
                                {{ Form::label('cheque_number', __('Cheque Number') . ' *', ['class' => 'form-label']) }}
                                <div class="input-group">

                                    {{ Form::text('cheque_number', old('cheque_number'), ['id' => 'cheque_number', 'class' => 'form-control', 'placeholder' => __('Enter Cheque Number')]) }}
                                </div>
                                <small class="text-muted">{{ __('Required when Type is Cheque') }}</small>
                                @error('cheque_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Title of Account Field (Conditional) -->
                            {{-- <div class="form-group col-md-6 mb-3" id="title-of-account-wrap" style="display: none;">
                            {{ Form::label('title_of_account', __('Title of Account') . ' *', ['class' => 'form-label']) }}
                            <div class="input-group">
                                
                                {{ Form::text('title_of_account', old('title_of_account'), ['id' => 'title_of_account', 'class' => 'form-control', 'placeholder' => __('Enter cheque account title')]) }}
                            </div>
                            <small class="text-muted">{{ __('Account holder name on cheque') }}</small>
                            @error('title_of_account')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div> --}}

                            <!-- Reference / BPV No Field -->
                            <div class="form-group col-md-6 mb-3">
                                {{ Form::label('reference', __('Reference / BPV No'), ['class' => 'form-label']) }}
                                <div class="input-group">

                                    {{ Form::text('reference', old('reference'), ['class' => 'form-control', 'placeholder' => __('Enter reference or BPV number')]) }}
                                </div>
                                @error('reference')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Payment Receipt Field -->
                            {{-- <div class="form-group col-md-6 mb-3">
                            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
                            <div class="choose-file">
                                <label for="add_receipt" class="form-label d-block">
                                    <div class="input-group">
                                        
                                        <input type="file" name="add_receipt" id="add_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif">
                                    </div>
                                </label>
                                <p class="upload_file small mt-1"></p>
                                <small class="text-muted">{{ __('Supported: PDF, JPG, PNG (Max: 2MB)') }}</small>
                            </div>
                            @error('add_receipt')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div> --}}

                            <!-- Description Field -->
                            <div class="form-group col-md-12 mb-3">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                <div class="input-group">

                                    {{ Form::textarea('description', old('description'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter payment description or notes')]) }}
                                </div>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="text-end">
                                    <a href="{{ route('cheque.index') }}" class="btn btn-secondary">
                                        <i class="ti ti-x"></i> {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-check"></i> {{ __('Create Cheque') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    @endsection
