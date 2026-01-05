<tr class="font-style" data-row_id="{{$revenue->id}}">
    <td>{{  Auth::user()->dateFormat($revenue->date)}}</td>
    <td>{{  Auth::user()->priceFormat($revenue->amount)}}</td>
    <td>{{ is_object($revenue->bankAccount) ? $revenue->bankAccount->bank_name.' '.$revenue->bankAccount->holder_name : ''}}</td>
    <td>{{ is_object($revenue->customer) ? $revenue->customer->name : '-'}}</td>
    <td>{{ is_object($revenue->category) ? $revenue->category->name : '-'}}</td>
    <td>{{  !empty($revenue->reference)?$revenue->reference:'-'}}</td>
    <td>{{  !empty($revenue->description)?$revenue->description:'-'}}</td>

    <td>
{{--                                        @if(!empty($revenue->add_receipt))--}}
{{--                                            <a href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" download="" class="action-btn bg-primary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-download text-white" ></i></span></a>--}}

{{--                                            <div class="action-btn bg-secondary">--}}
{{--                                                <a class="mx-3 btn btn-sm align-items-center" href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" target="_blank"  >--}}
{{--                                                    <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>--}}
{{--                                                </a>--}}
{{--                                            </div>--}}
{{--                                        @else--}}
{{--                                            ---}}
{{--                                        @endif--}}

        @if(!empty($revenue->add_receipt))
            <a  class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $revenuepath . '/' . $revenue->add_receipt }}" download="">
                <i class="ti ti-download text-white"></i>
            </a>
            <a href="{{ $revenuepath . '/' . $revenue->add_receipt }}"  class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span></a>
        @else
            -
        @endif

    </td>
    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
        <td class="Action">
            <span>
            @can('edit revenue')
                    <div class="action-btn bg-primary ms-2">
                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('revenue.edit',$revenue->id) }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                @endcan
                @can('delete revenue')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['revenue.destroy', $revenue->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$revenue->id]) !!}

                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$revenue->id}}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div>
                @endcan
            </span>
        </td>
    @endif
</tr>