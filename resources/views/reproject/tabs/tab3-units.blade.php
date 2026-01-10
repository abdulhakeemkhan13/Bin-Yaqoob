<form id="form-units">
    <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle"></i>
        {{ __('Click on each floor to expand and add/edit units. Units are auto-generated based on the number you specified for each floor.') }}
    </div>

    <div class="accordion" id="floorsAccordion">
        <!-- Floors will be dynamically loaded here -->
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">{{ __('Loading floors...') }}</p>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-6">
            <button type="button" class="btn btn-secondary" onclick="goToTab(2)">
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
