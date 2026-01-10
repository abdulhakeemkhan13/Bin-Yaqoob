<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle"></i>
            {{ __('Please review all the information below before final submission. Once submitted, the project will be activated.') }}
        </div>
    </div>
</div>

<div id="review-content">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('Loading...') }}</span>
        </div>
        <p class="mt-2">{{ __('Loading project data...') }}</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-6">
        <button type="button" class="btn btn-secondary" onclick="goToTab(4)">
            <i class="ti ti-arrow-left"></i> {{ __('Previous') }}
        </button>
    </div>
    <div class="col-6 text-end">
        <button type="button" class="btn btn-success btn-lg" id="btn-final-submit">
            <i class="ti ti-check"></i> {{ __('Submit & Activate Project') }}
        </button>
    </div>
</div>
