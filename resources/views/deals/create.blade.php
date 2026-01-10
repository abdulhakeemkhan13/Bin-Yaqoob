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
        @if (!$customFields->isEmpty())
            {{-- <div class="col-6 form-group">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel"> --}}
            @include('customFields.formBuilder')
            {{-- </div>
            </div> --}}
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
