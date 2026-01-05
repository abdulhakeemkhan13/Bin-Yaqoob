<tr data-row_id="{{$deductionoption->id}}">
    <td>{{ $deductionoption->name }}</td>
    <td>
        @can('edit deduction option')
            <div class="action-btn bg-primary ms-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('deductionoption/'.$deductionoption->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Deduction Option')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan

        @can('delete deduction option')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['deductionoption.destroy', $deductionoption->id],'id'=>'delete-form-'.$deductionoption->id]) !!}
                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                {!! Form::close() !!}
            </div>
        @endcan


    </td>
</tr>