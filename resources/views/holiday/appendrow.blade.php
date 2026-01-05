<tr data-row_id="{{$holiday->id}}">
    <td>{{ $holiday->occasion }}</td>
    <td>{{ \Auth::user()->dateFormat($holiday->date) }}</td>
    <td>{{ \Auth::user()->dateFormat($holiday->end_date) }}</td>
    @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
        <td class="Action">
            <span>
                @can('edit holiday')
                    <div class="action-btn bg-primary ms-2">
                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('holiday.edit',$holiday->id) }}" data-ajax-popup="true" data-title="{{__('Edit Holiday')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('delete holiday')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id],'id'=>'delete-form-'.$holiday->id]) !!}
                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"  title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$holiday->id}}').submit();">
                                <i class="ti ti-trash text-white"></i>
                            </a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            </span>
        </td>
    @endif
</tr>