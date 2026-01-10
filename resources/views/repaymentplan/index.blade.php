@extends('layouts.admin')

@section('page-title')
    {{ __('Payment Plans') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Payment Plans') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('re-payment-plans.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Create Plan') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Plan Name') }}</th>
                                    <th>{{ __('Down Payment %') }}</th>
                                    <th>{{ __('Installments') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="150">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plans as $plan)
                                    <tr>
                                        <td>{{ $plan->plan_name }}</td>
                                        <td>{{ $plan->down_payment_percentage ? $plan->down_payment_percentage . '%' : '-' }}
                                        </td>
                                        <td>{{ $plan->num_installments }}</td>
                                        <td>{{ $plan->frequency }}</td>
                                        <td>
                                            @if ($plan->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('re-payment-plans.edit', $plan->id) }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST"
                                                    action="{{ route('re-payment-plans.destroy', $plan->id) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </button>
                                                </form>
                                            </div>
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
