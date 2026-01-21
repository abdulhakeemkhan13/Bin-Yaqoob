@extends('layouts.admin')

@section('page-title')
    {{ __('Unit Details') }} - {{ $unit->unit_number }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.index') }}">{{ __('Real Estate Projects') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.show', $project->id) }}">{{ $project->name }}</a></li>
    <li class="breadcrumb-item">{{ $unit->unit_number }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('re-projects.show', $project->id) }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Project') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Unit Info Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ti ti-building me-2"></i>
                        {{ __('Unit Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Unit Number') }}</label>
                            <p class="mb-0 fw-bold">{{ $unit->unit_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Unit Type') }}</label>
                            <p class="mb-0">{{ $unit->unit_type }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Status') }}</label>
                            <p class="mb-0">
                                @if ($unit->status == 'Available')
                                    <span class="badge bg-success">{{ $unit->status }}</span>
                                @elseif($unit->status == 'Booked')
                                    <span class="badge bg-warning">{{ $unit->status }}</span>
                                @elseif($unit->status == 'Sold')
                                    <span class="badge bg-info">{{ $unit->status }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $unit->status }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Floor') }}</label>
                            <p class="mb-0">{{ $unit->floor->floor_name ?? 'Floor ' . $unit->floor->floor_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Covered Area') }}</label>
                            <p class="mb-0">{{ $unit->covered_area ? number_format($unit->covered_area, 2) . ' sq ft' : '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Price/Sqft') }}</label>
                            <p class="mb-0">{{ $unit->price_per_sqft ? 'PKR ' . number_format($unit->price_per_sqft, 2) : '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Total Price') }}</label>
                            <p class="mb-0 fw-bold text-success">{{ 'PKR ' . number_format($unit->price, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ __('Facing') }}</label>
                            <p class="mb-0">{{ $unit->facing ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Type-specific details -->
                    @if ($unit->unit_type === 'Flat' || $unit->unit_type === 'Penthouse')
                        <hr>
                        <h6 class="text-primary mb-3"><i class="ti ti-door me-2"></i>{{ __('Flat Details') }}</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="text-muted small">{{ __('Bedrooms') }}</label>
                                <p class="mb-0">{{ $unit->bedrooms ?? '-' }}</p>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="text-muted small">{{ __('Bathrooms') }}</label>
                                <p class="mb-0">{{ $unit->bathrooms ?? '-' }}</p>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="text-muted small">{{ __('Balconies') }}</label>
                                <p class="mb-0">{{ $unit->balconies ?? '-' }}</p>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="text-muted small">{{ __('Parking') }}</label>
                                <p class="mb-0">
                                    @if ($unit->has_parking)
                                        <i class="ti ti-check text-success"></i> {{ __('Yes') }}
                                    @else
                                        <i class="ti ti-x text-danger"></i> {{ __('No') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($unit->unit_type === 'Shop' || $unit->unit_type === 'Office')
                        <hr>
                        <h6 class="text-primary mb-3"><i class="ti ti-building-store me-2"></i>{{ __('Shop/Office Details') }}</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">{{ __('Floor Position') }}</label>
                                <p class="mb-0">{{ $unit->floor_position ?? '-' }}</p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">{{ __('Mezzanine') }}</label>
                                <p class="mb-0">
                                    @if ($unit->has_mezzanine)
                                        <i class="ti ti-check text-success"></i> {{ __('Yes') }}
                                    @else
                                        <i class="ti ti-x text-danger"></i> {{ __('No') }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">{{ __('Washroom') }}</label>
                                <p class="mb-0">
                                    @if ($unit->has_washroom)
                                        <i class="ti ti-check text-success"></i> {{ __('Yes') }}
                                    @else
                                        <i class="ti ti-x text-danger"></i> {{ __('No') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($unit->unit_type === 'Penthouse')
                        <hr>
                        <h6 class="text-primary mb-3"><i class="ti ti-star me-2"></i>{{ __('Penthouse Details') }}</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="text-muted small">{{ __('Terrace Area') }}</label>
                                <p class="mb-0">{{ $unit->terrace_area ? number_format($unit->terrace_area, 2) . ' sq ft' : '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="text-muted small">{{ __('Duplex') }}</label>
                                <p class="mb-0">
                                    @if ($unit->is_duplex)
                                        <i class="ti ti-check text-success"></i> {{ __('Yes') }}
                                    @else
                                        <i class="ti ti-x text-danger"></i> {{ __('No') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($unit->description)
                        <hr>
                        <label class="text-muted small">{{ __('Description') }}</label>
                        <p class="mb-0">{{ $unit->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Conditional Sections -->
        <div class="col-md-6">
            <!-- Client Details for Booked or Sold -->
            @if ($unit->status === 'Booked' || $unit->status === 'Sold')
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="ti ti-user me-2"></i>{{ __('Customer Information') }}</h6>
                    </div>
                    <div class="card-body">
                        @if ($unit->status === 'Booked' && $unit->booking)
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Customer Name') }}</label>
                                    <p class="mb-0 fw-bold">{{ $unit->booking->customer_name ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Contact Number') }}</label>
                                    <p class="mb-0">{{ $unit->booking->customer_phone ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Email') }}</label>
                                    <p class="mb-0">{{ $unit->booking->customer_email ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Booking Date') }}</label>
                                    <p class="mb-0">{{ $unit->booking->booking_date ? \Auth::user()->dateFormat($unit->booking->booking_date) : '-' }}</p>
                                </div>
                            </div>
                        @elseif ($unit->status === 'Sold' && $contract)
                            @if ($contract->customer)
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Full Name') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->full_name ?? $contract->customer->name }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Customer Type') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->client_type ?? '-' }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('CNIC / NTN') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->cnic_number ?? '-' }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Nationality') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->nationality ?? '-' }}</h6>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Mobile Primary') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->mobile_primary ?? $contract->customer->contact }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Mobile Secondary') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->mobile_secondary ?? '-' }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Email') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->email ?? '-' }}</h6>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Father / Company') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->father_or_spouse_name ?? '-' }}</h6>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Current Address') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->current_address ?? '-' }}</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Permanent Address') }}</p>
                                        <h6 class="mb-0">{{ $contract->customer->permanent_address ?? '-' }}</h6>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-muted text-sm mb-0">{{ __('Agreement Date') }}</p>
                                        <h6 class="mb-0">{{ $contract->agreement_date ? \Auth::user()->dateFormat($contract->agreement_date) : '-' }}</h6>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">{{ __('No customer information available.') }}</p>
                            @endif
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="ti ti-user-off" style="font-size: 2rem;"></i>
                                <p class="mb-0">{{ __('No customer linked to this unit yet.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Installment Schedule for Sold -->
            @if ($unit->status === 'Sold')
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="ti ti-cash me-2"></i>{{ __('Installment Schedule') }}</h6>
                    </div>
                    <div class="card-body">
                        @if ($installments && $installments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('Installment') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Issue Date') }}</th>
                                            <th>{{ __('Due Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($installments as $installment)
                                            <tr>
                                                <td>{{ $installment->installment_number }}</td>
                                                <td>
                                                    @if ($installment->installment_type == 'down_payment')
                                                        <span class="badge bg-info">{{ __('Down Payment') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Installment') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-success fw-bold">
                                                    {{ \Auth::user()->priceFormat($installment->amount) }}
                                                </td>
                                                <td>{{ \Auth::user()->dateFormat($installment->issue_date) }}</td>
                                                <td>{{ \Auth::user()->dateFormat($installment->due_date) }}</td>
                                                <td>
                                                    @if ($installment->status == 'paid')
                                                        <span class="badge bg-success">{{ __('Paid') }}</span>
                                                    @elseif($installment->status == 'generated')
                                                        <span class="badge bg-info">{{ __('Invoice Generated') }}</span>
                                                    @elseif($installment->status == 'overdue')
                                                        <span class="badge bg-danger">{{ __('Overdue') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $installment->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if ($installment->invoice_id)
                                                        <a href="{{ route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($installment->invoice_id)) }}"
                                                            class="btn btn-sm btn-info"
                                                            target="_blank">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    @elseif($installment->status != 'paid')
                                                        <a href="{{ route('invoice.create', ['cid' => $contract->customer_id ?? 0, 'contract_id' => $contract->id, 'installment_id' => $installment->id]) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="ti ti-plus"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-primary">
                                            <td colspan="2" class="text-end fw-bold">{{ __('Total') }}</td>
                                            <td class="text-success fw-bold">
                                                {{ \Auth::user()->priceFormat($installments->sum('amount')) }}
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="ti ti-cash-off" style="font-size: 2rem;"></i>
                                <p class="mb-0">{{ __('No installment schedule available.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Available Notice -->
            @if ($unit->status === 'Available')
                <div class="card">
                    <div class="card-body text-center">
                        <i class="ti ti-check-circle text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">{{ __('This unit is available for booking') }}</h6>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection