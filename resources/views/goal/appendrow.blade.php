<tr data-row_id="{{$goal->id}}">
    <td class="font-style">{{ $goal->name }}</td>
    <td class="font-style"> {{ __(\App\Models\Goal::$goalType[$goal->type]) }} </td>
    <td class="font-style">{{ $goal->from }}</td>
    <td class="font-style">{{ $goal->to }}</td>
    <td class="font-style">{{ \Auth::user()->priceFormat($goal->amount) }}</td>
    <td class="font-style">{{$goal->is_display==1 ? __('Yes') :__('No')}}</td>
    <td class="Action">
        <span>
        @can('edit goal')
        <div class="action-btn bg-primary ms-2">
            <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('goal.edit',$goal->id) }}" data-ajax-popup="true" data-title="{{__('Edit Goal')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
            </a>
        </div>
            @endcan
            @can('delete goal')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['goal.destroy', $goal->id],'id'=>'delete-form-'.$goal->id]) !!}
                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$goal->id}}').submit();">
                    <i class="ti ti-trash text-white"></i>
                </a>
                {!! Form::close() !!}
            </div>
            @endcan
        </span>
    </td>
</tr>
