<tr class="cust_tr" id="cust_detail" data-url="{{route('customer.show',$customer['id'])}}" data-id="{{$customer['id']}}" data-row_id="{{$customer['id']}}">
    <td class="Id">
        @can('show customer')
            <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="btn btn-outline-primary">
                {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}
            </a>
        @else
            <a href="#" class="btn btn-outline-primary">
                {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}
            </a>
        @endcan
    </td>
    <td class="font-style">{{$customer['name']}}</td>
    <td>{{$customer['contact']}}</td>
    <td>{{$customer['email']}}</td>
    <td>{{\Auth::user()->priceFormat($customer['balance'])}}</td>
    <td class="Action">
        <span>
        @if($customer['is_active']==0)
                <i class="ti ti-lock" title="Inactive"></i>
            @else
                @can('show customer')
                <div class="action-btn bg-info ms-2">
                    <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="mx-3 btn btn-sm align-items-center"
                       data-bs-toggle="tooltip" title="{{__('View')}}">
                        <i class="ti ti-eye text-white text-white"></i>
                    </a>
                </div>
                @endcan
                @can('edit customer')
                        <div class="action-btn bg-primary ms-2">
                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{ route('customer.edit',$customer['id']) }}" data-ajax-popup="true"  data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{__('Edit Customer')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('delete customer')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['customer.destroy', $customer['id']],'id'=>'delete-form-'.$customer['id']]) !!}
                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" ><i class="ti ti-trash text-white text-white"></i></a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            @endif
        </span>
    </td>
</tr>