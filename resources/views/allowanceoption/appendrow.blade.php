<tr data-row_id="{{$allowanceoption->id}}">
    <td>{{ $allowanceoption->name }}</td>
    <td>
        @can('edit allowance option')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('allowanceoption/'.$allowanceoption->id.'/edit') }}" data-ajax-popup="true"
                   data-title="{{__('Edit Document Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan

        @can('delete allowance option')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['allowanceoption.destroy', $allowanceoption->id],'id'=>'delete-form-'.$allowanceoption->id]) !!}
                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan

    </td>
</tr>