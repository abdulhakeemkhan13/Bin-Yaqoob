<form id="form-towers">
    <div class="alert alert-info mb-3">
        <i class="ti ti-info-circle"></i>
        {{ __('Add towers/blocks for this project. Each tower can have its own set of floors and units.') }}
    </div>

    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <button type="button" class="btn btn-success btn-sm" onclick="addTowerRow()">
                <i class="ti ti-plus"></i> {{ __('Add Tower/Block') }}
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th style="width: 100px;">{{ __('Code') }} <span class="text-danger">*</span></th>
                    <th>{{ __('Tower Name') }}</th>
                    <th style="width: 100px;">{{ __('Floors') }} <span class="text-danger">*</span></th>
                    <th style="width: 120px;">{{ __('Construction') }}</th>
                    <th style="width: 120px;">{{ __('Parking Type') }}</th>
                    <th style="width: 100px;">{{ __('Elevators') }}</th>
                    <th style="width: 110px;">{{ __('Status') }}</th>
                    <th style="width: 60px;"></th>
                </tr>
            </thead>
            <tbody id="towers-container">
                <!-- Rows will be generated dynamically -->
            </tbody>
        </table>
    </div>
    <div class="row mt-3">
        <div class="col-6">
            <button type="button" class="btn btn-secondary" onclick="goToTab(1)">
                <i class="ti ti-arrow-left"></i> {{ __('Previous') }}
            </button>
        </div>
        <div class="col-6 text-end">
            {{-- <button type="button" class="btn btn-outline-secondary me-2" onclick="skipTowersTab()">
                {{ __('Skip (No Towers)') }}
            </button> --}}
            <button type="submit" class="btn btn-primary">
                {{ __('Save & Continue') }} <i class="ti ti-arrow-right"></i>
            </button>
        </div>
    </div>
</form>
