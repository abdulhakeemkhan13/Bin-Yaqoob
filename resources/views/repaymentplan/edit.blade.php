@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Payment Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-payment-plans.index') }}">{{ __('Payment Plans') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Edit Payment Plan') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('re-payment-plans.update', $plan->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Plan Name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('plan_name') is-invalid @enderror"
                                        name="plan_name" value="{{ old('plan_name', $plan->plan_name) }}" required>
                                    @error('plan_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('No. of Installments') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number"
                                        class="form-control @error('num_installments') is-invalid @enderror"
                                        name="num_installments"
                                        value="{{ old('num_installments', $plan->num_installments) }}" min="1"
                                        required>
                                    @error('num_installments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Down Payment %') }}</label>
                                    <input type="number"
                                        class="form-control @error('down_payment_percentage') is-invalid @enderror"
                                        name="down_payment_percentage"
                                        value="{{ old('down_payment_percentage', $plan->down_payment_percentage) }}"
                                        step="0.01" min="0" max="100">
                                    @error('down_payment_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Frequency') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('frequency') is-invalid @enderror" name="frequency"
                                        required>
                                        <option value="Monthly"
                                            {{ old('frequency', $plan->frequency) == 'Monthly' ? 'selected' : '' }}>
                                            {{ __('Monthly') }}</option>
                                        <option value="Quarterly"
                                            {{ old('frequency', $plan->frequency) == 'Quarterly' ? 'selected' : '' }}>
                                            {{ __('Quarterly') }}</option>
                                        <option value="Half-Yearly"
                                            {{ old('frequency', $plan->frequency) == 'Half-Yearly' ? 'selected' : '' }}>
                                            {{ __('Half-Yearly (6 Months)') }}</option>
                                        <option value="Yearly"
                                            {{ old('frequency', $plan->frequency) == 'Yearly' ? 'selected' : '' }}>
                                            {{ __('Yearly') }}</option>
                                    </select>
                                    @error('frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Possession Charges') }}</label>
                                    <input type="number"
                                        class="form-control @error('possession_charges') is-invalid @enderror"
                                        name="possession_charges"
                                        value="{{ old('possession_charges', $plan->possession_charges) }}" step="0.01"
                                        min="0">
                                    @error('possession_charges')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Default Discount') }}</label>
                                    <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                        name="discount" value="{{ old('discount', $plan->discount) }}" step="0.01"
                                        min="0">
                                    @error('discount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Extra Charges') }}</label>
                                    <input type="number" class="form-control @error('extra_charges') is-invalid @enderror"
                                        name="extra_charges" value="{{ old('extra_charges', $plan->extra_charges) }}"
                                        step="0.01" min="0">
                                    @error('extra_charges')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Booking Charges') }}</label>
                                    <input type="number"
                                        class="form-control @error('booking_charges') is-invalid @enderror"
                                        name="booking_charges" value="{{ old('booking_charges', $plan->booking_charges) }}"
                                        step="0.01" min="0">
                                    @error('booking_charges')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ __('Active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="{{ route('re-payment-plans.index') }}"
                                    class="btn btn-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Update Plan') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
