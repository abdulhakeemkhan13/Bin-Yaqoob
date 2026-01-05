{{ Form::open(['route' => ['bill.custom.debit.note'], 'method' => 'post', 'data-ajax' => 'true', 'id' => 'debit-note-form']) }}
<div class="modal-body">
  <div class="row">
    <div class="form-group col-md-12">
      {{ Form::label('bill', __('Bill'), ['class'=>'form-label']) }}
      <select class="form-control select" required id="bill" name="bill">
        <option value="">{{ __('Select Bill') }}</option>
        @foreach($bills as $key => $bill)
          <option value="{{ $key }}">{{ \Auth::user()->billNumberFormat($bill) }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group col-md-6">
      {{ Form::label('amount', __('Amount'), ['class'=>'form-label']) }}
      {{-- add id="amount" so your AJAX can populate it --}}
      {{ Form::number('amount', null, ['id'=>'amount','class'=>'form-control','required'=>true,'step'=>'0.01','placeholder'=>__('Enter Amount')]) }}
    </div>

    <div class="form-group col-md-6">
      {{ Form::label('date', __('Date'), ['class'=>'form-label']) }}
      {{ Form::date('date', null, ['class'=>'form-control','required'=>true]) }}
    </div>

    <div class="form-group col-md-12">
      {{ Form::label('description', __('Description'), ['class'=>'form-label']) }}
      {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>2,'placeholder'=>__('Enter Description')]) !!}
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
  <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
