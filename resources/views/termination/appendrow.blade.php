<tr data-row_id="{{$termination->id}}">
    @role('company')
    <td>{{ !empty($termination->employee)?$termination->employee->name:'' }}</td>
    @endrole

    <td>{{ !empty($termination->terminationType)?$termination->terminationType->name:'' }}</td>
    <td>{{  \Auth::user()->dateFormat($termination->notice_date) }}</td>
    <td>{{  \Auth::user()->dateFormat($termination->termination_date) }}</td>
    <td>
        <a href="#" class="action-item" data-url="{{ route('termination.description',$termination->id) }}"
           data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Desciption')}}"
           data-title="{{__('Desciption')}}"><i class="fa fa-comment text-dark"></i></a>
    </td>
    @if(Gate::check('edit termination') || Gate::check('delete termination'))
        <td>

            @can('edit termination')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('termination/'.$termination->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Termination')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan

            @can('delete termination')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['termination.destroy', $termination->id],'id'=>'delete-form-'.$termination->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$termination->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
            @endcan

        </td>
    @endif
</tr>