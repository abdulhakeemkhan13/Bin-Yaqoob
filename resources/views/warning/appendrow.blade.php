<tr data-row_id="{{$warning->id}}">
    <td>{{!empty($warning->WarningBy)? $warning->WarningBy->name:'' }}</td>
    <td>{{ !empty($warning->warningTo)?$warning->warningTo->name:'' }}</td>
    <td>{{ $warning->subject }}</td>
    <td>{{  \Auth::user()->dateFormat($warning->warning_date) }}</td>
    <td>{{ $warning->description }}</td>
    @if(Gate::check('edit warning') || Gate::check('delete warning'))
        <td>
        

            @can('edit warning')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg" data-url="{{ URL::to('warning/'.$warning->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Warning')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
           @endcan


            @can('delete warning')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['warning.destroy', $warning->id],'id'=>'delete-form-'.$warning->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$warning->id}}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                    {!! Form::close() !!}
                </div>
            @endcan

        </td>

    @endif
</tr>