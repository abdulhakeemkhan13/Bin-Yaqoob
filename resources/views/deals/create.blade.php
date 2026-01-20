{{ Form::open(['url' => 'deals', 'data-ajax' => 'false', 'id' => 'deal-form', 'data-ajax' => 'true', 'id' => 'deal-form']) }}
<div class="modal-body">
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
            {{ Form::number('price', 0, ['class' => 'form-control', 'min' => 0]) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'client@example.com']) }}
        </div>

        {{-- Unit Selection Section --}}
        <div class="col-12">
            <hr>
            <h6>{{ __('Unit Details') }}</h6>
        </div>

        <div class="col-6 form-group">
            {{ Form::label('re_project_id', __('Select Project'), ['class' => 'form-label']) }}
            {{ Form::select('re_project_id', ['' => __('Select Project')] + $projects->toArray(), null, ['class' => 'form-control', 'id' => 're_project_id']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('re_tower_id', __('Select Tower / Block'), ['class' => 'form-label']) }}
            {{ Form::select('re_tower_id', ['' => __('Select Tower')], null, ['class' => 'form-control', 'id' => 're_tower_id']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('re_floor_id', __('Select Floor'), ['class' => 'form-label']) }}
            {{ Form::select('re_floor_id', ['' => __('Select Floor')], null, ['class' => 'form-control', 'id' => 're_floor_id']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('re_unit_id', __('Select Unit'), ['class' => 'form-label']) }}
            {{ Form::select('re_unit_id', ['' => __('Select Unit')], null, ['class' => 'form-control', 'id' => 're_unit_id']) }}
        </div>

        <div class="col-6 form-group">
            {{ Form::label('offered_price', __('Offered Price'), ['class' => 'form-label']) }}
            {{ Form::number('offered_price', null, ['class' => 'form-control', 'step' => '0.01', 'id' => 'offered_price']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('discount', __('Discount'), ['class' => 'form-label']) }}
            {{ Form::number('discount', null, ['class' => 'form-control', 'step' => '0.01', 'id' => 'discount']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('expected_closing_date', __('Expected Closing Date'), ['class' => 'form-label']) }}
            {{ Form::date('expected_closing_date', null, ['class' => 'form-control']) }}
        </div>
        {{-- End Unit Selection Section --}}

        @if (!$customFields->isEmpty())
            @include('customFields.formBuilder')
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).on("change", "#re_project_id", function() {
        var projectId = $(this).val();
        if (projectId) {
            $.ajax({
                url: '{{ route('deals.towers.json') }}',
                data: {
                    project_id: projectId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    $('#re_tower_id').empty().append(
                        '<option value="">{{ __('Select Tower') }}</option>');
                    $('#re_floor_id').empty().append(
                        '<option value="">{{ __('Select Floor') }}</option>');
                    $('#re_unit_id').empty().append(
                        '<option value="">{{ __('Select Unit') }}</option>');
                    $.each(data, function(key, value) {
                        $('#re_tower_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    });

    $(document).on("change", "#re_tower_id", function() {
        var towerId = $(this).val();
        var projectId = $('#re_project_id').val();
        if (towerId) {
            $.ajax({
                url: '{{ route('deals.floors.json') }}',
                data: {
                    tower_id: towerId,
                    project_id: projectId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    $('#re_floor_id').empty().append(
                        '<option value="">{{ __('Select Floor') }}</option>');
                    $('#re_unit_id').empty().append(
                        '<option value="">{{ __('Select Unit') }}</option>');
                    $.each(data, function(key, value) {
                        $('#re_floor_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    });

    $(document).on("change", "#re_floor_id", function() {
        var floorId = $(this).val();
        if (floorId) {
            $.ajax({
                url: '{{ route('deals.units.json') }}',
                data: {
                    floor_id: floorId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    $('#re_unit_id').empty().append(
                        '<option value="">{{ __('Select Unit') }}</option>');
                    $.each(data, function(key, value) {
                        $('#re_unit_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    });

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
</script>
