{{ Form::model($deal, ['route' => ['deals.update', $deal->id], 'method' => 'PUT', 'data-ajax' => 'true', 'id' => 'deal-form']) }}
<div class="modal-body">
    {{-- start for ai module --}}
    @php
        $plan = \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if ($plan->chatgpt == 1)
        <div class="text-end">
            <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['deal']) }}" data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
    {{-- end for ai module --}}
    <div class="row">
        <div class="col-6 form-group">
            {{ Form::label('name', __('Deal Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
            {{ Form::number('price', null, ['class' => 'form-control']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('pipeline_id', __('Pipeline'), ['class' => 'form-label']) }}
            {{ Form::select('pipeline_id', $pipelines, null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('stage_id', __('Stage'), ['class' => 'form-label']) }}
            {{ Form::select('stage_id', ['' => __('Select Stage')], null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', ['Active' => 'Active', 'Won' => 'Won', 'Loss' => 'Loss'], null, ['class' => 'form-control']) }}
        </div>

        {{-- Unit Selection Section --}}
        <div class="col-12">
            <hr>
            <h6>{{ __('Unit Details') }}</h6>
        </div>

        <div class="col-4 form-group">
            {{ Form::label('re_project_id', __('Project/Tower'), ['class' => 'form-label']) }}
            {{ Form::select('re_project_id', ['' => __('Select Project')] + $projects, null, ['class' => 'form-control', 'id' => 're_project_id']) }}
        </div>
        <div class="col-4 form-group">
            {{ Form::label('re_floor_id', __('Floor'), ['class' => 'form-label']) }}
            {{ Form::select('re_floor_id', ['' => __('Select Floor')], null, ['class' => 'form-control', 'id' => 're_floor_id']) }}
        </div>
        <div class="col-4 form-group">
            {{ Form::label('re_unit_id', __('Unit'), ['class' => 'form-label']) }}
            {{ Form::select('re_unit_id', ['' => __('Select Unit')], null, ['class' => 'form-control', 'id' => 're_unit_id']) }}
        </div>
        <div class="col-4 form-group">
            {{ Form::label('offered_price', __('Offered Price'), ['class' => 'form-label']) }}
            {{ Form::number('offered_price', null, ['class' => 'form-control', 'step' => '0.01']) }}
        </div>
        <div class="col-4 form-group">
            {{ Form::label('discount', __('Discount'), ['class' => 'form-label']) }}
            {{ Form::number('discount', null, ['class' => 'form-control', 'step' => '0.01']) }}
        </div>
        <div class="col-4 form-group">
            {{ Form::label('expected_closing_date', __('Expected Closing Date'), ['class' => 'form-label']) }}
            {{ Form::date('expected_closing_date', null, ['class' => 'form-control']) }}
        </div>
        {{-- End Unit Selection Section --}}

        @if (!$customFields->isEmpty())
            @include('customFields.formBuilder')
        @endif
        <div class="col-12 form-group">
            {{ Form::label('sources', __('Sources'), ['class' => 'form-label']) }}
            {{ Form::select('sources[]', $sources, null, ['class' => 'form-control select2', 'multiple' => '', 'id' => 'choices-multiple3']) }}
        </div>
        {{-- <div class="col-12 form-group">
            {{ Form::label('products', __('Products'), ['class' => 'form-label']) }}
            {{ Form::select('products[]', $products, null, ['class' => 'form-control select2', 'multiple' => '', 'id' => 'choices-multiple4', 'required' => 'required']) }}
        </div> --}}
        <div class="col-12 form-group">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'summernote-simple']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}


<script>
    var stage_id = '{{ $deal->stage_id }}';
    var current_floor_id = '{{ $deal->re_floor_id }}';
    var current_unit_id = '{{ $deal->re_unit_id }}';

    $(document).ready(function() {
        // Stage dropdown logic
        $("#commonModal select[name=pipeline_id]").trigger('change');

        // If project is already selected, load floors
        if ($('#re_project_id').val()) {
            loadFloors($('#re_project_id').val(), current_floor_id, current_unit_id);
        }
    });

    // Pipeline -> Stage
    $(document).on("change", "#commonModal select[name=pipeline_id]", function() {
        $.ajax({
            url: '{{ route('stages.json') }}',
            data: {
                pipeline_id: $(this).val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#stage_id').empty();
                $("#stage_id").append(
                    '<option value="" selected="selected">{{ __('Select Stage') }}</option>');
                $.each(data, function(key, data) {
                    var select = '';
                    if (key == '{{ $deal->stage_id }}') {
                        select = 'selected';
                    }
                    $("#stage_id").append('<option value="' + key + '" ' + select + '>' +
                        data + '</option>');
                });
                $("#stage_id").val(stage_id);
                $('#stage_id').select2({
                    placeholder: "{{ __('Select Stage') }}"
                });
            }
        })
    });

    // Project -> Floor
    $(document).on("change", "#re_project_id", function() {
        loadFloors($(this).val(), null, null);
    });

    // Floor -> Unit
    $(document).on("change", "#re_floor_id", function() {
        loadUnits($(this).val(), null);
    });

    function loadFloors(projectId, selectedFloorId, selectedUnitId) {
        if (!projectId) {
            $('#re_floor_id').empty().append('<option value="">{{ __('Select Floor') }}</option>');
            $('#re_unit_id').empty().append('<option value="">{{ __('Select Unit') }}</option>');
            return;
        }
        $.ajax({
            url: '{{ route('deals.floors.json') }}',
            data: {
                project_id: projectId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#re_floor_id').empty();
                $('#re_floor_id').append('<option value="">{{ __('Select Floor') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (selectedFloorId && key == selectedFloorId) ? 'selected' : '';
                    $('#re_floor_id').append('<option value="' + key + '" ' + selected + '>' +
                        value + '</option>');
                });
                // If we have a pre-selected floor, load units
                if (selectedFloorId) {
                    loadUnits(selectedFloorId, selectedUnitId);
                }
            }
        });
    }

    function loadUnits(floorId, selectedUnitId) {
        if (!floorId) {
            $('#re_unit_id').empty().append('<option value="">{{ __('Select Unit') }}</option>');
            return;
        }
        $.ajax({
            url: '{{ route('deals.units.json') }}',
            data: {
                floor_id: floorId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#re_unit_id').empty();
                $('#re_unit_id').append('<option value="">{{ __('Select Unit') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (selectedUnitId && key == selectedUnitId) ? 'selected' : '';
                    $('#re_unit_id').append('<option value="' + key + '" ' + selected + '>' +
                        value + '</option>');
                });
            }
        });
    }
</script>
