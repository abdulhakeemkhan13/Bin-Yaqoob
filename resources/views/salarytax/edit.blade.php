{{-- resources/views/salarytax/edit.blade.php --}}
{{ Form::model($salarytax, ['route' => ['salarytax.update',$salarytax->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('title', __('Title')) }}
        {{ Form::text('title', null, ['class' => 'form-control','required']) }}
    </div>
    <div class="form-group">
        {{ Form::label('amount', __('Amount')) }}
        {{ Form::number('amount', null, ['class' => 'form-control','step'=>'0.01','min'=>'0','required']) }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
