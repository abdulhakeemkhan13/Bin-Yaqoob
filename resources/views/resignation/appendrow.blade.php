<tr data-row_id="{{$resignation->id}}">
    @role('company')
    <td>{{ !empty($resignation->employee)?$resignation->employee->name:'' }}</td>
    @endrole
    <td>{{  \Auth::user()->dateFormat($resignation->notice_date) }}</td>
    <td>{{  \Auth::user()->dateFormat($resignation->resignation_date) }}</td>
    <td>{{ $resignation->description }}</td>
    @if(Gate::check('edit resignation') || Gate::check('delete resignation'))
        <td>

            @can('edit resignation')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg" data-url="{{ URL::to('resignation/'.$resignation->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Resignation')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan


            @can('delete resignation')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['resignation.destroy', $resignation->id],'id'=>'delete-form-'.$resignation->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$resignation->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>