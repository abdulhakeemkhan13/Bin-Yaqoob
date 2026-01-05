<tr data-row_id="{{$purchase->id}}">
    <td class="Id">
        <a href="{{ route('purchase.show',\Crypt::encrypt($purchase->id)) }}" class="btn btn-outline-primary">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</a>

    </td>

    <td> {{ (!empty( $purchase->vender)?$purchase->vender->name:'') }} </td>

    <td>{{ !empty($purchase->category)?$purchase->category->name:''}}</td>
    <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>

    <td>
        @if($purchase->status == 0)
            <span class="purchase_status badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
        @elseif($purchase->status == 1)
            <span class="purchase_status badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
        @elseif($purchase->status == 2)
            <span class="purchase_status badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
        @elseif($purchase->status == 3)
            <span class="purchase_status badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
        @elseif($purchase->status == 4)
            <span class="purchase_status badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
        @endif
    </td>



    @if(Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
        <td class="Action">
            <span>

                @can('show purchase')
                    <div class="action-btn bg-info ms-2">
                            <a href="{{ route('purchase.show',\Crypt::encrypt($purchase->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                <i class="ti ti-eye text-white"></i>
                            </a>
                        </div>
                @endcan
                @can('edit purchase')
                    <div class="action-btn bg-primary ms-2">
                        <a href="{{ route('purchase.edit',\Crypt::encrypt($purchase->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit" data-original-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('delete purchase')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['purchase.destroy', $purchase->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$purchase->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$purchase->id}}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            </span>
        </td>
    @endif
</tr>