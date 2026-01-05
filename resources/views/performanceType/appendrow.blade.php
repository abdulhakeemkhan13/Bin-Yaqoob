<tr class="font-style" data-row_id="{{$type->id}}">
    <td>{{ $type->name }}</td>

    <td class="">

        <div class="action-btn bg-primary ms-2">
            <a href="#" data-url="{{ route('performanceType.edit',$type->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Performance Type')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>

        <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['performanceType.destroy', $type->id],'id'=>'delete-form-'.$type->id]) !!}
            <a href="#!" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" data-confirm-yes="document.getElementById('delete-form-{{$type->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
            {!! Form::close() !!}
        </div>

    </td>

</tr>