<tr data-row_id="{{$transfer->id}}">
    @role('company')
    <td>{{ is_object($transfer->employee) ? $transfer->employee->name : '' }}</td>
    @endrole
    <td>{{ is_object($transfer->branch) ? $transfer->branch->name : '' }}</td>
    <td>{{ is_object($transfer->department) ? $transfer->department->name : '' }}</td>
    <td>{{  \Auth::user()->dateFormat($transfer->transfer_date) }}</td>
    <td>{{ $transfer->description }}</td>
    @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
        <td>
            @can('edit transfer')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ URL::to('transfer/'.$transfer->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Transfer')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
                @endcan
            @can('delete transfer')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['transfer.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id]) !!}

                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
                </a>
                {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>