<tr class="font-style" data-row_id="{{$transfer->id}}">
    <td>{{ \Auth::user()->dateFormat($transfer->date) }}</td>
    <td>{{ !empty($transfer->fromBankAccount)? $transfer->fromBankAccount->bank_name.' '.$transfer->fromBankAccount->holder_name:''}}</td>
    <td>{{!empty($transfer->toBankAccount)? $transfer->toBankAccount->bank_name.' '. $transfer->toBankAccount->holder_name:''}}</td>
    <td>{{ \Auth::user()->priceFormat($transfer->amount)}}</td>
    <td>{{ $transfer->reference}}</td>
    <td>{{ $transfer->description}}</td>
    @if(Gate::check('edit bank transfer') || Gate::check('delete bank transfer'))
        <td class="Action">
            <span>
            @can('edit bank transfer')
                <div class="action-btn bg-primary ms-2">
                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('bank-transfer.edit',$transfer->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Transfer')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
            @endcan
            @can('delete bank transfer')
                <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['bank-transfer.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();">
                        <i class="ti ti-trash text-white text-white text-white"></i>
                    </a>
                {!! Form::close() !!}
                </div>
            @endcan
            </span>
        </td>
    @endif
</tr>
