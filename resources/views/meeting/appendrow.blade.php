<tr data-row_id="{{$meeting->id}}">
    <td>{{ $meeting->title }}</td>
    <td>{{  \Auth::user()->dateFormat($meeting->date) }}</td>
    <td>{{  \Auth::user()->timeFormat($meeting->time) }}</td>
    @if(Gate::check('edit meeting') || Gate::check('delete meeting'))
        <td>
            @can('edit meeting')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ URL::to('meeting/'.$meeting->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Meeting')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
            </div>
            @endcan
            @can('delete meeting')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['meeting.destroy', $meeting->id],'id'=>'delete-form-'.$meeting->id]) !!}
                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$meeting->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
            </div>
            @endcan
        </td>
    @endif
</tr>