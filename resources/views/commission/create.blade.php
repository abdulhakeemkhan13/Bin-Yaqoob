{{ Form::open(['url' => 'commission', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Commission Title')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('commission_for', __('Commission For'), ['class' => 'form-label']) }}
            {{ Form::select('commission_for', $commission_for, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'commission_for']) }}
        </div>

        <div class="form-group col-md-6" id="employee_div">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::select('employee_id', ['' => __('Select Employee')] + $employees->toArray(), $selected_employee, ['class' => 'form-control select']) }}
        </div>

        <div class="form-group col-md-6 d-none" id="dealer_div">
            {{ Form::label('dealer_id', __('Dealer'), ['class' => 'form-label']) }}
            {{ Form::select('dealer_id', ['' => __('Select Dealer')] + $users->toArray(), null, ['class' => 'form-control select']) }}
        </div>

        <div class="form-group col-md-6 d-none" id="management_div">
            {{ Form::label('user_id', __('Management Person'), ['class' => 'form-label']) }}
            {{ Form::select('user_id', ['' => __('Select User')] + $users->toArray(), null, ['class' => 'form-control select']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('contract_id', __('Contract'), ['class' => 'form-label']) }}
            {{ Form::select('contract_id', ['' => __('Select Contract')] + $contracts->toArray(), $selected_contract, ['class' => 'form-control select']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('deal_id', __('Deal'), ['class' => 'form-label']) }}
            {{ Form::select('deal_id', ['' => __('Select Deal')] + $deals->toArray(), $selected_deal, ['class' => 'form-control select']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('type', __('Calculation Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $commission_types, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'calculation_type']) }}
        </div>

        <div class="form-group col-md-6" id="amount_div">
            {{ Form::label('amount', __('Commission Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Fixed Amount')]) }}
        </div>

        <div class="form-group col-md-6 d-none" id="percentage_div">
            {{ Form::label('commission_percentage', __('Commission Percentage (%)'), ['class' => 'form-label']) }}
            {{ Form::number('commission_percentage', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Percentage')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('release_condition_type', __('Release Condition'), ['class' => 'form-label']) }}
            {{ Form::select('release_condition_type', $release_condition_types, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'release_condition_type']) }}
        </div>

        <div class="form-group col-md-6 d-none" id="release_percentage_div">
            {{ Form::label('release_percentage', __('Release After Receiving (%)'), ['class' => 'form-label']) }}
            {{ Form::number('release_percentage', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('e.g. 80')]) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter any additional notes')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        // Toggle recipient type
        $('#commission_for').on('change', function() {
            var val = $(this).val();
            $('#employee_div').addClass('d-none');
            $('#dealer_div').addClass('d-none');
            $('#management_div').addClass('d-none');

            if (val == 'employee') {
                $('#employee_div').removeClass('d-none');
            } else if (val == 'dealer') {
                $('#dealer_div').removeClass('d-none');
            } else if (val == 'company_management') {
                $('#management_div').removeClass('d-none');
            }
        });

        // Toggle calculation type
        $('#calculation_type').on('change', function() {
            var val = $(this).val();
            if (val == 'fixed') {
                $('#amount_div').removeClass('d-none');
                $('#percentage_div').addClass('d-none');
            } else {
                $('#amount_div').addClass('d-none');
                $('#percentage_div').removeClass('d-none');
            }
        });

        // Toggle release condition
        $('#release_condition_type').on('change', function() {
            var val = $(this).val();
            if (val == 'percentage_received') {
                $('#release_percentage_div').removeClass('d-none');
            } else {
                $('#release_percentage_div').addClass('d-none');
            }
        });

        // Trigger changes on load to set initial state
        $('#commission_for').trigger('change');
        $('#calculation_type').trigger('change');
        $('#release_condition_type').trigger('change');
    });
</script>
