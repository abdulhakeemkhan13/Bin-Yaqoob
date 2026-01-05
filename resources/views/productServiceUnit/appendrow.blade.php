<tr data-row_id="{{$unit->id}}">
    <td>{{ $unit->name }}</td>
    <td class="Action">
        <span>
        @can('edit constant category')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('product-unit.edit',$unit->id) }}" data-ajax-popup="true" data-title="{{__('Edit Unit')}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
                </div>
            @endcan
            @can('delete constant category')
                <div class="action-btn bg-danger ms-2">

                {!! Form::open(['method' => 'DELETE', 'route' => ['product-unit.destroy', $unit->id],'id'=>'delete-form-'.$unit->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$unit->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                {!! Form::close() !!}
                </div>
            @endcan
        </span>
    </td>
</tr>