{{ Form::open(['url' => 'minutes-sheet', 'method' => 'post', 'id' => 'minutes-sheet-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('reference_no', __('Reference No'), ['class' => 'form-label']) }}
                {{ Form::text('reference_no', $referenceNo, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('bank_account_id', __('Bank Account'), ['class' => 'form-label']) }}
                {{ Form::select('bank_account_id', $bankAccounts, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Bank Account')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
                {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 4]) }}
            </div>
        </div>
    </div>
    <div class="alert alert-info mt-3">
        <i class="ti ti-info-circle"></i>
        {{ __('Creating as:') }} <strong>{{ $userDesignation }}</strong>.
        {{ __('This will be automatically forwarded to') }}
        <strong>{{ \App\Models\MinutesSheet::getNextStage($userDesignation) }}</strong> {{ __('for approval.') }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        $('#minutes-sheet-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        show_toastr('Success', response.message, 'success');
                        $('#commonModal').modal('hide');
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    } else {
                        show_toastr('Error', response.error || 'Something went wrong',
                            'error');
                    }
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON?.error || 'Something went wrong';
                    show_toastr('Error', errorMessage, 'error');
                }
            });
        });
    });
</script>
