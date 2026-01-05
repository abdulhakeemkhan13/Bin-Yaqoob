@foreach ($branches as $branch)
<tr data-row_id="{{$branch->id}}">
    <td>{{ $branch->name }}</td>
    <td class="Action text-end">
        <span>
            @can('edit branch')
                <div class="action-btn bg-primary ms-2">

                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('branch/'.$branch->id.'/edit') }}"  data-ajax-popup="true" data-title="{{__('Edit Branch')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
            </div>
            @endcan
            @can('delete branch')
                <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['branch.destroy', $branch->id],'id'=>'delete-form-'.$branch->id]) !!}

                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$branch->id}}').submit();"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
            @endcan
        </span>
    </td>
</tr>
@endforeach

