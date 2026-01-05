<tr data-row_id="{{$goalTracking->id}}">
    <td>{{ !empty($goalTracking->goalType)?$goalTracking->goalType->name:'' }}</td>
    <td>{{$goalTracking->subject}}</td>
    <td>{{ !empty($goalTracking->branches)?$goalTracking->branches->name:'' }}</td>
    <td>{{$goalTracking->target_achievement}}</td>
    <td>{{\Auth::user()->dateFormat($goalTracking->start_date)}}</td>
    <td>{{\Auth::user()->dateFormat($goalTracking->end_date)}}</td>
    <td>
        @for($i=1; $i<=5; $i++)
            @if($goalTracking->rating < $i)
                <i class="fas fa-star"></i>
            @else
                <i class="text-warning fas fa-star"></i>
            @endif
        @endfor
    </td>
    <td>
        <div class="progress-wrapper">
            <span class="progress-percentage"><small class="font-weight-bold"></small>{{$goalTracking->progress}}%</span>
            <div class="progress progress-xs mt-2 w-100">
                <div class="progress-bar bg-{{Utility::getProgressColor($goalTracking->progress)}}" role="progressbar" aria-valuenow="{{$goalTracking->progress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$goalTracking->progress}}%;"></div>
            </div>
        </div>

    </td>
    @if( Gate::check('edit goal tracking') ||Gate::check('delete goal tracking'))
        <td>
            @can('edit goal tracking')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ route('goaltracking.edit',$goalTracking->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Goal Tracking')}}" class="mx-3 btn btn-sm align-items-center " data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i></a>
            </div>
                @endcan
            @can('delete goal tracking')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['goaltracking.destroy', $goalTracking->id],'id'=>'delete-form-'.$goalTracking->id]) !!}
                   <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$goalTracking->id}}').submit();">
                   <i class="ti ti-trash text-white"></i>
                    </a>
                {!! Form::close() !!}
            </div>
            @endcan
        </td>
    @endif
</tr>