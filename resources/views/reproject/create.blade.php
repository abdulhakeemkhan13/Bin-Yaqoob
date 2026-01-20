@extends('layouts.admin')

@section('page-title')
    {{ __('Create Real Estate Project') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.index') }}">{{ __('Real Estate Projects') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@push('css-page')
    <style>
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .wizard-step {
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .wizard-step .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .wizard-step.active .step-number {
            background: var(--bs-primary);
            color: white;
        }

        .wizard-step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .wizard-step .step-title {
            font-size: 12px;
            color: #666;
        }

        .wizard-step.active .step-title {
            color: var(--bs-primary);
            font-weight: bold;
        }

        .tab-content-wrapper {
            min-height: 400px;
        }

        .floor-row,
        .unit-row,
        .plan-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .installment-preview {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Project Setup Wizard') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Wizard Steps -->
                    <div class="wizard-steps">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-title">{{ __('Basic Info') }}</div>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-title">{{ __('Towers/Blocks') }}</div>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-title">{{ __('Floors') }}</div>
                        </div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-title">{{ __('Units') }}</div>
                        </div>
                        <div class="wizard-step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-title">{{ __('Payment Plans') }}</div>
                        </div>
                        <div class="wizard-step" data-step="6">
                            <div class="step-number">6</div>
                            <div class="step-title">{{ __('Review') }}</div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content-wrapper">
                        <!-- Tab 1: Basic Info -->
                        <div class="wizard-tab" id="tab-1">
                            @include('reproject.tabs.tab1-basic-info')
                        </div>

                        <!-- Tab 2: Towers/Blocks -->
                        <div class="wizard-tab d-none" id="tab-2">
                            @include('reproject.tabs.tab2-towers')
                        </div>

                        <!-- Tab 3: Floors -->
                        <div class="wizard-tab d-none" id="tab-3">
                            @include('reproject.tabs.tab3-floors')
                        </div>

                        <!-- Tab 4: Units -->
                        <div class="wizard-tab d-none" id="tab-4">
                            @include('reproject.tabs.tab4-units')
                        </div>

                        <!-- Tab 5: Payment Plans -->
                        <div class="wizard-tab d-none" id="tab-5">
                            @include('reproject.tabs.tab5-payment-plans')
                        </div>

                        <!-- Tab 6: Review -->
                        <div class="wizard-tab d-none" id="tab-6">
                            @include('reproject.tabs.tab6-review')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        var projectId = {{ request()->get('project_id', 0) }};
        var currentTab = 1;
        var maxTab = projectId > 0 ? 2 : 1;

        $(document).ready(function() {
            // If continuing an existing project, fetch its data
            if (projectId > 0) {
                loadProjectData();
            }
        });

        function loadProjectData() {
            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/data',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Populate forms with existing data
                        populateBasicInfo(response.project);
                        if (response.project.floors && response.project.floors.length > 0) {
                            maxTab = 3;
                            populateFloors(response.project.floors);
                        }
                    }
                }
            });
        }

        function populateBasicInfo(project) {
            $('#project_name').val(project.name);
            $('#city').val(project.city);
            $('#area').val(project.area);
            $('#address').val(project.address);
            $('#total_floors').val(project.total_floors);
            $('#total_units').val(project.total_units);
            $('#project_type').val(project.type);
            $('#start_date').val(project.start_date);
            $('#expected_completion').val(project.expected_completion);
            $('#description').val(project.description);
        }

        function populateFloors(floors) {
            $('#floors-container').empty();
            floors.forEach(function(floor, index) {
                addFloorRow(floor);
            });
        }

        function goToTab(tabNumber) {
            if (tabNumber > maxTab) {
                toastr.error('{{ __('Please complete the current step first.') }}');
                return;
            }

            // Hide all tabs
            $('.wizard-tab').addClass('d-none');
            // Show selected tab
            $('#tab-' + tabNumber).removeClass('d-none');

            // Update step indicators
            $('.wizard-step').removeClass('active');
            $('.wizard-step[data-step="' + tabNumber + '"]').addClass('active');

            // Mark previous steps as completed
            for (var i = 1; i < tabNumber; i++) {
                $('.wizard-step[data-step="' + i + '"]').addClass('completed');
            }

            currentTab = tabNumber;

            // Load data for specific tabs
            if (tabNumber == 2) {
                // Auto-generate tower rows based on total_towers
                generateTowerRows();
            } else if (tabNumber == 3) {
                // Auto-generate floor rows based on tower data
                generateFloorRows();
            } else if (tabNumber == 4 && projectId > 0) {
                loadFloorsForUnits();
            } else if (tabNumber == 5) {
                loadAvailablePlans();
            } else if (tabNumber == 6 && projectId > 0) {
                loadReviewData();
            }
        }

        function generateTowerRows() {
            var totalTowers = parseInt($('#total_towers').val()) || 1;
            var container = $('#towers-container');

            // Only generate if empty
            if (container.find('.tower-row').length > 0) {
                return;
            }

            container.empty();
            towerIndex = 0;

            for (var i = 1; i <= totalTowers; i++) {
                addTowerRow();
            }
        }

        // Store tower data from tab 2 submission
        var savedTowers = [];

        function generateFloorRows() {
            // Get tower data from the submitted towers or from saved towers
            if (savedTowers.length === 0) {
                // Collect from form if not saved yet
                $('.tower-row').each(function() {
                    savedTowers.push({
                        tower_code: $(this).find('.tower-code').val(),
                        tower_name: $(this).find('.tower-name').val(),
                        floors_count: parseInt($(this).find('.tower-floors').val()) || 1
                    });
                });
            }

            if (savedTowers.length === 0) {
                // No towers, show simple floor list
                $('#tower-tabs').html(
                    '<li class="nav-item"><a class="nav-link active" href="#">{{ __('No Towers Defined') }}</a></li>');
                return;
            }

            // Generate tower tabs
            var tabsHtml = '';
            var contentHtml = '';

            savedTowers.forEach(function(tower, index) {
                var isActive = index === 0 ? 'active' : '';
                var towerLabel = tower.tower_name || ('{{ __('Tower') }} ' + tower.tower_code);

                // Tab header
                tabsHtml += `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${isActive}" id="tower-tab-${index}" data-bs-toggle="tab" 
                            data-bs-target="#tower-content-${index}" type="button" role="tab">
                            <strong>${tower.tower_code}</strong> - ${towerLabel}
                            <span class="badge bg-primary ms-1">${tower.floors_count} {{ __('floors') }}</span>
                        </button>
                    </li>
                `;

                // Tab content with floor table
                var floorsHtml = '';
                for (var f = 1; f <= tower.floors_count; f++) {
                    var floorName = getOrdinalFloorName(f);
                    floorsHtml += `
                        <tr class="floor-row" data-tower-code="${tower.tower_code}">
                            <td>
                                <input type="hidden" class="tower-code-input" value="${tower.tower_code}">
                                <input type="text" class="form-control form-control-sm floor-number" value="${f}" readonly style="background-color: #f8f9fa;">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm floor-name" value="${floorName}" required>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm floor-units" value="1" min="1" required>
                            </td>
                        </tr>
                    `;
                }

                contentHtml += `
                    <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="tower-content-${index}" role="tabpanel">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 80px;">{{ __('Floor #') }}</th>
                                        <th>{{ __('Floor Name') }}</th>
                                        <th style="width: 150px;">{{ __('No. of Units') }} <span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody class="floors-container-tower" data-tower="${tower.tower_code}">
                                    ${floorsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            $('#tower-tabs').html(tabsHtml);
            $('#tower-tabs-content').html(contentHtml);
        }

        function getOrdinalFloorName(num) {
            if (num === 0) return 'Ground Floor';
            var suffix = 'th';
            if (num % 100 >= 11 && num % 100 <= 13) {
                suffix = 'th';
            } else {
                switch (num % 10) {
                    case 1:
                        suffix = 'st';
                        break;
                    case 2:
                        suffix = 'nd';
                        break;
                    case 3:
                        suffix = 'rd';
                        break;
                }
            }
            return num + suffix + ' Floor';
        }

        function loadFloorsForUnits() {
            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/floors',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderFloorsAccordion(response.floors);
                    }
                },
                error: function() {
                    $('#floorsAccordion').html(
                        '<div class="alert alert-warning">{{ __('Failed to load floors.') }}</div>');
                }
            });
        }

        function renderFloorsAccordion(floors) {
            if (floors.length === 0) {
                $('#floorsAccordion').html(
                    '<div class="alert alert-warning">{{ __('No floors found. Please add floors first.') }}</div>');
                return;
            }

            var html = '';
            floors.forEach(function(floor, index) {
                var isFirst = index === 0;
                var collapseId = 'floor-collapse-' + floor.id;
                var headerId = 'floor-header-' + floor.id;

                html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="${headerId}">
                            <button class="accordion-button ${isFirst ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${isFirst ? 'true' : 'false'}">
                                <strong>${floor.floor_name || 'Floor ' + floor.floor_number}</strong>
                                <span class="badge bg-info ms-2">${floor.total_units} {{ __('units') }}</span>
                            </button>
                        </h2>
                        <div id="${collapseId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}" data-bs-parent="#floorsAccordion">
                            <div class="accordion-body">
                                <div class="row mb-5 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Apply Same Price/Sqft') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control floor-price-sqft-input" id="floor-price-sqft-${floor.id}" placeholder="{{ __('Price/Sqft') }}" step="0.01" min="0">
                                            <button type="button" class="btn btn-success" onclick="applyFloorPriceSqft(${floor.id})" title="{{ __('Apply Price/Sqft to all units on this floor') }}">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Apply Same Type') }}</label>
                                        <div class="input-group">
                                            <select class="form-control floor-type-input" id="floor-type-${floor.id}">
                                                <option value="Flat">{{ __('Flat') }}</option>
                                                <option value="Shop">{{ __('Shop') }}</option>
                                                <option value="Office">{{ __('Office') }}</option>
                                                <option value="Penthouse">{{ __('Penthouse') }}</option>
                                            </select>
                                            <button type="button" class="btn btn-info" onclick="applyFloorType(${floor.id})">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Apply Same Area') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control floor-area-input" id="floor-area-${floor.id}" placeholder="{{ __('sq ft') }}" step="0.01" min="0">
                                            <button type="button" class="btn btn-secondary" onclick="applyFloorArea(${floor.id})">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width:120px;">{{ __('Unit #') }}</th>
                                                <th style="width:120px;">{{ __('Type') }}</th>
                                                <th style="width:130px;">{{ __('Area (sq ft)') }}</th>
                                                <th style="width:130px;">{{ __('Price/Sqft') }}</th>
                                                <th style="width:150px;">{{ __('Total Price') }}</th>
                                                <th style="width:120px;">{{ __('Facing') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="units-floor-${floor.id}">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#floorsAccordion').html(html);

            // Generate unit rows for each floor
            floors.forEach(function(floor) {
                generateUnitsForFloor(floor);
            });
        }

        function applyFloorPriceSqft(floorId) {
            var pricePerSqft = $('#floor-price-sqft-' + floorId).val();
            if (pricePerSqft) {
                $('#units-floor-' + floorId + ' .unit-row').each(function() {
                    var row = $(this);
                    var area = parseFloat(row.find('.unit-area').val()) || 0;
                    row.find('.unit-price-sqft').val(pricePerSqft);
                    if (area > 0) {
                        var totalPrice = area * parseFloat(pricePerSqft);
                        row.find('.unit-price').val(totalPrice.toFixed(2));
                    }
                });
                // alert('{{ __('Price/Sqft applied to all units on this floor.') }}');
            }
        }

        function applyFloorType(floorId) {
            var type = $('#floor-type-' + floorId).val();
            $('#units-floor-' + floorId + ' .unit-type').val(type);
            // alert('{{ __('Type applied to all units on this floor.') }}');
        }

        function applyFloorArea(floorId) {
            var area = $('#floor-area-' + floorId).val();
            if (area) {
                $('#units-floor-' + floorId + ' .unit-row').each(function() {
                    var row = $(this);
                    row.find('.unit-area').val(area);
                    // Recalculate total price if price/sqft exists
                    var pricePerSqft = parseFloat(row.find('.unit-price-sqft').val()) || 0;
                    if (pricePerSqft > 0) {
                        var totalPrice = parseFloat(area) * pricePerSqft;
                        row.find('.unit-price').val(totalPrice.toFixed(2));
                    }
                });
                // alert('{{ __('Area applied to all units on this floor.') }}');
            }
        }

        function generateUnitsForFloor(floor) {
            var container = $('#units-floor-' + floor.id);
            container.empty();

            for (var i = 1; i <= floor.total_units; i++) {
                var unitNo = floor.floor_number + String(i).padStart(2, '0'); // e.g., 101, 102, 201, 202
                var html = `
                    <tr class="unit-row" data-floor-id="${floor.id}">
                        <td>
                            <input type="text" class="form-control form-control-sm unit-number" value="${unitNo}" required>
                        </td>
                        <td>
                            <select class="form-control form-control-sm unit-type" required>
                                <option value="Flat">{{ __('Flat') }}</option>
                                <option value="Shop">{{ __('Shop') }}</option>
                                <option value="Office">{{ __('Office') }}</option>
                                <option value="Penthouse">{{ __('Penthouse') }}</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm unit-area" value="1" step="0.01" min="0" onchange="calculateFromArea(this)">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm unit-price-sqft" value="1" step="0.01" min="0" onchange="calculateTotalPrice(this)">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm unit-price" value="1" step="0.01" min="0" readonly style="background-color: #f8f9fa;">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm unit-facing" placeholder="N/S/E/W">
                        </td>
                    </tr>
                `;
                container.append(html);
            }
        }

        // Calculate Total Price when Area or Price/Sqft changes
        function calculateTotalPrice(input) {
            var row = $(input).closest('tr');
            var area = parseFloat(row.find('.unit-area').val()) || 0;
            var pricePerSqft = parseFloat(row.find('.unit-price-sqft').val()) || 0;

            if (area > 0 && pricePerSqft > 0) {
                var totalPrice = area * pricePerSqft;
                row.find('.unit-price').val(totalPrice.toFixed(2));
            }
        }

        // Calculate Price/Sqft when Total Price changes
        function calculatePricePerSqft(input) {
            var row = $(input).closest('tr');
            var area = parseFloat(row.find('.unit-area').val()) || 0;
            var totalPrice = parseFloat(row.find('.unit-price').val()) || 0;

            if (area > 0 && totalPrice > 0) {
                var pricePerSqft = totalPrice / area;
                row.find('.unit-price-sqft').val(pricePerSqft.toFixed(2));
            }
        }

        // Recalculate when Area changes
        function calculateFromArea(input) {
            var row = $(input).closest('tr');
            var area = parseFloat(row.find('.unit-area').val()) || 0;
            var pricePerSqft = parseFloat(row.find('.unit-price-sqft').val()) || 0;
            var totalPrice = parseFloat(row.find('.unit-price').val()) || 0;

            // If price/sqft is set, recalculate total price
            if (area > 0 && pricePerSqft > 0) {
                row.find('.unit-price').val((area * pricePerSqft).toFixed(2));
            }
            // Otherwise if total price is set, recalculate price/sqft
            else if (area > 0 && totalPrice > 0) {
                row.find('.unit-price-sqft').val((totalPrice / area).toFixed(2));
            }
        }

        function loadReviewData() {
            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/data',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderReviewData(response.project);
                    }
                }
            });
        }

        // Tab 1: Save Basic Info
        $('#form-basic-info').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            console.log('Submitting Tab 1 form...');

            $.ajax({
                url: '{{ route('re-projects.basic-info') }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Tab 1 response:', response);
                    if (response.success) {
                        projectId = response.project_id;
                        maxTab = response.next_tab;
                        toastr.success(response.message);
                        console.log('Moving to tab:', response.next_tab);
                        goToTab(response.next_tab);
                    }
                },
                error: function(xhr) {
                    console.log('Tab 1 error:', xhr);
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || '{{ __('Something went wrong') }}');
                    }
                }
            });
        });

        // Tab 3: Save Floors
        $('#form-floors').on('submit', function(e) {
            e.preventDefault();
            var floors = [];
            $('.floor-row').each(function() {
                floors.push({
                    tower_code: $(this).find('.tower-code-input').val() || '',
                    floor_number: $(this).find('.floor-number').val(),
                    floor_name: $(this).find('.floor-name').val(),
                    total_units: $(this).find('.floor-units').val()
                });
            });

            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/floors',
                type: 'POST',
                data: {
                    floors: floors
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        maxTab = response.next_tab;
                        toastr.success(response.message);
                        goToTab(response.next_tab);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                    }
                }
            });
        });

        // Tab 3: Save Units
        $('#form-units').on('submit', function(e) {
            e.preventDefault();

            // Validate all prices are filled
            var missingPrices = [];
            $('.unit-row').each(function() {
                var price = $(this).find('.unit-price').val();
                var unitNo = $(this).find('.unit-number').val();
                if (!price || price <= 0) {
                    missingPrices.push(unitNo);
                    $(this).find('.unit-price').addClass('is-invalid');
                } else {
                    $(this).find('.unit-price').removeClass('is-invalid');
                }
            });

            if (missingPrices.length > 0) {
                toastr.error('{{ __('Please enter price for all units. Missing: ') }}' + missingPrices.slice(0, 5)
                    .join(', ') + (missingPrices.length > 5 ? '...' : ''));
                return;
            }

            var units = [];
            $('.unit-row').each(function() {
                units.push({
                    floor_id: $(this).attr('data-floor-id'),
                    unit_number: $(this).find('.unit-number').val(),
                    unit_type: $(this).find('.unit-type').val(),
                    covered_area: $(this).find('.unit-area').val(),
                    price_per_sqft: $(this).find('.unit-price-sqft').val(),
                    price: $(this).find('.unit-price').val(),
                    facing: $(this).find('.unit-facing').val()
                });
            });

            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/units',
                type: 'POST',
                data: {
                    units: units
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        maxTab = response.next_tab;
                        toastr.success(response.message);
                        goToTab(response.next_tab);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                    }
                }
            });
        });

        // Tab 4: Assign Payment Plans
        $('#form-payment-plans').on('submit', function(e) {
            e.preventDefault();
            var selectedPlanIds = [];
            $('input[name="payment_plan_ids[]"]:checked').each(function() {
                selectedPlanIds.push($(this).val());
            });

            if (selectedPlanIds.length === 0) {
                toastr.error('{{ __('Please select at least one payment plan.') }}');
                return;
            }

            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/assign-plans',
                type: 'POST',
                data: {
                    payment_plan_ids: selectedPlanIds
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        maxTab = response.next_tab;
                        toastr.success(response.message);
                        goToTab(response.next_tab);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                    }
                }
            });
        });

        // Load available payment plans for Tab 4
        function loadAvailablePlans() {
            $.ajax({
                url: '{{ route('re-payment-plans.active') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderAvailablePlans(response.plans);
                    }
                },
                error: function() {
                    $('#available-plans-container').html(
                        '<div class="alert alert-warning">{{ __('Failed to load payment plans.') }}</div>');
                }
            });
        }

        function renderAvailablePlans(plans) {
            if (plans.length === 0) {
                $('#available-plans-container').html(`
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-circle"></i>
                        {{ __('No payment plans available. Please create plans first from') }}
                        <a href="{{ route('re-payment-plans.create') }}" target="_blank">{{ __('Payment Plans') }}</a>.
                    </div>
                `);
                return;
            }

            var html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
            html += '<thead class="bg-light"><tr>';
            html += '<th style="width:50px;"></th>';
            html += '<th>{{ __('Plan Name') }}</th>';
            html += '<th>{{ __('Duration') }}</th>';
            html += '<th>{{ __('Installments') }}</th>';
            html += '<th>{{ __('Frequency') }}</th>';
            html += '<th>{{ __('Down Payment') }}</th>';
            html += '</tr></thead><tbody>';

            plans.forEach(function(plan) {
                html += '<tr>';
                html += '<td><input type="checkbox" class="form-check-input" name="payment_plan_ids[]" value="' +
                    plan.id + '"></td>';
                html += '<td><strong>' + plan.plan_name + '</strong></td>';
                html += '<td>' + plan.duration_months + ' {{ __('months') }}</td>';
                html += '<td>' + plan.num_installments + '</td>';
                html += '<td>' + plan.frequency + '</td>';
                html += '<td>';
                if (plan.down_payment_percentage) {
                    html += plan.down_payment_percentage + '%';
                } else if (plan.down_payment_amount) {
                    html += parseFloat(plan.down_payment_amount).toLocaleString();
                } else {
                    html += '-';
                }
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            $('#available-plans-container').html(html);
        }

        // Tab 5: Final Submit
        $('#btn-final-submit').on('click', function() {
            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/submit',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || '{{ __('Something went wrong') }}');
                }
            });
        });

        // Dynamic floor addition
        function addFloorRow(data = null) {
            var index = $('.floor-row').length + 1;
            var html = `
                <div class="floor-row">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Floor Number') }} *</label>
                            <input type="text" class="form-control floor-number" value="${data ? data.floor_number : index}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Floor Name') }}</label>
                            <input type="text" class="form-control floor-name" value="${data ? (data.floor_name || '') : ''}" placeholder="e.g. Ground Floor">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Total Units') }} *</label>
                            <input type="number" class="form-control floor-units" value="${data ? data.total_units : 1}" min="0" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeFloorRow(this)">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#floors-container').append(html);
        }

        function removeFloorRow(btn) {
            if ($('.floor-row').length > 1) {
                $(btn).closest('.floor-row').remove();
            } else {
                toastr.error('{{ __('At least one floor is required.') }}');
            }
        }

        // Dynamic unit addition
        function addUnitRow(floorId = '', floorName = '') {
            var html = `
                <div class="unit-row">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Floor') }} *</label>
                            <select class="form-control unit-floor" required>
                                <option value="">{{ __('Select') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Unit No') }} *</label>
                            <input type="text" class="form-control unit-number" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Type') }} *</label>
                            <select class="form-control unit-type" required>
                                <option value="Flat">{{ __('Flat') }}</option>
                                <option value="Shop">{{ __('Shop') }}</option>
                                <option value="Office">{{ __('Office') }}</option>
                                <option value="Penthouse">{{ __('Penthouse') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Area (sq ft)') }}</label>
                            <input type="number" class="form-control unit-area" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Price') }} *</label>
                            <input type="number" class="form-control unit-price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">{{ __('Facing') }}</label>
                            <input type="text" class="form-control unit-facing">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeUnitRow(this)">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#units-container').append(html);
            // Populate floor dropdown
            loadFloorsForUnitDropdown();
        }

        function removeUnitRow(btn) {
            $(btn).closest('.unit-row').remove();
        }

        function loadFloorsForUnitDropdown() {
            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/floors',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">{{ __('Select') }}</option>';
                        response.floors.forEach(function(floor) {
                            options += '<option value="' + floor.id + '">' + floor.floor_number + (floor
                                .floor_name ? ' - ' + floor.floor_name : '') + '</option>';
                        });
                        $('.unit-floor').html(options);
                    }
                }
            });
        }

        function renderFloorsForUnits(floors) {
            var options = '<option value="">{{ __('Select') }}</option>';
            floors.forEach(function(floor) {
                options += '<option value="' + floor.id + '">' + floor.floor_number + (floor.floor_name ? ' - ' +
                    floor.floor_name : '') + '</option>';
            });
            $('.unit-floor').html(options);
        }

        // Dynamic payment plan addition
        function addPlanRow() {
            var html = `
                <div class="plan-row">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Plan Name') }} *</label>
                            <input type="text" class="form-control plan-name" placeholder="e.g. 3 Year Plan" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Duration (months)') }} *</label>
                            <input type="number" class="form-control plan-duration" value="36" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Down Payment %') }}</label>
                            <input type="number" class="form-control plan-down-percent" step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Down Payment Amt') }}</label>
                            <input type="number" class="form-control plan-down-amount" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Installments') }} *</label>
                            <input type="number" class="form-control plan-installments" value="36" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Frequency') }} *</label>
                            <select class="form-control plan-frequency" required>
                                <option value="Monthly">{{ __('Monthly') }}</option>
                                <option value="Quarterly">{{ __('Quarterly') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Base Amount') }}</label>
                            <input type="number" class="form-control plan-base-amount" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Possession Charges') }}</label>
                            <input type="number" class="form-control plan-possession" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Discount') }}</label>
                            <input type="number" class="form-control plan-discount" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Extra Charges') }}</label>
                            <input type="number" class="form-control plan-extra" step="0.01" min="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removePlanRow(this)">
                                <i class="ti ti-trash"></i> {{ __('Remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#plans-container').append(html);
        }

        function removePlanRow(btn) {
            if ($('.plan-row').length > 1) {
                $(btn).closest('.plan-row').remove();
            } else {
                toastr.error('{{ __('At least one payment plan is required.') }}');
            }
        }

        function renderReviewData(project) {
            var html = '<div class="row">';

            // Project Info
            html += '<div class="col-md-6"><h6>{{ __('Project Details') }}</h6>';
            html += '<table class="table table-sm">';
            html += '<tr><td><strong>{{ __('Name') }}:</strong></td><td>' + project.name + '</td></tr>';
            html += '<tr><td><strong>{{ __('Code') }}:</strong></td><td>' + project.code + '</td></tr>';
            html += '<tr><td><strong>{{ __('City') }}:</strong></td><td>' + project.city + '</td></tr>';
            html += '<tr><td><strong>{{ __('Type') }}:</strong></td><td>' + project.type + '</td></tr>';
            html += '<tr><td><strong>{{ __('Total Towers') }}:</strong></td><td>' + (project.total_towers || project
                .towers?.length || 0) + '</td></tr>';
            html += '<tr><td><strong>{{ __('Total Floors') }}:</strong></td><td>' + project.total_floors + '</td></tr>';
            html += '<tr><td><strong>{{ __('Total Units') }}:</strong></td><td>' + project.total_units + '</td></tr>';
            html += '</table></div>';

            // Approval Info (if available)
            html += '<div class="col-md-6"><h6>{{ __('Approval Info') }}</h6>';
            html += '<table class="table table-sm">';
            html += '<tr><td><strong>{{ __('Authority') }}:</strong></td><td>' + (project.approval_authority || '-') +
                '</td></tr>';
            html += '<tr><td><strong>{{ __('NOC Number') }}:</strong></td><td>' + (project.noc_number || '-') +
                '</td></tr>';
            html += '<tr><td><strong>{{ __('Approval Date') }}:</strong></td><td>' + (project.approval_date || '-') +
                '</td></tr>';
            html += '</table></div>';

            // Towers/Blocks with Floors
            if (project.towers && project.towers.length > 0) {
                html += '<div class="col-12 mt-3"><h6>{{ __('Towers / Blocks') }}</h6>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-sm">';
                html += '<thead class="bg-light"><tr>';
                html += '<th>{{ __('Tower Code') }}</th>';
                html += '<th>{{ __('Tower Name') }}</th>';
                html += '<th>{{ __('Floors') }}</th>';
                html += '<th>{{ __('Construction') }}</th>';
                html += '<th>{{ __('Status') }}</th>';
                html += '</tr></thead><tbody>';

                project.towers.forEach(function(tower) {
                    html += '<tr>';
                    html += '<td><strong>' + tower.tower_code + '</strong></td>';
                    html += '<td>' + (tower.tower_name || '-') + '</td>';
                    html += '<td>' + tower.floors_count + '</td>';
                    html += '<td>' + (tower.construction_type || '-') + '</td>';
                    html += '<td>' + (tower.status || '-') + '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div></div>';
            }

            // Floors with Units (grouped by tower if available)
            html += '<div class="col-12 mt-3"><h6>{{ __('Floors & Units') }}</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-bordered table-sm">';
            html += '<thead class="bg-light"><tr>';
            html += '<th>{{ __('Tower') }}</th>';
            html += '<th>{{ __('Floor #') }}</th>';
            html += '<th>{{ __('Floor Name') }}</th>';
            html += '<th>{{ __('Units') }}</th>';
            html += '</tr></thead><tbody>';

            project.floors.forEach(function(floor) {
                html += '<tr>';
                html += '<td>' + (floor.tower ? floor.tower.tower_code : '-') + '</td>';
                html += '<td>' + floor.floor_number + '</td>';
                html += '<td>' + (floor.floor_name || '-') + '</td>';
                html += '<td>' + floor.total_units + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div></div>';

            // Payment Plans
            html += '<div class="col-12 mt-3"><h6>{{ __('Payment Plans') }}</h6>';
            html +=
                '<table class="table table-sm"><thead><tr><th>{{ __('Plan') }}</th><th>{{ __('Duration') }}</th><th>{{ __('Installments') }}</th><th>{{ __('Frequency') }}</th></tr></thead><tbody>';
            project.payment_plans.forEach(function(plan) {
                html += '<tr><td>' + plan.plan_name + '</td><td>' + plan.duration_months + ' months</td><td>' + plan
                    .num_installments + '</td><td>' + plan.frequency + '</td></tr>';
            });
            html += '</tbody></table></div>';

            html += '</div>';
            $('#review-content').html(html);
        }

        // Tower Management Functions
        var towerIndex = 0;

        function addTowerRow(data = null) {
            towerIndex++;
            var defaultCode = String.fromCharCode(64 + towerIndex); // A, B, C...
            if (towerIndex > 26) {
                defaultCode = 'Block-' + towerIndex;
            }

            var html = `
                <tr class="tower-row" data-index="${towerIndex}">
                    <td>
                        <input type="text" class="form-control form-control-sm tower-code" value="${data ? data.tower_code : defaultCode}" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm tower-name" value="${data ? (data.tower_name || '') : ''}" placeholder="{{ __('Tower/Block Name') }}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm tower-floors" value="${data ? data.floors_count : 1}" min="1" required>
                    </td>
                    <td>
                        <select class="form-control form-control-sm tower-construction">
                            <option value="RCC" ${data && data.construction_type === 'RCC' ? 'selected' : ''}>{{ __('RCC') }}</option>
                            <option value="Steel" ${data && data.construction_type === 'Steel' ? 'selected' : ''}>{{ __('Steel') }}</option>
                            <option value="Composite" ${data && data.construction_type === 'Composite' ? 'selected' : ''}>{{ __('Composite') }}</option>
                            <option value="Other" ${data && data.construction_type === 'Other' ? 'selected' : ''}>{{ __('Other') }}</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control form-control-sm tower-parking">
                            <option value="None" ${data && data.parking_type === 'None' ? 'selected' : ''}>{{ __('None') }}</option>
                            <option value="Basement" ${data && data.parking_type === 'Basement' ? 'selected' : ''}>{{ __('Basement') }}</option>
                            <option value="Podium" ${data && data.parking_type === 'Podium' ? 'selected' : ''}>{{ __('Podium') }}</option>
                            <option value="Mechanical" ${data && data.parking_type === 'Mechanical' ? 'selected' : ''}>{{ __('Mechanical') }}</option>
                            <option value="Open" ${data && data.parking_type === 'Open' ? 'selected' : ''}>{{ __('Open') }}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm tower-elevators" value="${data ? data.elevator_count : 0}" min="0">
                    </td>
                    <td>
                        <select class="form-control form-control-sm tower-status">
                            <option value="Planning" ${data && data.status === 'Planning' ? 'selected' : ''}>{{ __('Planning') }}</option>
                            <option value="Construction" ${data && data.status === 'Construction' ? 'selected' : ''}>{{ __('Construction') }}</option>
                            <option value="Completed" ${data && data.status === 'Completed' ? 'selected' : ''}>{{ __('Completed') }}</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeTowerRow(this)">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#towers-container').append(html);
        }

        function removeTowerRow(btn) {
            $(btn).closest('.tower-row').remove();
        }

        function skipTowersTab() {
            // Skip tower creation and go directly to floors
            maxTab = 3;
            goToTab(3);
        }

        // Tab 2: Save Towers
        $('#form-towers').on('submit', function(e) {
            e.preventDefault();

            var towers = [];
            $('.tower-row').each(function() {
                towers.push({
                    tower_code: $(this).find('.tower-code').val(),
                    tower_name: $(this).find('.tower-name').val(),
                    floors_count: $(this).find('.tower-floors').val(),
                    construction_type: $(this).find('.tower-construction').val(),
                    parking_type: $(this).find('.tower-parking').val(),
                    elevator_count: $(this).find('.tower-elevators').val(),
                    status: $(this).find('.tower-status').val()
                });
            });

            if (towers.length === 0) {
                // No towers, skip to floors
                skipTowersTab();
                return;
            }

            $.ajax({
                url: '{{ url('re-projects') }}/' + projectId + '/towers',
                type: 'POST',
                data: {
                    towers: towers
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        maxTab = response.next_tab;
                        toastr.success(response.message);
                        goToTab(response.next_tab);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || '{{ __('Something went wrong') }}');
                    }
                }
            });
        });
    </script>
@endpush
