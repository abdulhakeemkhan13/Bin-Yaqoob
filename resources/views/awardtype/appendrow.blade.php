<tr data-row_id="{{$awardtype->id}}">
    <td>{{ $awardtype->name }}</td>
    <td>
        @can('edit award type')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('awardtype/'.$awardtype->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Award Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan

        @can('delete award type')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['awardtype.destroy', $awardtype->id],'id'=>'delete-form-'.$awardtype->id]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan

    </td>
</tr>