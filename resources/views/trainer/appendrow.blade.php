<tr data-row_id="{{$trainer->id}}">
    <td>{{ !empty($trainer->branches)?$trainer->branches->name:'' }}</td>
    <td>{{$trainer->firstname .' '.$trainer->lastname}}</td>
    <td>{{$trainer->contact}}</td>
    <td>{{$trainer->email}}</td>
    @if( Gate::check('edit trainer') ||Gate::check('delete trainer') || Gate::check('show trainer'))
        <td>
            @can('show trainer')
            <div class="action-btn bg-info ms-2">
                <a href="#" data-url="{{ route('trainer.show',$trainer->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Trainer Detail')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('View Detail')}}">
                <i class="ti ti-eye text-white"></i>
                </a>
            </div>
                @endcan
            @can('edit trainer')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ route('trainer.edit',$trainer->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Trainer')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
                @endcan
            @can('delete trainer')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['trainer.destroy', $trainer->id],'id'=>'delete-form-'.$trainer->id]) !!}

                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$trainer->id}}').submit();" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}">
                <i class="ti ti-trash text-white"></i>

                </a>
                {!! Form::close() !!}
            </div>
            @endcan
        </td>
    @endif
</tr>