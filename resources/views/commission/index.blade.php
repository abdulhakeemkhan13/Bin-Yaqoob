@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Commissions') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Commission') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create commission')
            <a href="#" data-url="{{ route('commission.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create New Commission') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Commission For') }}</th>
                                    <th>{{ __('Recipient') }}</th>
                                    <th>{{ __('Contract / Deal') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Released') }}</th>
                                    <th>{{ __('Condition') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="150px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commissions as $commission)
                                    <tr>
                                        <td>{{ $commission->title }}</td>
                                        <td>{{ App\Models\Commission::$commission_for[$commission->commission_for] ?? $commission->commission_for }}
                                        </td>
                                        <td>
                                            @if ($commission->commission_for == 'employee')
                                                {{ $commission->employee ? $commission->employee->name : '-' }}
                                            @else
                                                {{ $commission->dealer ? $commission->dealer->name : '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($commission->contract)
                                                <span class="badge bg-primary">{{ __('Contract') }}:
                                                    {{ $commission->contract->contract_no }}</span>
                                            @elseif($commission->deal)
                                                <span class="badge bg-info">{{ __('Deal') }}:
                                                    {{ $commission->deal->name }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ Auth::user()->priceFormat($commission->total_commission_amount) }}</td>
                                        <td>{{ Auth::user()->priceFormat($commission->amount_released) }}</td>
                                        <td>
                                            {{ App\Models\Commission::$release_condition_types[$commission->release_condition_type] ?? $commission->release_condition_type }}
                                            @if ($commission->release_condition_type == 'percentage_received')
                                                ({{ $commission->release_percentage }}%)
                                            @endif
                                        </td>
                                        <td>
                                            @if ($commission->status == 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif($commission->status == 'partially_released')
                                                <span class="badge bg-info">{{ __('Partially Released') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Fully Released') }}</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                @can('edit commission')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                            data-url="{{ route('commission.edit', $commission->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Edit Commission') }}"
                                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                            data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete commission')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['commission.destroy', $commission->id],
                                                            'id' => 'delete-form-' . $commission->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $commission->id }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
