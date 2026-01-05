<tr data-row_id="{{$travel->id}}">
    @role('company')
    <td>{{ !empty($travel->employee)?$travel->employee->name:'' }}</td>
    @endrole
    <td>{{ \Auth::user()->dateFormat( $travel->start_date) }}</td>
    <td>{{ \Auth::user()->dateFormat( $travel->end_date) }}</td>
    <td>{{ $travel->purpose_of_visit }}</td>
    <td>{{ $travel->place_of_visit }}</td>
    <td>{{ $travel->description }}</td>
    @if(Gate::check('edit travel') || Gate::check('delete travel'))
        <td>

            @can('edit travel')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('travel/'.$travel->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Trip')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
           @endcan


            @can('delete travel')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['travel.destroy', $travel->id],'id'=>'delete-form-'.$travel->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$travel->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
                </a>
                    {!! Form::close() !!}
                </div>
            @endcan


        </td>
    @endif
</tr>