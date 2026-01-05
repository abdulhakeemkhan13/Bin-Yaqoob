<tr data-row_id="{{$department->id}}">
    <td>{{ !empty($department->branch)?$department->branch->name:'' }}</td>
    <td>{{ $department->name }}</td>

    <td class="Action">
        <span>
            @can('edit department')
            <div class="action-btn bg-primary ms-2">

                <a href="#" data-url="{{ URL::to('department/'.$department->id.'/edit') }}"  data-ajax-popup="true" data-title="{{__('Edit Department')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i></a>
            </div>
                @endcan
            @can('delete department')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['department.destroy', $department->id],'id'=>'delete-form-'.$department->id]) !!}


                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$department->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
                    </div>
            @endcan
        </span>
    </td>
</tr>