@php
    if(!empty($indicator->rating)){
        $rating = json_decode($indicator->rating,true);
        if(!empty($rating)){
            $starsum = array_sum($rating);
            $overallrating = $starsum/count($rating);
        }else{
                $overallrating = 0;
        }

    }
    else{
        $overallrating = 0;
    }
@endphp
<tr data-row_id="{{$indicator->id}}">
    <td>{{ !empty($indicator->branches)?$indicator->branches->name:'' }}</td>
    <td>{{ !empty($indicator->departments)?$indicator->departments->name:'' }}</td>
    <td>{{ !empty($indicator->designations)?$indicator->designations->name:'' }}</td>
    <td>

        @for($i=1; $i<=5; $i++)
            @if($overallrating < $i)
                @if(is_float($overallrating) && (round($overallrating) == $i))
                    <i class="text-warning fas fa-star-half-alt"></i>
                @else
                    <i class="fas fa-star"></i>
                @endif
            @else
                <i class="text-warning fas fa-star"></i>
            @endif
        @endfor
        <span class="theme-text-color">({{number_format($overallrating,1)}})</span>
    </td>


    <td>{{ !empty($indicator->user)?$indicator->user->name:'' }}</td>
    <td>{{ \Auth::user()->dateFormat($indicator->created_at) }}</td>
    @if( Gate::check('edit indicator') ||Gate::check('delete indicator') || Gate::check('show indicator'))
        <td>
            @can('show indicator')
            <div class="action-btn bg-info ms-2">
                <a href="#" data-url="{{ route('indicator.show',$indicator->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Indicator Detail')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('View Detail')}}">
                    <i class="ti ti-eye text-white"></i></a>
            </div>
            @endcan
            @can('edit indicator')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ route('indicator.edit',$indicator->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Indicator')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i></a>
            </div>
                @endcan
            @can('delete indicator')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['indicator.destroy', $indicator->id],'id'=>'delete-form-'.$indicator->id]) !!}

                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$indicator->id}}').submit();">
                <i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>