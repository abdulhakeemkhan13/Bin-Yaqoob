{{ Form::open(['route' => 'minutes-sheet.process-action', 'method' => 'post', 'id' => 'minutes-sheet-action-form']) }}
{{ Form::hidden('minutes_sheet_id', $minutesSheet->id) }}
<div class="modal-body">
    {{-- Summary Info --}}
    <div class="card bg-light mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>{{ __('Reference:') }}</strong> {{ $minutesSheet->reference_no }}</p>
                    <p class="mb-1"><strong>{{ __('Subject:') }}</strong> {{ $minutesSheet->subject }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>{{ __('Amount:') }}</strong>
                        {{ \Auth::user()->priceFormat($minutesSheet->amount) }}</p>
                    <p class="mb-1"><strong>{{ __('Created By:') }}</strong> {{ $minutesSheet->creator->name ?? '-' }}
                    </p>
                </div>
            </div>
            @if ($isRejected)
                <div class="alert alert-danger mt-2 mb-0">
                    <i class="ti ti-x"></i>
                    {{ __('This minutes sheet was rejected. You can resubmit (forward to next level) or send back to creator.') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Action Selection --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('action', __('Action'), ['class' => 'form-label']) }}

                @if ($isRejected)
                    {{-- For rejected sheets: Resubmit or Send Back --}}
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="action" id="action-resubmit" value="resubmit"
                            required>
                        <label class="btn btn-outline-success" for="action-resubmit">
                            <i class="ti ti-arrow-up"></i> {{ __('Resubmit (Forward)') }}
                        </label>
                        <input type="radio" class="btn-check" name="action" id="action-send-back" value="send_back">
                        <label class="btn btn-outline-warning" for="action-send-back">
                            <i class="ti ti-arrow-back-up"></i> {{ __('Send Back to Creator') }}
                        </label>
                    </div>
                @else
                    {{-- For pending sheets: Approve or Reject (step back) --}}
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="action" id="action-approve" value="approve"
                            required>
                        <label class="btn btn-outline-success" for="action-approve">
                            <i class="ti ti-check"></i> {{ __('Approve (Forward)') }}
                        </label>
                        <input type="radio" class="btn-check" name="action" id="action-reject" value="reject">
                        <label class="btn btn-outline-danger" for="action-reject">
                            <i class="ti ti-arrow-back"></i> {{ __('Reject (Send Back)') }}
                        </label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Remarks --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }} <span
                    class="text-danger">*</span>
                {{ Form::textarea('remarks', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 3, 'placeholder' => __('Enter your remarks (required)')]) }}
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('details', __('Additional Details'), ['class' => 'form-label']) }}
                {{ Form::textarea('details', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Optional additional details')]) }}
            </div>
        </div>
    </div>

    {{-- Info boxes for different actions --}}
    @if ($isRejected)
        <div class="alert alert-success mt-3" id="resubmit-info" style="display:none;">
            <i class="ti ti-info-circle"></i>
            @if ($nextStage)
                {{ __('Resubmitting will forward this to:') }} <strong>{{ $nextStage }}</strong>
            @else
                {{ __('Resubmitting will mark this as fully approved (you are at Chairman level).') }}
            @endif
        </div>
        <div class="alert alert-warning mt-3" id="send-back-info" style="display:none;">
            <i class="ti ti-alert-circle"></i>
            {{ __('This will restart the workflow. It will be sent to:') }}
            <strong>{{ \App\Models\MinutesSheet::getNextStage($creatorDesignation) ?? $creatorDesignation }}</strong>
        </div>
    @else
        <div class="alert alert-info mt-3" id="approve-info" style="display:none;">
            <i class="ti ti-info-circle"></i>
            @if ($isFinalStage)
                {{ __('As Chairman, your approval will mark this minutes sheet as fully approved.') }}
            @else
                {{ __('Approving will forward this to:') }} <strong>{{ $nextStage }}</strong>
            @endif
        </div>
        <div class="alert alert-warning mt-3" id="reject-info" style="display:none;">
            <i class="ti ti-alert-circle"></i>
            @if ($previousStage)
                {{ __('Rejecting will send this back to:') }} <strong>{{ $previousStage }}</strong>
                <br><small>{{ __('They can then resubmit or reject further.') }}</small>
            @else
                {{ __('Rejecting will mark this as rejected (no previous level to send back to).') }}
            @endif
        </div>
    @endif
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Submit') }}" class="btn btn-primary" id="submit-action-btn">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        // Show/hide info based on action selection
        $('input[name="action"]').on('change', function() {
            var action = $(this).val();

            // Hide all info boxes first
            $('#approve-info, #reject-info, #resubmit-info, #send-back-info').hide();

            // Show relevant info box
            if (action === 'approve') {
                $('#approve-info').show();
            } else if (action === 'reject') {
                $('#reject-info').show();
            } else if (action === 'resubmit') {
                $('#resubmit-info').show();
            } else if (action === 'send_back') {
                $('#send-back-info').show();
            }
        });

        // Form submission
        $('#minutes-sheet-action-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            // Validate action is selected
            if (!$('input[name="action"]:checked').val()) {
                show_toastr('Error', 'Please select an action', 'error');
                return;
            }

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
