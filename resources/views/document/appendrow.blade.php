<tr data-row_id="{{$document->id}}">
    <td>{{ $document->name }}</td>
    <td>
        <h6 class="float-left mr-1">
            @if( $document->is_required == 1 )
                <div class="doc_status_badge badge bg-primary p-2 px-3 rounded">{{__('Required')}}</div>
            @else
                <div class="doc_status_badge badge bg-danger p-2 px-3 rounded">{{__('Not Required')}}</div>
            @endif
        </h6>
    </td>

    @if(Gate::check('edit document type') || Gate::check('delete document type'))
        <td>
            @can('edit document type')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('document/'.$document->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Document Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan

            @can('delete document type')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['document.destroy', $document->id],'id'=>'delete-form-'.$document->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                    {!! Form::close() !!}
                </div>
            @endcan

        </td>
    @endif
</tr>