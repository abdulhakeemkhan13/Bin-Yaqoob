@if (!empty($customer))
    <div class="row">
        <div class="col-md-10">
            <h6>{{ __('Customer Details') }}</h6>
            <div class="customer-info">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Name') }}</small>
                        <p class="mb-1"><strong>{{ $customer->name ?? '-' }}</strong></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Email') }}</small>
                        <p class="mb-1">{{ $customer->email ?? '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Contact') }}</small>
                        <p class="mb-1">{{ $customer->contact ?? '-' }}</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <small class="text-muted">{{ __('CNIC') }}</small>
                        <p class="mb-1">{{ $customer->cnic_number ?? '-' }}</p>
                    </div>
                    <div class="col-md-8">
                        <small class="text-muted">{{ __('Address') }}</small>
                        <p class="mb-1">{{ $customer->billing_address ?? ($customer->current_address ?? '-') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm text-danger">{{ __('Remove') }}</a>
        </div>
    </div>
@endif
