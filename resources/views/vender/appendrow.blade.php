<tr class="cust_tr" data-url="{{ route('vender.show', \Crypt::encrypt($vender['id'])) }}" data-id="{{ $vender['id'] }}" data-row_id="{{ $vender['id'] }}">
    <td class="Id">
        @can('show vender')
            <a href="{{ route('vender.show', \Crypt::encrypt($vender['id'])) }}" class="btn btn-outline-primary">
                {{ AUth::user()->venderNumberFormat($vender['vender_id']) }}
            </a>
        @else
            <a href="#" class="btn btn-outline-primary"> {{ AUth::user()->venderNumberFormat($vender['vender_id']) }}
            </a>
        @endcan
    </td>
    <td>{{ $vender['name'] }}</td>
    <td>{{ $vender['contact'] }}</td>
    <td>{{ $vender['email'] }}</td>
    <td>{{ \Auth::user()->priceFormat($vender['balance']) }}</td>
    <td class="Action">
        <span>
                @if ($vender['is_active'] == 0)
                    <i class="fa fa-lock" title="Inactive"></i>
                @else
                    @can('show vender')
                        <div class="action-btn bg-info ms-2">
                            <a href="{{ route('vender.show', \Crypt::encrypt($vender['id'])) }}"
                                class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                title="{{ __('View') }}">
                                <i class="ti ti-eye text-white text-white"></i>
                            </a>
                        </div>
                    @endcan
                    @can('edit vender')
                        <div class="action-btn bg-primary ms-2">
                            <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                            data-title="{{__('Edit Vendor')}}"
                                data-url="{{ route('vender.edit', $vender['id']) }}"
                                data-ajax-popup="true" title="{{ __('Edit') }}"
                                data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                <i class="ti ti-pencil text-white"></i>
                            </a>
                        </div>
                    @endcan
                    @can('delete vender')
                        <div class="action-btn bg-danger ms-2">
                            {!! Form::open(['method' => 'DELETE', 'route' => ['vender.destroy', $vender['id']], 'id' => 'delete-form-' . $vender['id']]) !!}
                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"
                                       data-original-title="{{ __('Delete') }}" title="{{ __('Delete') }}"
                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                       data-confirm-yes="document.getElementById('delete-form-{{ $vender['id'] }}').submit();">
                                    <i class="ti ti-trash text-white text-white"></i>
                                </a>
                            {!! Form::close() !!}
                        </div>
                    @endcan
            @endif
        </span>
    </td>
</tr>
