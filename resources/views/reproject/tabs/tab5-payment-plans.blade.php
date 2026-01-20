<form id="form-payment-plans">
    <div class="alert alert-info mb-4">
        <i class="ti ti-info-circle"></i>
        {{ __('Select payment plans to assign to this project. Plans can be created from') }}
        <a href="{{ route('re-payment-plans.index') }}" target="_blank">{{ __('Payment Plans') }}</a> {{ __('menu.') }}
    </div>

    <div id="available-plans-container">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">{{ __('Loading available plans...') }}</p>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-6">
            <button type="button" class="btn btn-secondary" onclick="goToTab(4)">
                <i class="ti ti-arrow-left"></i> {{ __('Previous') }}
            </button>
        </div>
        <div class="col-6 text-end">
            <button type="submit" class="btn btn-primary">
                {{ __('Save & Continue') }} <i class="ti ti-arrow-right"></i>
            </button>
        </div>
    </div>
</form>
