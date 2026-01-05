<tr data-row_id="{{$bill->id}}">
    <td class="Id">
        <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->billNumberFormat($bill->bill_id) }}</a>
    </td>
    <td>{{ !empty($bill->category)?$bill->category->name:'-'}}</td>
    <td>{{ Auth::user()->dateFormat($bill->bill_date) }}</td>
    <td>{{ Auth::user()->dateFormat($bill->due_date) }}</td>
    <td>
        @if($bill->status == 0)
            <span class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
        @elseif($bill->status == 1)
            <span class="status_badge badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
        @elseif($bill->status == 2)
            <span class="status_badge badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
        @elseif($bill->status == 3)
            <span class="status_badge badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
        @elseif($bill->status == 4)
            <span class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
        @endif
    </td>
    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
        <td class="Action">
            <span>
                @can('duplicate bill')
                    <div class="action-btn bg-primary ms-2">
                        {!! Form::open(['method' => 'get', 'route' => ['bill.duplicate', $bill->id],'id'=>'duplicate-form-'.$bill->id]) !!}

                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}" data-bs-toggle="tooltip" title="{{__('Duplicate Bill')}}" data-original-title="{{__('Delete')}}" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$bill->id}}').submit();">
                        <i class="ti ti-copy text-white"></i>
                            {!! Form::close() !!}
                        </a>
                    </div>
                @endcan
                @can('show bill')
                    <div class="action-btn bg-info ms-2">
                        <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                            <i class="ti ti-eye text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('edit bill')
                    <div class="action-btn bg-primary ms-2">
                        <a href="{{ route('bill.edit',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit" data-original-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('delete bill')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['bill.destroy', $bill->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$bill->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$bill->id}}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            </span>
        </td>
    @endif
</tr>