<tr data-row_id="{{$complaint->id}}">
    <td>{{!empty( $complaint->complaintFrom)? $complaint->complaintFrom->name:'' }}</td>
    <td>{{ !empty($complaint->complaintAgainst)?$complaint->complaintAgainst->name:'' }}</td>
    <td>{{ $complaint->title }}</td>
    <td>{{ \Auth::user()->dateFormat( $complaint->complaint_date) }}</td>
    <td>{{ $complaint->description }}</td>
    @if(Gate::check('edit complaint') || Gate::check('delete complaint'))
        <td>

            @can('edit complaint')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('complaint/'.$complaint->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Complaint')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
           @endcan


            @can('delete complaint')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['complaint.destroy', $complaint->id],'id'=>'delete-form-'.$complaint->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$complaint->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
            @endcan


        </td>
    @endif
</tr>