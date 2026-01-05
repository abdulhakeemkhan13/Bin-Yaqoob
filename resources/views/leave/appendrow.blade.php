<tr data-row_id="{{$leave->id}}">
    @if(\Auth::user()->type!='Employee')
        <td>{{ !empty($leave->employees) ? $leave->employees->name : '-'}}</td>
    @endif
    <td>{{ !empty($leave->leaveType) ? $leave->leaveType->title : '-'}}</td>
    <td>{{ \Auth::user()->dateFormat($leave->applied_on )}}</td>
    <td>{{ \Auth::user()->dateFormat($leave->start_date ) }}</td>
    <td>{{ \Auth::user()->dateFormat($leave->end_date )  }}</td>
        <td>{{ $leave->total_leave_days }}</td>
    <td>{{ $leave->leave_reason }}</td>
    <td>
        @if($leave->status=="Pending")<div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $leave->status }}</div>
        @elseif($leave->status=="Approved")
            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
        @else($leave->status=="Reject")
            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
        @endif
    </td>
    <td>
        @if(\Auth::user()->type == 'Employee')
            @if($leave->status == "Pending")
                @can('edit leave')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" data-url="{{ URL::to('leave/'.$leave->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Leave')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                </div>
                @endcan
            @endif
        @else
        <div class="action-btn bg-warning ms-2">
            <a href="#" data-url="{{ URL::to('leave/'.$leave->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Leave Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Leave Action')}}" data-original-title="{{__('Leave Action')}}">
                <i class="ti ti-caret-right text-white"></i> </a>
        </div>
            @can('edit leave')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ URL::to('leave/'.$leave->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Leave')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i></a>
            </div>
            @endcan
        @endif
        @can('delete leave')
        <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['leave.destroy', $leave->id],'id'=>'delete-form-'.$leave->id]) !!}
            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$leave->id}}').submit();">
            <i class="ti ti-trash text-white"></i></a>
            {!! Form::close() !!}
        </div>
        @endif
    </td>
</tr>