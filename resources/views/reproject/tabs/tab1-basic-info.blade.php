<form id="form-basic-info">
    @csrf
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-label">{{ __('Project Name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="project_name" name="name" required>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label class="form-label">{{ __('Project Code') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="project_code" name="code" required
                    style="text-transform: uppercase;" maxlength="20" placeholder="e.g. PRJ001">
                <small class="text-muted">{{ __('Unique code') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('City') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="city" name="city" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('Area') }}</label>
                <input type="text" class="form-control" id="area" name="area">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Address') }}</label>
                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('Total Floors') }} <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="total_floors" name="total_floors" min="1"
                    value="1" required>
                <small class="text-muted">{{ __('Enter number of floors to generate floor list in next step') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('Project Type') }} <span class="text-danger">*</span></label>
                <select class="form-control" id="project_type" name="type" required>
                    <option value="Residential">{{ __('Residential') }}</option>
                    <option value="Commercial">{{ __('Commercial') }}</option>
                    <option value="Mixed">{{ __('Mixed') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('Start Date') }}</label>
                <input type="date" class="form-control" id="start_date" name="start_date">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="form-label">{{ __('Expected Completion') }}</label>
                <input type="date" class="form-control" id="expected_completion" name="expected_completion">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{ __('Description') }}</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">
                {{ __('Save & Continue') }} <i class="ti ti-arrow-right"></i>
            </button>
        </div>
    </div>
</form>
