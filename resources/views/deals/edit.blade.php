{{ Form::model($deal, ['route' => ['deals.update', $deal->id], 'method' => 'PUT', 'data-ajax' => 'true', 'id' => 'deal-form']) }}
<div class="modal-body">
    <style>
        .note-editable{color:#000 !important;background-color:#fff !important}
    </style>
    {{-- start for ai module --}}
    {{-- @php
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
    @endif --}}
    {{-- end for ai module --}}
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-general-tab" data-bs-toggle="pill" data-bs-target="#pills-general"
                type="button" role="tab" aria-controls="pills-general"
                aria-selected="true">{{ __('General') }}</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-client-tab" data-bs-toggle="pill" data-bs-target="#pills-client"
                type="button" role="tab" aria-controls="pills-client"
                aria-selected="false">{{ __('Client Detail') }}</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-general" role="tabpanel" aria-labelledby="pills-general-tab">
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
                    {{ Form::label('re_project_id', __('Select Project'), ['class' => 'form-label']) }}
                    {{ Form::select('re_project_id', ['' => __('Select Project')] + $projects, null, ['class' => 'form-control', 'id' => 're_project_id']) }}
                </div>
                <div class="col-4 form-group">
                    {{ Form::label('re_tower_id', __('Select Tower / Block'), ['class' => 'form-label']) }}
                    {{ Form::select('re_tower_id', ['' => __('Select Tower')], null, ['class' => 'form-control', 'id' => 're_tower_id']) }}
                </div>
                <div class="col-4 form-group">
                    {{ Form::label('re_floor_id', __('Select Floor'), ['class' => 'form-label']) }}
                    {{ Form::select('re_floor_id', ['' => __('Select Floor')], null, ['class' => 'form-control', 'id' => 're_floor_id']) }}
                </div>
                <div class="col-4 form-group">
                    {{ Form::label('re_unit_id', __('Select Unit'), ['class' => 'form-label']) }}
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
                <div class="col-12 form-group">
                    {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                    {{ Form::textarea('notes', null, ['class' => 'summernote-simple']) }}
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-client" role="tabpanel" aria-labelledby="pills-client-tab">
            <div class="row">
                <div class="col-6 form-group">
                    {{ Form::label('customer_type', __('Customer Type'), ['class' => 'form-label']) }}
                    {{ Form::select('customer_type', ['Individual' => 'Individual', 'Company' => 'Company'], null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('full_name', __('Full Name'), ['class' => 'form-label']) }}
                    {{ Form::text('full_name', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('father_or_company_name', __('Father / Company Name'), ['class' => 'form-label']) }}
                    {{ Form::text('father_or_company_name', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('cnic_or_ntn', __('CNIC / NTN'), ['class' => 'form-label']) }}
                    {{ Form::text('cnic_or_ntn', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('mobile_primary', __('Mobile Primary'), ['class' => 'form-label']) }}
                    {{ Form::text('mobile_primary', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('mobile_secondary', __('Mobile Secondary'), ['class' => 'form-label']) }}
                    {{ Form::text('mobile_secondary', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                    {{ Form::email('email', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('nationality', __('Nationality'), ['class' => 'form-label']) }}
                    {{ Form::text('nationality', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('current_address', __('Current Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('current_address', null, ['class' => 'form-control', 'rows' => 2]) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('permanent_address', __('Permanent Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('permanent_address', null, ['class' => 'form-control', 'rows' => 2]) }}
                </div>

                <div class="col-12">
                    <hr>
                    <h6>{{ __('Nominee Details') }}</h6>
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('nominee_name', __('Nominee Name'), ['class' => 'form-label']) }}
                    {{ Form::text('nominee_name', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('nominee_relation', __('Nominee Relation'), ['class' => 'form-label']) }}
                    {{ Form::text('nominee_relation', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('nominee_cnic', __('Nominee CNIC'), ['class' => 'form-label']) }}
                    {{ Form::text('nominee_cnic', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('nominee_contact', __('Nominee Contact'), ['class' => 'form-label']) }}
                    {{ Form::text('nominee_contact', null, ['class' => 'form-control']) }}
                </div>
            </div>
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
    var current_project_id = '{{ $deal->re_project_id }}';
    var current_tower_id = '{{ $deal->re_tower_id }}';
    var current_floor_id = '{{ $deal->re_floor_id }}';
    var current_unit_id = '{{ $deal->re_unit_id }}';

    $(document).ready(function() {
        // Stage dropdown logic
        $("#commonModal select[name=pipeline_id]").trigger('change');

        // Initial load if project is selected
        if (current_project_id) {
            loadTowers(current_project_id, current_tower_id, current_floor_id, current_unit_id);
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
                $('#stage_id').empty().append(
                    '<option value="" selected="selected">{{ __('Select Stage') }}</option>');
                $.each(data, function(key, data) {
                    var select = (key == stage_id) ? 'selected' : '';
                    $("#stage_id").append('<option value="' + key + '" ' + select + '>' +
                        data + '</option>');
                });
                $('#stage_id').select2({
                    placeholder: "{{ __('Select Stage') }}"
                });
            }
        })
    });

    // Project -> Tower
    $(document).on("change", "#re_project_id", function() {
        loadTowers($(this).val(), null, null, null);
    });

    // Tower -> Floor
    $(document).on("change", "#re_tower_id", function() {
        loadFloors($('#re_project_id').val(), $(this).val(), null, null);
    });

    // Floor -> Unit
    $(document).on("change", "#re_floor_id", function() {
        loadUnits($(this).val(), null);
    });

    // Unit -> Price
    $(document).on("change", "#re_unit_id", function() {
        var unitId = $(this).val();
        if (unitId) {
            $.ajax({
                url: '{{ route('deals.unit.detail.json') }}',
                data: {
                    unit_id: unitId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    if (data.success) {
                        $('#offered_price').val(data.price);
                        $('#price').val(data.price);
                    }
                }
            });
        }
    });

    function loadTowers(projectId, selectedTowerId, selectedFloorId, selectedUnitId) {
        if (!projectId) {
            $('#re_tower_id').empty().append('<option value="">{{ __('Select Tower') }}</option>');
            $('#re_floor_id').empty().append('<option value="">{{ __('Select Floor') }}</option>');
            $('#re_unit_id').empty().append('<option value="">{{ __('Select Unit') }}</option>');
            return;
        }
        $.ajax({
            url: '{{ route('deals.towers.json') }}',
            data: {
                project_id: projectId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#re_tower_id').empty().append('<option value="">{{ __('Select Tower') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (selectedTowerId && key == selectedTowerId) ? 'selected' : '';
                    $('#re_tower_id').append('<option value="' + key + '" ' + selected + '>' +
                        value + '</option>');
                });
                if (selectedTowerId) {
                    loadFloors(projectId, selectedTowerId, selectedFloorId, selectedUnitId);
                }
            }
        });
    }

    function loadFloors(projectId, towerId, selectedFloorId, selectedUnitId) {
        if (!projectId || !towerId) {
            $('#re_floor_id').empty().append('<option value="">{{ __('Select Floor') }}</option>');
            $('#re_unit_id').empty().append('<option value="">{{ __('Select Unit') }}</option>');
            return;
        }
        $.ajax({
            url: '{{ route('deals.floors.json') }}',
            data: {
                project_id: projectId,
                tower_id: towerId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            success: function(data) {
                $('#re_floor_id').empty().append('<option value="">{{ __('Select Floor') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (selectedFloorId && key == selectedFloorId) ? 'selected' : '';
                    $('#re_floor_id').append('<option value="' + key + '" ' + selected + '>' +
                        value + '</option>');
                });
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
                $('#re_unit_id').empty().append('<option value="">{{ __('Select Unit') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (selectedUnitId && key == selectedUnitId) ? 'selected' : '';
                    $('#re_unit_id').append('<option value="' + key + '" ' + selected + '>' +
                        value + '</option>');
                });
            }
        });
    }
</script>
