<tr data-row_id="{{$leavetype->id}}">
    <td>{{ $leavetype->title }}</td>
    <td>{{ $leavetype->days}}</td>

    <td>


        @can('edit leave type')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('leavetype/'.$leavetype->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Leave Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan

        @can('delete leave type')
            <div class="action-btn bg-danger ms-2">

                {!! Form::open(['method' => 'DELETE', 'route' => ['leavetype.destroy', $leavetype->id],'id'=>'delete-form-'.$leavetype->id]) !!}
                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$leavetype->id}}').submit();">
                    <i class="ti ti-trash text-white"></i>
                </a>
                {!! Form::close() !!}
            </div>
        @endcan

    </td>
</tr>