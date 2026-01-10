{{ Form::open(['route' => ['deals.convert.contract.store', $deal->id], 'method' => 'POST', 'data-ajax' => 'true', 'id' => 'convert-contract-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>{{ __('Converting Deal:') }}</strong> {{ $deal->name }}<br>
                <strong>{{ __('Value:') }}</strong>
                {{ \Auth::user()->priceFormat($deal->offered_price ?? ($deal->price ?? 0)) }}
            </div>
        </div>
        <div class="col-12 form-group">
            {{ Form::label('type', __('Contract Type'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::select('type', ['' => __('Select Contract Type')] + $contractTypes->toArray(), null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::date('start_date', $deal->created_at->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::date('end_date', $deal->expected_closing_date ?? now()->addMonths(1)->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Convert to Contract') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
