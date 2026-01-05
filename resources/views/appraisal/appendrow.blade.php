@php
    $competencyCount = $competencyCount ?? 0;
    $designation = !empty($appraisal->employees) ? $appraisal->employees->designation->id : 0;
    $targetRating = Utility::getTargetrating($designation, $competencyCount);
    // Remove the dd() call as it breaks the AJAX response
    // dd($appraisal, $designation);
    
    if(!empty($appraisal->rating) && ($competencyCount != 0)) {
        $rating = json_decode($appraisal->rating, true);
        $overallrating = 0;
        if($rating) {
            $starsum = array_sum($rating);
            $overallrating = $starsum/$competencyCount;
        }
    } else {
        $overallrating = 0;
    }
@endphp

@php
    if(!empty($appraisal->rating)) {
        $rating = json_decode($appraisal->rating, true);
        $starsum = !empty($rating) ? array_sum($rating) : 0;
        $overallrating = ($starsum != 0) ? $starsum/count($rating) : 0;
    } else {
        $overallrating = 0;
    }
@endphp
<tr data-row_id="{{$appraisal->id}}">
    <td>{{ !empty($appraisal->branches)?$appraisal->branches->name:'' }}</td>
    <td>{{ !empty($appraisal->employees)?!empty($appraisal->employees->department)?$appraisal->employees->department->name:'':'' }}</td>
    <td>{{ !empty($appraisal->employees)?!empty($appraisal->employees->designation)?$appraisal->employees->designation->name:'':'' }}</td>
    <td>{{!empty($appraisal->employees)?$appraisal->employees->name:'' }}</td>

    <td >
        @for($i=1; $i<=5; $i++)
            @if($targetRating < $i)
                @if(is_float($targetRating) && (round($targetRating) == $i))
                    <i class="text-warning fas fa-star-half-alt"></i>
                @else
                    <i class="fas fa-star"></i>
                @endif
            @else
                <i class="text-warning fas fa-star"></i>
            @endif
        @endfor
        <span class="theme-text-color">({{number_format($targetRating,1)}})</span>
    </td>


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
    <td>{{ $appraisal->appraisal_date}}</td>
    @if( Gate::check('edit appraisal') ||Gate::check('delete appraisal') ||Gate::check('show appraisal'))
        <td>
            @can('show appraisal')
                <div class="action-btn bg-info ms-2">
                    <a href="#" data-url="{{ route('appraisal.show',$appraisal->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Appraisal Detail')}}" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('View Detail')}}" class="mx-3 btn btn-sm align-items-center">
                        <i class="ti ti-eye text-white"></i></a>
                </div>
            @endcan
            @can('edit appraisal')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" data-url="{{ route('appraisal.edit',$appraisal->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Appraisal')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}" class="mx-3 btn btn-sm align-items-center">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete appraisal')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['appraisal.destroy', $appraisal->id],'id'=>'delete-form-'.$appraisal->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$appraisal->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>
