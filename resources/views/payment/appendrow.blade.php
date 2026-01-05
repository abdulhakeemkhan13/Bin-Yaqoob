<tr class="font-style" data-row_id="{{$payment->id}}">
    <td>{{  Auth::user()->dateFormat($payment->date)}}</td>
    <td>{{  Auth::user()->priceFormat($payment->amount)}}</td>
    <td>{{ !empty($payment->bankAccount)?$payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name:''}}</td>
    <td>{{  !empty($payment->vender)?$payment->vender->name:'-'}}</td>
    <td>
        @if(!empty($payment->category))
            {{ is_object($payment->category) ? $payment->category->name : $payment->category }}
        @else
            -
        @endif
    </td>
    <td>{{  !empty($payment->reference)?$payment->reference:'-'}}</td>
    <td>{{  !empty($payment->description)?$payment->description:'-'}}</td>
    <td>
        @if(!empty($payment->add_receipt))
            <a class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $paymentpath . '/' . $payment->add_receipt }}" download="">
                <i class="ti ti-download text-white"></i>
            </a>
            <a href="{{ $paymentpath . '/' . $payment->add_receipt }}"  class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span></a>
        @else
            -
        @endif

    </td>
    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
        <td class="action">
            @can('edit payment')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('payments.edit', $payment->id) }}" data-ajax-popup="true" data-title="{{__('Edit Payment')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete payment')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id],'id'=>'delete-form-'.$payment->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$payment->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
            @endcan
        </td>
    @endif
</tr>