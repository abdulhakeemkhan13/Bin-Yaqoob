<tr data-row_id="{{$trainingtype->id}}">
    <td>{{ $trainingtype->name }}</td>

    <td>

        @can('edit training type')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('trainingtype.edit',$trainingtype->id) }}" data-ajax-popup="true" data-title="{{__('Edit Training Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan


        @can('delete training type')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['trainingtype.destroy', $trainingtype->id],'id'=>'delete-form-'.$trainingtype->id]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan


    </td>
</tr>