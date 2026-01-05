<tr data-row_id="{{$announcement->id}}">
    <td>{{ $announcement->title }}</td>
    <td>{{  \Auth::user()->dateFormat($announcement->start_date) }}</td>
    <td>{{  \Auth::user()->dateFormat($announcement->end_date) }}</td>
    <td>{{ $announcement->description }}</td>
    @if(Gate::check('edit announcement') || Gate::check('delete announcement'))
        <td>

            @can('edit announcement')
                <div class="action-btn bg-primary ms-2">


                    <a href="#" data-url="{{ URL::to('announcement/'.$announcement->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Announcement')}}" class="x-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>

                </div>
            @endcan

            @can('delete announcement')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['announcement.destroy', $announcement->id],'id'=>'delete-form-'.$announcement->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$announcement->id}}').submit();">
                            <i class="ti ti-trash text-white text-white"></i>
                        </a>
                    {!! Form::close() !!}
                </div>
            @endcan

        </td>
    @endif
</tr>