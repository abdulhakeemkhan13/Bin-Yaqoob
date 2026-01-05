<tr data-row_id="{{$promotion->id}}">
    @role('company')
    <td>{{ !empty($promotion->employee)?$promotion->employee->name:'' }}</td>
    @endrole
    <td>{{ !empty($promotion->designation)?$promotion->designation->name:'' }}</td>
    <td>{{ $promotion->promotion_title }}</td>
    <td>{{  \Auth::user()->dateFormat($promotion->promotion_date) }}</td>
    <td>{{ $promotion->description }}</td>
    @if(Gate::check('edit promotion') || Gate::check('delete promotion'))
        <td>
         
           @can('edit promotion')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('promotion/'.$promotion->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Promotion')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
           @endcan

        

            @can('delete promotion')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['promotion.destroy', $promotion->id],'id'=>'delete-form-'.$promotion->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$promotion->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
                </a>
                    {!! Form::close() !!}
                </div>
            @endcan

        </td>
    @endif
</tr>