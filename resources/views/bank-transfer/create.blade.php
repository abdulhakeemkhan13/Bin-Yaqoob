{{Form::open(array('url'=>'bank-transfer','method'=>'post', 'data-ajax'=>'true', 'id'=>'bank-transfer-form'))}}
<div class="modal-body">

    <div class="row">
        <div class="form-group  col-md-6">
            {{ Form::label('from_account', __('From Account'),['class'=>'form-label']) }}
            {{ Form::select('from_account', $bankAccount,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('to_account', __('To Account'),['class'=>'form-label']) }}
            {{ Form::select('to_account', $bankAccount,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Form::number('amount', '', array('class' => 'form-control','required'=>'required','step'=>'0.01' , 'placeholder'=>__('Enter Amount'))) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
            {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Form::text('reference', '', array('class' => 'form-control' , 'placeholder'=>__('Enter Reference'))) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3 ,'required'=>'required' , 'placeholder'=>__('Enter Description'))) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

@push('script-page')
<script>
$(document).ready(function() {
    $('#bank-transfer-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = "{{ route('bank-transfer.store') }}"; // Hardcode the correct URL
        
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(data) {
                if(data.success) {
                    show_toastr('Success', data.message, 'success');
                    $('#commonModal').modal('hide');
                    if(data.data) {
                        // Handle data response
                        if(data.data.table_id && data.data.action) {
                            var table_id = data.data.table_id;
                            var action = data.data.action;
                            var row_id = data.data.row_id;
                            
                            if(action == 'add') {
                                $('#'+table_id+'-table-body').append(data.data.datarow);
                            } else if(action == 'edit') {
                                $('tr[data-row_id="'+row_id+'"]').replaceWith(data.data.datarow);
                            }
                        }
                    }
                } else {
                    show_toastr('Error', data.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                show_toastr('Error', 'Something went wrong!', 'error');
            }
        });
    });
});
</script>
@endpush
