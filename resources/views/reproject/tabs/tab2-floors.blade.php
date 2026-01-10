<form id="form-floors">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th style="width: 80px;">{{ __('Floor #') }}</th>
                    <th>{{ __('Floor Name') }}</th>
                    <th style="width: 150px;">{{ __('No. of Units') }} <span class="text-danger">*</span></th>
                </tr>
            </thead>
            <tbody id="floors-container">
                <!-- Rows will be generated dynamically based on total_floors -->
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
            <button type="submit" class="btn btn-primary">
                {{ __('Save & Continue') }} <i class="ti ti-arrow-right"></i>
            </button>
        </div>
    </div>
</form>
