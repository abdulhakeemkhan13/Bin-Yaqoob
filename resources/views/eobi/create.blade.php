{{-- resources/views/eobi/create.blade.php --}}
{{ Form::open(['route' => 'eobis.store', 'method' => 'POST']) }}
<div class="modal-body">
    {{ Form::hidden('employee_id', $employee->id) }}
    <div class="form-group">
        {{ Form::label('title', __('Title')) }}
        {{ Form::text('title', 'EOBI', ['class' => 'form-control','required']) }}
    </div>
    <div class="form-group">
        {{ Form::label('amount', __('Amount')) }}
        {{ Form::number('amount', 0, ['class' => 'form-control','step'=>'0.01','min'=>'0','required']) }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
