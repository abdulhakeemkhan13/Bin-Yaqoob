<form id="form-floors">
    <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle"></i>
        {{ __('Define floors for each tower/block. Each floor will contain units that can be sold.') }}
    </div>

    <!-- Tower Tabs -->
    <ul class="nav nav-tabs" id="tower-tabs" role="tablist">
        <!-- Tabs will be generated dynamically -->
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="tower-tabs-content">
        <!-- Floor tables per tower will be generated dynamically -->
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
