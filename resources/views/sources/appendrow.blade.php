<tr data-row_id="{{$source->id}}">
    <td>{{ $source->name }}</td>
    <td class="Active">

        @can('edit source')
            <div class="action-btn bg-info ms-2">
                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('sources/'.$source->id.'/edit') }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Source')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan
        @can('delete source')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['sources.destroy', $source->id]]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan
    </td>
</tr>