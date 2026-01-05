<tr data-row_id="{{$loanoption->id}}">
    <td>{{ $loanoption->name }}</td>
    <td>
        @can('edit loan option')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('loanoption/'.$loanoption->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Loan Option')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan
        @can('delete loan option')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['loanoption.destroy', $loanoption->id],'id'=>'delete-form-'.$loanoption->id]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan

    </td>
</tr>