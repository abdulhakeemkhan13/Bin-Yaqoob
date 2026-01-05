@foreach ($companyPolicy as $policy)
@php
    $policyPath=\App\Models\Utility::get_file('uploads/companyPolicy');
@endphp
<tr data-row_id="{{$policy->id}}">
    <td>{{ !empty($policy->branches)?$policy->branches->name:'' }}</td>
    <td>{{ $policy->title }}</td>
    <td>{{ $policy->description }}</td>
    <td>
        @if (!empty($policy->attachment))
            <div class="action-btn bg-primary ms-2">

                <a  class="mx-3 btn btn-sm align-items-center" href="{{ $policyPath . '/' . $policy->attachment }}" download="">
                    <i class="ti ti-download text-white"></i>
                </a>
            </div>
            <div class="action-btn bg-secondary ms-2">
                <a class="mx-3 btn btn-sm align-items-center" href="{{ $policyPath . '/' . $policy->attachment }}" target="_blank"  >
                    <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                </a>
            </div>
        @else
            <p>-</p>
        @endif

    </td>
    @if(Gate::check('edit company policy') || Gate::check('delete company policy'))
        <td>
            @can('edit company policy')
            <div class="action-btn bg-primary ms-2">
                <a href="#" data-url="{{ route('company-policy.edit',$policy->id)}}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Company Policy')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
            </div>
                @endcan
            @can('delete company policy')
            <div class="action-btn bg-danger ms-2">
            {!! Form::open(['method' => 'DELETE', 'route' => ['company-policy.destroy', $policy->id],'id'=>'delete-form-'.$policy->id]) !!}

                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$policy->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
            </div>
            @endif
        </td>
    @endif
</tr>
@endforeach