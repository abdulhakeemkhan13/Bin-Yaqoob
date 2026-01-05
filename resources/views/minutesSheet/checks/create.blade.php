{{ Form::open(['route' => 'minutes-sheet.check.store', 'method' => 'post', 'id' => 'check-form']) }}
{{ Form::hidden('minutes_sheet_id', $minutesSheet->id) }}
<div class="modal-body">
    {{-- Minutes Sheet Info --}}
    <div class="card bg-light mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>{{ __('Reference:') }}</strong> {{ $minutesSheet->reference_no }}</p>
                    <p class="mb-1"><strong>{{ __('Subject:') }}</strong> {{ $minutesSheet->subject }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>{{ __('Total Amount:') }}</strong>
                        {{ \Auth::user()->priceFormat($minutesSheet->amount) }}</p>
                    <p class="mb-1"><strong>{{ __('Remaining:') }}</strong> <span
                            class="text-success fw-bold">{{ \Auth::user()->priceFormat($remainingAmount) }}</span></p>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <p class="mb-0"><strong>{{ __('Bank Account:') }}</strong>
                        {{ $minutesSheet->bankAccount->bank_name ?? '-' }} -
                        {{ $minutesSheet->bankAccount->holder_name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Check Form --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('cheque_number', __('Check Number'), ['class' => 'form-label']) }}
                {{ Form::text('cheque_number', $checkNumber, ['class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('payee_name', __('Payee Name'), ['class' => 'form-label']) }} <span
                    class="text-danger">*</span>
                {{ Form::text('payee_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter payee name')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'max' => $remainingAmount, 'placeholder' => __('Max: ') . number_format($remainingAmount, 2)]) }}
                <small class="text-muted">{{ __('Maximum available:') }}
                    {{ \Auth::user()->priceFormat($remainingAmount) }}</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('notes', __('Notes (Optional)'), ['class' => 'form-label']) }}
                {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Optional notes')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create Check') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        $('#check-form').on('submit', function(e) {
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
