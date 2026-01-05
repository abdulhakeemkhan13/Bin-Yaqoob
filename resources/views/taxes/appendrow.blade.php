<tr data-row_id="{{$tax->id}}">
    <td class="font-style">{{ $tax->name }}</td>
    <td class="font-style">{{ $tax->rate }}</td>
    <td class="Action">
        <span>
        @can('edit constant tax')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('taxes.edit',$tax->id) }}" data-ajax-popup="true" data-title="{{__('Edit Tax Rate')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
                </div>
            @endcan
            @can('delete constant tax')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['taxes.destroy', $tax->id],'id'=>'delete-form-'.$tax->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$tax->id}}').submit();">
                <i class="ti ti-trash text-white"></i>
            </a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </span>
    </td>
</tr>