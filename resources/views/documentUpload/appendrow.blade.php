
<tr data-row_id="{{$document->id}}">
    <td>{{ $document->name }}</td>
    <td>
        @if (!empty($document->document))
            <div class="action-btn bg-primary ms-2">
                <a class="mx-3 btn btn-sm align-items-center"
                   href="{{ $documentPath . '/' . $document->document }}" download>
                    <i class="ti ti-download text-white"></i>
                </a>
            </div>
            <div class="action-btn bg-secondary ms-2">
                <a class="mx-3 btn btn-sm align-items-center" href="{{ $documentPath . '/' . $document->document }}" target="_blank"  >
                    <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                </a>
            </div>
        @else
            <p>-</p>
        @endif
    </td>
    <td>{{ !empty($roles)?$roles->name:'All' }}</td>
    <td>{{ $document->description }}</td>
    @if(Gate::check('edit document') || Gate::check('delete document'))
        <td>
            @can('edit document')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ route('document-upload.edit',$document->id)}}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Document')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
            </div>
                @endcan
            @can('delete document')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['document-upload.destroy', $document->id],'id'=>'delete-form-'.$document->id]) !!}

                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$document->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
            </div>
            @endcan
        </td>
    @endif
</tr>
