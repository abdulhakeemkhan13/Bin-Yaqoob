{{ Form::open(array('route' => ['leads.labels.store',$lead->id], 'id' => 'lead-label-form')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            <div class="row gutters-xs">
                @foreach ($labels as $label)
                    <div class="col-12 custom-control custom-checkbox mt-2 mb-2">
                        {{ Form::checkbox('labels[]',$label->id,(array_key_exists($label->id,$selected))?true:false,['class' => 'form-check-input','id'=>'labels_'.$label->id]) }}
                        {{ Form::label('labels_'.$label->id, ucfirst($label->name),['class'=>'custom-control-label ml-4 text-white p-2 px-3 rounded badge bg-'.$label->color]) }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn btn-primary">
</div>

{{Form::close()}}

<script>
    $(document).ready(function() {
        $("#lead-label-form").on("submit", function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var action = $(this).attr('action');
            
            $.ajax({
                url: action,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Update the card in the kanban board
                        if (data.data && data.data.card_html && data.data.card_container) {
                            var cardContainer = $("[data-card-container='" + data.data.card_container + "']");
                            if (cardContainer.length) {
                                if (data.data.action === 'edit') {
                                    $("[data-row_id='" + data.data.card_id + "']").replaceWith(data.data.card_html);
                                }
                            }
                        }
                        
                        // Close the modal
                        $('.modal').modal('hide');
                        
                        // Show success message
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.error);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.error);
                }
            });
        });
    });
</script>

