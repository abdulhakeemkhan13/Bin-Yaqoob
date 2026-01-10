@extends('layouts.admin')

@section('page-title')
    {{ $project->name }} - {{ __('Project Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.index') }}">{{ __('Real Estate Projects') }}</a></li>
    <li class="breadcrumb-item">{{ $project->name }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('re-projects.index') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Project Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Project Information') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>{{ __('Code') }}:</strong></td>
                            <td>{{ $project->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Name') }}:</strong></td>
                            <td>{{ $project->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('City') }}:</strong></td>
                            <td>{{ $project->city }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Area') }}:</strong></td>
                            <td>{{ $project->area ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Type') }}:</strong></td>
                            <td><span class="badge bg-primary">{{ $project->type }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Status') }}:</strong></td>
                            <td>
                                @if ($project->status == 'Planning')
                                    <span class="badge bg-warning">{{ $project->status }}</span>
                                @elseif($project->status == 'Active')
                                    <span class="badge bg-success">{{ $project->status }}</span>
                                @else
                                    <span class="badge bg-info">{{ $project->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Total Floors') }}:</strong></td>
                            <td>{{ $project->total_floors }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Total Units') }}:</strong></td>
                            <td>{{ $project->total_units }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Start Date') }}:</strong></td>
                            <td>{{ $project->start_date ? $project->start_date->format('d M, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Expected Completion') }}:</strong></td>
                            <td>{{ $project->expected_completion ? $project->expected_completion->format('d M, Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                    @if ($project->description)
                        <p class="mt-3"><strong>{{ __('Description') }}:</strong><br>{{ $project->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Floors & Units -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Floors & Units') }}</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="floorsAccordionShow">
                        @foreach ($project->floors as $index => $floor)
                            <div class="accordion-item">
                                <h2 class="accordion-header" style="margin-bottom: 10px !important;"
                                    id="floorShow-heading-{{ $floor->id }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#floorShow-collapse-{{ $floor->id }}"
                                        aria-expanded="{{ $index == 0 ? 'true' : 'false' }}">
                                        <i class="ti ti-building me-2"></i>
                                        <strong>{{ $floor->floor_name ?? 'Floor ' . $floor->floor_number }}</strong>
                                        <span class="badge bg-info ms-2">{{ $floor->units->count() }}
                                            {{ __('Units') }}</span>
                                        @php
                                            $available = $floor->units->where('status', 'Available')->count();
                                            $booked = $floor->units->where('status', 'Booked')->count();
                                            $sold = $floor->units->where('status', 'Sold')->count();
                                        @endphp
                                        @if ($available > 0)
                                            <span class="badge bg-success ms-1">{{ $available }}
                                                {{ __('Available') }}</span>
                                        @endif
                                        @if ($booked > 0)
                                            <span class="badge bg-warning ms-1">{{ $booked }}
                                                {{ __('Booked') }}</span>
                                        @endif
                                        @if ($sold > 0)
                                            <span class="badge bg-secondary ms-1">{{ $sold }}
                                                {{ __('Sold') }}</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="floorShow-collapse-{{ $floor->id }}"
                                    class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                    data-bs-parent="#floorsAccordionShow">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>{{ __('Unit No') }}</th>
                                                        <th>{{ __('Type') }}</th>
                                                        <th>{{ __('Area (sq ft)') }}</th>
                                                        <th>{{ __('Price/Sqft') }}</th>
                                                        <th>{{ __('Total Price') }}</th>
                                                        <th>{{ __('Facing') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th width="100">{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($floor->units as $unit)
                                                        <tr id="unit-row-{{ $unit->id }}">
                                                            <td class="unit-number">
                                                                <strong>{{ $unit->unit_number }}</strong>
                                                            </td>
                                                            <td class="unit-type">{{ $unit->unit_type }}</td>
                                                            <td class="unit-area">
                                                                {{ $unit->covered_area ? number_format($unit->covered_area, 2) : '-' }}
                                                            </td>
                                                            <td class="unit-price-sqft">
                                                                {{ $unit->price_per_sqft ? number_format($unit->price_per_sqft, 2) : '-' }}
                                                            </td>
                                                            <td class="unit-price">{{ number_format($unit->price, 2) }}
                                                            </td>
                                                            <td class="unit-facing">{{ $unit->facing ?? '-' }}</td>
                                                            <td class="unit-status">
                                                                @if ($unit->status == 'Available')
                                                                    <span
                                                                        class="badge bg-success">{{ $unit->status }}</span>
                                                                @elseif($unit->status == 'Booked')
                                                                    <span
                                                                        class="badge bg-warning">{{ $unit->status }}</span>
                                                                @elseif($unit->status == 'Sold')
                                                                    <span class="badge bg-info">{{ $unit->status }}</span>
                                                                @else
                                                                    <span
                                                                        class="badge bg-secondary">{{ $unit->status }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($unit->status == 'Available')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-warning btn-edit-unit"
                                                                        data-unit-id="{{ $unit->id }}"
                                                                        title="{{ __('Edit Unit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </button>
                                                                @endif
                                                                <button type="button"
                                                                    class="btn btn-sm btn-info btn-view-unit"
                                                                    data-unit-id="{{ $unit->id }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('View Details') }}">
                                                                    <i class="ti ti-eye"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">
                                                                {{ __('No units defined') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Plans -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Payment Plans') }}</h5>
                </div>
                <div class="card-body">
                    @foreach ($project->paymentPlans as $plan)
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="mb-3">
                                <i class="ti ti-cash"></i> {{ $plan->plan_name }}
                                <span class="badge bg-primary">{{ $plan->duration_months }} {{ __('Months') }}</span>
                                <span class="badge bg-info">{{ $plan->frequency }}</span>
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <strong>{{ __('Down Payment') }}:</strong><br>
                                    @if ($plan->down_payment_percentage)
                                        {{ $plan->down_payment_percentage }}%
                                    @else
                                        -
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <strong>{{ __('Installments') }}:</strong><br>
                                    {{ $plan->num_installments }}
                                </div>
                                <div class="col-md-2">
                                    <strong>{{ __('Possession Charges') }}:</strong><br>
                                    {{ $plan->possession_charges ? number_format($plan->possession_charges, 2) : '-' }}
                                </div>
                                <div class="col-md-2">
                                    <strong>{{ __('Discount') }}:</strong><br>
                                    {{ $plan->discount ? number_format($plan->discount, 2) : '-' }}
                                </div>
                                <div class="col-md-2">
                                    <strong>{{ __('Extra Charges') }}:</strong><br>
                                    {{ $plan->extra_charges ? number_format($plan->extra_charges, 2) : '-' }}
                                </div>
                            </div>

                            <p class="text-muted mb-0">
                                <i class="ti ti-info-circle"></i>
                                {{ __('Installments are automatically generated when a customer books a unit with this plan.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Unit Modal -->

    <div class="modal fade" id="editUnitModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Unit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUnitForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_unit_id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Unit Number') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_unit_number" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Unit Type') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_unit_type" required
                                        onchange="toggleTypeFields()">
                                        <option value="Flat">{{ __('Flat') }}</option>
                                        <option value="Shop">{{ __('Shop') }}</option>
                                        <option value="Office">{{ __('Office') }}</option>
                                        <option value="Penthouse">{{ __('Penthouse') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Covered Area (sq ft)') }}</label>
                                    <input type="number" class="form-control" id="edit_covered_area" step="0.01"
                                        min="0" onchange="editCalculateFromArea()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Price/Sqft') }}</label>
                                    <input type="number" class="form-control" id="edit_price_per_sqft" step="0.01"
                                        min="0" onchange="editCalculateTotalPrice()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Total Price') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_price" step="0.01"
                                        min="0" required onchange="editCalculatePricePerSqft()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Facing') }}</label>
                                    <select class="form-control" id="edit_facing">
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="North">North</option>
                                        <option value="South">South</option>
                                        <option value="East">East</option>
                                        <option value="West">West</option>
                                        <option value="N-E">N-E</option>
                                        <option value="N-W">N-W</option>
                                        <option value="S-E">S-E</option>
                                        <option value="S-W">S-W</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea class="form-control" id="edit_description" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Flat Fields -->
                            <div id="flatFields" class="col-12">
                                <hr>
                                <h6 class="text-primary mb-3">{{ __('Flat Details') }}</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Bedrooms') }}</label>
                                            <input type="number" class="form-control" id="edit_bedrooms"
                                                min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Bathrooms') }}</label>
                                            <input type="number" class="form-control" id="edit_bathrooms"
                                                min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Balconies') }}</label>
                                            <input type="number" class="form-control" id="edit_balconies"
                                                min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-center mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="edit_has_parking">
                                            <label class="form-check-label">{{ __('Has Parking') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Shop/Office Fields -->
                            <div id="shopOfficeFields" class="col-12" style="display: none;">
                                <hr>
                                <h6 class="text-primary mb-3">{{ __('Shop/Office Details') }}</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Floor Position') }}</label>
                                            <select class="form-control" id="edit_floor_position">
                                                <option value="">{{ __('Select') }}</option>
                                                <option value="Front">Front</option>
                                                <option value="Back">Back</option>
                                                <option value="Corner">Corner</option>
                                                <option value="Center">Center</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="edit_has_mezzanine">
                                            <label class="form-check-label">{{ __('Has Mezzanine') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="edit_has_washroom">
                                            <label class="form-check-label">{{ __('Has Washroom') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Penthouse Fields -->
                            <div id="penthouseFields" class="col-12" style="display: none;">
                                <hr>
                                <h6 class="text-primary mb-3">{{ __('Penthouse Details') }}</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Terrace Area (sq ft)') }}</label>
                                            <input type="number" class="form-control" id="edit_terrace_area"
                                                step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="edit_is_duplex">
                                            <label class="form-check-label">{{ __('Is Duplex') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Unit Details Modal -->
    <div class="modal fade" id="viewUnitModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-building me-2"></i>
                        <span id="view_unit_title">{{ __('Unit Details') }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Unit Details Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="ti ti-home me-2"></i>{{ __('Unit Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Unit Number') }}</label>
                                    <p class="mb-0 fw-bold" id="view_unit_number">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Unit Type') }}</label>
                                    <p class="mb-0" id="view_unit_type">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Status') }}</label>
                                    <p class="mb-0" id="view_unit_status">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Covered Area') }}</label>
                                    <p class="mb-0" id="view_covered_area">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Price/Sqft') }}</label>
                                    <p class="mb-0" id="view_price_per_sqft">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Total Price') }}</label>
                                    <p class="mb-0 fw-bold text-success" id="view_price">-</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">{{ __('Facing') }}</label>
                                    <p class="mb-0" id="view_facing">-</p>
                                </div>
                            </div>

                            <!-- Type-specific details -->
                            <div id="view_flat_details" style="display: none;">
                                <hr>
                                <h6 class="text-primary mb-3"><i class="ti ti-door me-2"></i>{{ __('Flat Details') }}
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="text-muted small">{{ __('Bedrooms') }}</label>
                                        <p class="mb-0" id="view_bedrooms">-</p>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="text-muted small">{{ __('Bathrooms') }}</label>
                                        <p class="mb-0" id="view_bathrooms">-</p>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="text-muted small">{{ __('Balconies') }}</label>
                                        <p class="mb-0" id="view_balconies">-</p>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="text-muted small">{{ __('Parking') }}</label>
                                        <p class="mb-0" id="view_has_parking">-</p>
                                    </div>
                                </div>
                            </div>

                            <div id="view_shop_details" style="display: none;">
                                <hr>
                                <h6 class="text-primary mb-3"><i
                                        class="ti ti-building-store me-2"></i>{{ __('Shop/Office Details') }}</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="text-muted small">{{ __('Floor Position') }}</label>
                                        <p class="mb-0" id="view_floor_position">-</p>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="text-muted small">{{ __('Mezzanine') }}</label>
                                        <p class="mb-0" id="view_has_mezzanine">-</p>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="text-muted small">{{ __('Washroom') }}</label>
                                        <p class="mb-0" id="view_has_washroom">-</p>
                                    </div>
                                </div>
                            </div>

                            <div id="view_penthouse_details" style="display: none;">
                                <hr>
                                <h6 class="text-primary mb-3"><i
                                        class="ti ti-star me-2"></i>{{ __('Penthouse Details') }}</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small">{{ __('Terrace Area') }}</label>
                                        <p class="mb-0" id="view_terrace_area">-</p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small">{{ __('Duplex') }}</label>
                                        <p class="mb-0" id="view_is_duplex">-</p>
                                    </div>
                                </div>
                            </div>

                            <div id="view_description_section" style="display: none;">
                                <hr>
                                <label class="text-muted small">{{ __('Description') }}</label>
                                <p class="mb-0" id="view_description">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Client/Customer Details Section -->
                    <div class="card" id="view_client_section" style="display: none;">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="ti ti-user me-2"></i>{{ __('Customer Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Customer Name') }}</label>
                                    <p class="mb-0 fw-bold" id="view_client_name">-</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Contact Number') }}</label>
                                    <p class="mb-0" id="view_client_phone">-</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Email') }}</label>
                                    <p class="mb-0" id="view_client_email">-</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">{{ __('Booking Date') }}</label>
                                    <p class="mb-0" id="view_booking_date">-</p>
                                </div>
                            </div>
                            <div id="view_no_client" class="text-center text-muted py-3">
                                <i class="ti ti-user-off" style="font-size: 2rem;"></i>
                                <p class="mb-0">{{ __('No customer linked to this unit yet.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div id="view_available_notice" class="text-center text-muted py-4">
                        <i class="ti ti-check-circle text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">{{ __('This unit is available for booking') }}</h6>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js-page')
    <script>
        var projectId = {{ $project->id }};
        var baseUrl = '{{ url('re-projects') }}';

        function toggleTypeFields() {
            var type = $('#edit_unit_type').val();
            $('#flatFields, #shopOfficeFields, #penthouseFields').hide();

            if (type === 'Flat') {
                $('#flatFields').show();
            } else if (type === 'Shop' || type === 'Office') {
                $('#shopOfficeFields').show();
            } else if (type === 'Penthouse') {
                $('#flatFields').show();
                $('#penthouseFields').show();
            }
        }

        // Edit modal calculation functions
        function editCalculateTotalPrice() {
            var area = parseFloat($('#edit_covered_area').val()) || 0;
            var pricePerSqft = parseFloat($('#edit_price_per_sqft').val()) || 0;

            if (area > 0 && pricePerSqft > 0) {
                $('#edit_price').val((area * pricePerSqft).toFixed(2));
            }
        }

        function editCalculatePricePerSqft() {
            var area = parseFloat($('#edit_covered_area').val()) || 0;
            var totalPrice = parseFloat($('#edit_price').val()) || 0;

            if (area > 0 && totalPrice > 0) {
                $('#edit_price_per_sqft').val((totalPrice / area).toFixed(2));
            }
        }

        function editCalculateFromArea() {
            var area = parseFloat($('#edit_covered_area').val()) || 0;
            var pricePerSqft = parseFloat($('#edit_price_per_sqft').val()) || 0;
            var totalPrice = parseFloat($('#edit_price').val()) || 0;

            if (area > 0 && pricePerSqft > 0) {
                $('#edit_price').val((area * pricePerSqft).toFixed(2));
            } else if (area > 0 && totalPrice > 0) {
                $('#edit_price_per_sqft').val((totalPrice / area).toFixed(2));
            }
        }

        $(document).on('click', '.btn-edit-unit', function() {
            var unitId = $(this).data('unit-id');

            $.ajax({
                url: baseUrl + '/' + projectId + '/units/' + unitId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var unit = response.unit;
                        $('#edit_unit_id').val(unit.id);
                        $('#edit_unit_number').val(unit.unit_number);
                        $('#edit_unit_type').val(unit.unit_type);
                        $('#edit_covered_area').val(unit.covered_area);
                        $('#edit_price').val(unit.price);
                        $('#edit_facing').val(unit.facing || '');
                        $('#edit_price_per_sqft').val(unit.price_per_sqft || '');
                        $('#edit_description').val(unit.description || '');
                        $('#edit_bedrooms').val(unit.bedrooms || '');
                        $('#edit_bathrooms').val(unit.bathrooms || '');
                        $('#edit_balconies').val(unit.balconies || '');
                        $('#edit_has_parking').prop('checked', unit.has_parking);
                        $('#edit_floor_position').val(unit.floor_position || '');
                        $('#edit_has_mezzanine').prop('checked', unit.has_mezzanine);
                        $('#edit_has_washroom').prop('checked', unit.has_washroom);
                        $('#edit_terrace_area').val(unit.terrace_area || '');
                        $('#edit_is_duplex').prop('checked', unit.is_duplex);

                        toggleTypeFields();
                        $('#editUnitModal').modal('show');
                    }
                },
                error: function() {
                    alert('{{ __('Failed to load unit details.') }}');
                }
            });
        });

        $('#editUnitForm').on('submit', function(e) {
            e.preventDefault();
            var unitId = $('#edit_unit_id').val();
            var type = $('#edit_unit_type').val();

            var data = {
                unit_number: $('#edit_unit_number').val(),
                unit_type: type,
                covered_area: $('#edit_covered_area').val(),
                price_per_sqft: $('#edit_price_per_sqft').val(),
                price: $('#edit_price').val(),
                facing: $('#edit_facing').val(),
                description: $('#edit_description').val(),
                bedrooms: $('#edit_bedrooms').val(),
                bathrooms: $('#edit_bathrooms').val(),
                balconies: $('#edit_balconies').val(),
                has_parking: $('#edit_has_parking').is(':checked') ? 1 : 0,
                floor_position: $('#edit_floor_position').val(),
                has_mezzanine: $('#edit_has_mezzanine').is(':checked') ? 1 : 0,
                has_washroom: $('#edit_has_washroom').is(':checked') ? 1 : 0,
                terrace_area: $('#edit_terrace_area').val(),
                is_duplex: $('#edit_is_duplex').is(':checked') ? 1 : 0,
            };

            $.ajax({
                url: baseUrl + '/' + projectId + '/units/' + unitId,
                type: 'PUT',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        var unit = response.unit;
                        var row = $('#unit-row-' + unitId);

                        // Update the row cells dynamically
                        row.find('.unit-number').html('<strong>' + unit.unit_number + '</strong>');
                        row.find('.unit-type').text(unit.unit_type);
                        row.find('.unit-area').text(unit.covered_area ? parseFloat(unit.covered_area)
                            .toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) : '-');
                        row.find('.unit-price').text(parseFloat(unit.price).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        row.find('.unit-facing').text(unit.facing || '-');

                        // Reset form and close modal
                        $('#editUnitForm')[0].reset();
                        $('#edit_unit_id').val('');
                        $('#editUnitModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    var message = '{{ __('Something went wrong') }}';
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        var errors = [];
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors.push(value[0]);
                        });
                        message = errors.join('\n');
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }
                    // alert(message);
                }
            });
        });

        // View unit details button
        $(document).on('click', '.btn-view-unit', function() {
            var unitId = $(this).data('unit-id');

            $.ajax({
                url: baseUrl + '/' + projectId + '/units/' + unitId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var unit = response.unit;

                        // Set title
                        $('#view_unit_title').text('{{ __('Unit') }} ' + unit.unit_number);

                        // Basic info
                        $('#view_unit_number').text(unit.unit_number);
                        $('#view_unit_type').text(unit.unit_type);

                        // Status with badge
                        var statusBadge = '';
                        if (unit.status === 'Available') {
                            statusBadge = '<span class="badge bg-success">Available</span>';
                        } else if (unit.status === 'Booked') {
                            statusBadge = '<span class="badge bg-warning">Booked</span>';
                        } else if (unit.status === 'Sold') {
                            statusBadge = '<span class="badge bg-info">Sold</span>';
                        } else {
                            statusBadge = '<span class="badge bg-secondary">' + unit.status + '</span>';
                        }
                        $('#view_unit_status').html(statusBadge);

                        $('#view_covered_area').text(unit.covered_area ? parseFloat(unit.covered_area)
                            .toLocaleString() + ' sq ft' : '-');
                        $('#view_price_per_sqft').text(unit.price_per_sqft ? 'PKR ' + parseFloat(unit
                            .price_per_sqft).toLocaleString() : '-');
                        $('#view_price').text('PKR ' + parseFloat(unit.price).toLocaleString());
                        $('#view_facing').text(unit.facing || '-');

                        // Type-specific sections
                        $('#view_flat_details, #view_shop_details, #view_penthouse_details').hide();

                        if (unit.unit_type === 'Flat' || unit.unit_type === 'Penthouse') {
                            $('#view_flat_details').show();
                            $('#view_bedrooms').text(unit.bedrooms || '-');
                            $('#view_bathrooms').text(unit.bathrooms || '-');
                            $('#view_balconies').text(unit.balconies || '-');
                            $('#view_has_parking').html(unit.has_parking ?
                                '<i class="ti ti-check text-success"></i> Yes' :
                                '<i class="ti ti-x text-danger"></i> No');
                        }

                        if (unit.unit_type === 'Shop' || unit.unit_type === 'Office') {
                            $('#view_shop_details').show();
                            $('#view_floor_position').text(unit.floor_position || '-');
                            $('#view_has_mezzanine').html(unit.has_mezzanine ?
                                '<i class="ti ti-check text-success"></i> Yes' :
                                '<i class="ti ti-x text-danger"></i> No');
                            $('#view_has_washroom').html(unit.has_washroom ?
                                '<i class="ti ti-check text-success"></i> Yes' :
                                '<i class="ti ti-x text-danger"></i> No');
                        }

                        if (unit.unit_type === 'Penthouse') {
                            $('#view_penthouse_details').show();
                            $('#view_terrace_area').text(unit.terrace_area ? parseFloat(unit
                                .terrace_area).toLocaleString() + ' sq ft' : '-');
                            $('#view_is_duplex').html(unit.is_duplex ?
                                '<i class="ti ti-check text-success"></i> Yes' :
                                '<i class="ti ti-x text-danger"></i> No');
                        }

                        // Description
                        if (unit.description) {
                            $('#view_description_section').show();
                            $('#view_description').text(unit.description);
                        } else {
                            $('#view_description_section').hide();
                        }

                        // Client section - show based on status
                        if (unit.status === 'Available') {
                            $('#view_available_notice').show();
                            $('#view_client_section').hide();
                        } else {
                            $('#view_available_notice').hide();
                            $('#view_client_section').show();

                            // TODO: Load booking/client data when booking module is ready
                            if (unit.booking) {
                                $('#view_no_client').hide();
                                $('#view_client_name').text(unit.booking.customer_name || '-');
                                $('#view_client_phone').text(unit.booking.customer_phone || '-');
                                $('#view_client_email').text(unit.booking.customer_email || '-');
                                $('#view_booking_date').text(unit.booking.booking_date || '-');
                            } else {
                                $('#view_no_client').show();
                                $('#view_client_name, #view_client_phone, #view_client_email, #view_booking_date')
                                    .closest('.col-md-6').hide();
                            }
                        }

                        $('#viewUnitModal').modal('show');
                    }
                },
                error: function() {
                    alert('{{ __('Failed to load unit details.') }}');
                }
            });
        });
    </script>
@endpush
