<tr data-row_id="{{$award->id}}">

    @role('company')
    <td>{{!empty($award->employee)? $award->employee->name:'' }}</td>
    @endrole
    <td>{{ !empty($award->awardType)?$award->awardType->name:'' }}</td>
    <td>{{  \Auth::user()->dateFormat($award->date )}}</td>
    <td>{{ $award->gift }}</td>
    <td>{{ $award->description }}</td>

    @if(Gate::check('edit award') || Gate::check('delete award'))
        <td class="Action">
            <span>
                @can('edit award')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" data-url="{{ URL::to('award/'.$award->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Award')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i></a>
                </div>
                    @endcan
                @can('delete award')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['award.destroy', $award->id],'id'=>'delete-form-'.$award->id]) !!}
                 <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$award->id}}').submit();">
                     <i class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                    </div>
                @endcan
            </span>
        </td>
    @endif
</tr>