{{ Form::model($contract, ['route' => ['contract.update', $contract->id], 'method' => 'PUT', 'id' => 'contract-edit-form', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="wizard-steps mb-4">
        <div class="wizard-step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-title">{{ __('Primary Customer') }}</div>
        </div>
        <div class="wizard-step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-title">{{ __('Joint Owners') }}</div>
        </div>
        <div class="wizard-step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-title">{{ __('Legal Terms') }}</div>
        </div>
        <div class="wizard-step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-title">{{ __('Financial Terms') }}</div>
        </div>
        <div class="wizard-step" data-step="5">
            <div class="step-number">5</div>
            <div class="step-title">{{ __('Documents') }}</div>
        </div>
    </div>

    <div class="tab-content" id="wizard-content">
        <!-- Step 1: Primary Customer -->
        <div class="tab-pane fade show active" id="step-1" role="tabpanel">
            @php $customer = $contract->customer; @endphp
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('client_type', __('Customer Type'), ['class' => 'form-label']) }}
                    {{ Form::select('client_type', ['Individual' => 'Individual', 'Company' => 'Company'], $customer->client_type ?? 'Individual', ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('full_name', __('Full Name'), ['class' => 'form-label']) }}
                    {{ Form::text('full_name', $customer->full_name ?? ($customer->name ?? ''), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('father_or_spouse_name', __('Father / Spouse Name'), ['class' => 'form-label']) }}
                    {{ Form::text('father_or_spouse_name', $customer->father_or_spouse_name ?? '', ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('date_of_birth', __('Date of Birth'), ['class' => 'form-label']) }}
                    {{ Form::date('date_of_birth', $customer->date_of_birth ?? null, ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('nationality', __('Nationality'), ['class' => 'form-label']) }}
                    {{ Form::text('nationality', $customer->nationality ?? '', ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('cnic_number', __('CNIC / NTN Number'), ['class' => 'form-label']) }}
                    {{ Form::text('cnic_number', $customer->cnic_number ?? '', ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('mobile_primary', __('Mobile (Primary)'), ['class' => 'form-label']) }}
                    {{ Form::text('mobile_primary', $customer->mobile_primary ?? ($customer->contact ?? ''), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                    {{ Form::email('email', $customer->email ?? '', ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('current_address', __('Current Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('current_address', $customer->current_address ?? '', ['class' => 'form-control', 'rows' => 2]) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('permanent_address', __('Permanent Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('permanent_address', $customer->permanent_address ?? '', ['class' => 'form-control', 'rows' => 2]) }}
                </div>
            </div>
        </div>

        <!-- Step 2: Joint Owners -->
        <div class="tab-pane fade" id="step-2" role="tabpanel">
            <div class="row mb-3">
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-sm btn-primary" id="add-owner-row">
                        <i class="ti ti-plus"></i> {{ __('Add Joint Owner') }}
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="joint-owners-table">
                    <thead>
                        <tr>
                            <th>{{ __('Full Name') }}</th>
                            <th>{{ __('CNIC') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Ownership %') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="joint-owners-body">
                        @foreach ($joint_owners as $index => $owner)
                            <tr id="owner-row-{{ $index }}">
                                <td><input type="text" name="joint_owners[{{ $index }}][name]"
                                        class="form-control form-control-sm" value="{{ $owner->customer->full_name }}"
                                        required></td>
                                <td><input type="text" name="joint_owners[{{ $index }}][cnic]"
                                        class="form-control form-control-sm"
                                        value="{{ $owner->customer->cnic_number }}" required></td>
                                <td><input type="text" name="joint_owners[{{ $index }}][phone]"
                                        class="form-control form-control-sm"
                                        value="{{ $owner->customer->mobile_primary }}"></td>
                                <td><input type="number" name="joint_owners[{{ $index }}][percent]"
                                        class="form-control form-control-sm" value="{{ $owner->ownership_percent }}"
                                        step="0.01"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-owner"
                                        data-id="{{ $index }}"><i class="ti ti-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="no-owners-msg" class="text-center text-muted py-3"
                style="{{ count($joint_owners) > 0 ? 'display:none;' : '' }}">
                {{ __('No joint owners added. Primary owner will have 100% ownership.') }}
            </div>
        </div>

        <!-- Step 3: Legal Terms -->
        <div class="tab-pane fade" id="step-3" role="tabpanel">
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('subject', __('Contract Subject/Title'), ['class' => 'form-label']) }}
                    {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('project_id', __('Select Project'), ['class' => 'form-label']) }}
                    {{ Form::select('project_id', $projects, $contract->re_project_id, ['class' => 'form-control select', 'id' => 'project_id', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('tower_id', __('Tower / Block'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="tower_id" name="tower_id">
                        <option value="">{{ __('Select Tower') }}</option>
                        @foreach ($towers as $tid => $tname)
                            <option value="{{ $tid }}" {{ $contract->tower_id == $tid ? 'selected' : '' }}>
                                {{ $tname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('floor_id', __('Floor'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="floor_id" name="floor_id">
                        <option value="">{{ __('Select Floor') }}</option>
                        @foreach ($floors as $fid => $fname)
                            <option value="{{ $fid }}" {{ $contract->floor_id == $fid ? 'selected' : '' }}>
                                {{ $fname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="unit_id" name="unit_id" required>
                        <option value="">{{ __('Select Unit') }}</option>
                        @foreach ($units as $uid => $uname)
                            <option value="{{ $uid }}" {{ $contract->unit_id == $uid ? 'selected' : '' }}>
                                {{ $uname }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <div class="form-group col-md-6">
                    {{ Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) }}
                    {{ Form::number('sale_price', $contract->sale_price ?? $contract->value, ['class' => 'form-control', 'id' => 'sale_price', 'step' => '0.01', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('booking_date', __('Booking Date'), ['class' => 'form-label']) }}
                    {{ Form::date('booking_date', $contract->booking_date ?? $contract->start_date, ['class' => 'form-control', 'id' => 'booking_date', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('agreement_date', __('Agreement Date'), ['class' => 'form-label']) }}
                    {{ Form::date('agreement_date', null, ['class' => 'form-control']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('possession_due_date', __('Possession Due Date'), ['class' => 'form-label']) }}
                    {{ Form::date('possession_due_date', null, ['class' => 'form-control']) }}
                </div>

                <div class="form-group col-md-12">
                    {{ Form::label('description', __('Description/Notes'), ['class' => 'form-label']) }}
                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) }}
                </div>

                @if (!$customFields->isEmpty())
                    <div class="col-12 form-group">
                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                            @include('customFields.formBuilder')
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Step 4: Financial Terms -->
        <div class="tab-pane fade" id="step-4" role="tabpanel">
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('payment_plan_type', __('Plan Type'), ['class' => 'form-label']) }}
                    {{ Form::select('payment_plan_type', ['Installment' => 'Installment', 'FullPayment' => 'Full Payment'], null, ['class' => 'form-control', 'id' => 'payment_plan_type']) }}
                </div>

                <div id="installment-section" class="col-12"
                    style="{{ $contract->payment_plan_type == 'FullPayment' ? 'display:none;' : '' }}">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('payment_plan_id', __('Update Template Plan'), ['class' => 'form-label']) }}
                            <select class="form-control" id="payment_plan_id" name="payment_plan_id">
                                <option value="">{{ __('-- Select Template --') }}</option>
                                @foreach ($payment_plans as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-down-percent="{{ $plan->down_payment_percentage }}"
                                        data-installments="{{ $plan->num_installments }}"
                                        data-frequency="{{ $plan->frequency }}">
                                        {{ $plan->plan_name }} ({{ $plan->frequency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            {{ Form::label('installment_frequency', __('Installment Frequency'), ['class' => 'form-label']) }}
                            {{ Form::select(
                                'installment_frequency',
                                [
                                    'Monthly' => 'Monthly ',
                                    'Quarterly' => 'Quarterly',
                                    'Half-Yearly' => 'Half Yearly',
                                    'Yearly' => 'Yearly',
                                ],
                                optional($contract->installment_plan)->installment_frequency ?? 'Monthly',
                                ['class' => 'form-control', 'id' => 'installment_frequency'],
                            ) }}
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('down_payment_amount', __('Down Payment'), ['class' => 'form-label']) }}
                            {{ Form::number('down_payment_amount', optional($contract->installment_plan)->down_payment_amount ?? 0, ['class' => 'form-control', 'id' => 'down_payment_amount', 'step' => '0.01']) }}
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('installment_count', __('No. of Installments'), ['class' => 'form-label']) }}
                            {{ Form::number('installment_count', optional($contract->installment_plan)->installment_count ?? 1, ['class' => 'form-control', 'id' => 'installment_count']) }}
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('installment_amount', __('Per Installment'), ['class' => 'form-label']) }}
                            {{ Form::number('installment_amount', optional($contract->installment_plan)->installment_amount ?? 0, ['class' => 'form-control', 'id' => 'installment_amount', 'step' => '0.01', 'readonly' => 'readonly']) }}
                        </div>
                    </div>

                    <!-- Installment Schedule Preview -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">{{ __('Installment Schedule Preview') }}</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="installment-preview-body">
                                            @if ($contract->installments->count() > 0)
                                                @foreach ($contract->installments as $inst)
                                                    <tr>
                                                        <td>{{ $inst->installment_number }}</td>
                                                        <td><span
                                                                class="badge {{ $inst->installment_type == 'down_payment' ? 'bg-success' : 'bg-primary' }}">{{ ucfirst(str_replace('_', ' ', $inst->installment_type)) }}</span>
                                                        </td>
                                                        <td>{{ number_format($inst->amount, 2) }}</td>
                                                        <td>{{ $inst->due_date }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        {{ __('No installments generated yet.') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="alert alert-secondary text-center">
                        <strong>{{ __('Total Payable:') }}</strong> <span id="total_payable_display">0.00</span>
                    </div>
                </div>

                @php
                    $hasInvoices = $contract->installments()->whereNotNull('invoice_id')->exists();
                @endphp
                @if ($hasInvoices)
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle"></i>
                            {{ __('Some installments have generated invoices. Pricing and installment steps are locked for editing to maintain data integrity.') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Step 5: Documents -->
        <div class="tab-pane fade" id="step-5" role="tabpanel">
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('doc_cnic_front', __('CNIC Front'), ['class' => 'form-label']) }}
                    {{ Form::file('doc_cnic_front', ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('doc_cnic_back', __('CNIC Back'), ['class' => 'form-label']) }}
                    {{ Form::file('doc_cnic_back', ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('doc_photo', __('Customer Photo'), ['class' => 'form-label']) }}
                    {{ Form::file('doc_photo', ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('doc_signature', __('Customer Signature'), ['class' => 'form-label']) }}
                    {{ Form::file('doc_signature', ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" id="prev-step"
        style="display: none;">{{ __('Previous') }}</button>
    <button type="button" class="btn btn-primary" id="next-step">{{ __('Next') }}</button>
    <button type="submit" class="btn btn-success" id="submit-wizard"
        style="display: none;">{{ __('Update Contract') }}</button>
</div>
{{ Form::close() }}

<style>
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 5%;
        right: 5%;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }

    .wizard-step {
        z-index: 2;
        text-align: center;
        flex: 1;
        pointer-events: none;
    }

    .wizard-step.active .step-number {
        border-color: #6fd943;
        background: #6fd943;
        color: #fff;
    }

    .wizard-step.completed .step-number {
        background: #17a2b8;
        border-color: #17a2b8;
        color: #fff;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 5px;
        font-weight: bold;
        transition: all 0.3s;
    }

    .step-title {
        font-size: 11px;
        color: #6c757d;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
    }
</style>

<script>
    $(document).ready(function() {
        let currentStep = 1;
        const totalSteps = 5;
        const hasInvoices = @json($hasInvoices);

        if (hasInvoices) {
            $('#sale_price, #payment_plan_type, #payment_plan_id, #installment_frequency, #down_payment_amount, #installment_count, #booking_date')
                .attr('readonly', true);
            $('#payment_plan_id, #payment_plan_type, #installment_frequency').on('mousedown', function(e) {
                e.preventDefault();
            });
        }

        function validateStep(step) {
            let isValid = true;
            $(`#step-${step} [required]`).each(function() {
                let field = $(this);
                field.removeClass('is-invalid').siblings('.invalid-feedback').remove();
                if (!field.val() || field.val().trim() === '') {
                    isValid = false;
                    field.addClass('is-invalid').after(
                        `<div class="invalid-feedback">{{ __('Required') }}</div>`);
                }
            });
            return isValid;
        }

        $('#next-step').click(function() {
            if (!validateStep(currentStep)) return false;
            if (currentStep < totalSteps) {
                $(`#step-${currentStep}`).removeClass('show active');
                $(`.wizard-step[data-step="${currentStep}"]`).addClass('completed');
                currentStep++;
                $(`#step-${currentStep}`).addClass('show active');
                $(`.wizard-step[data-step="${currentStep}"]`).addClass('active');
                updateButtons();
            }
        });

        $('#prev-step').click(function() {
            if (currentStep > 1) {
                $(`#step-${currentStep}`).removeClass('show active');
                $(`.wizard-step[data-step="${currentStep}"]`).removeClass('active');
                currentStep--;
                $(`#step-${currentStep}`).addClass('show active');
                updateButtons();
            }
        });

        function updateButtons() {
            $('#prev-step').toggle(currentStep > 1);
            $('#next-step').toggle(currentStep < totalSteps);
            $('#submit-wizard').toggle(currentStep === totalSteps);
        }

        // Joint Owners Logic
        let ownerIndex = $('#joint-owners-body tr').length;
        $('#add-owner-row').click(function() {
            $('#no-owners-msg').hide();
            let html = `
                <tr id="owner-row-${ownerIndex}">
                    <td><input type="text" name="joint_owners[${ownerIndex}][name]" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="joint_owners[${ownerIndex}][cnic]" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="joint_owners[${ownerIndex}][phone]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="joint_owners[${ownerIndex}][percent]" class="form-control form-control-sm" value="0" step="0.01"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-owner" data-id="${ownerIndex}"><i class="ti ti-trash"></i></button></td>
                </tr>
            `;
            $('#joint-owners-body').append(html);
            ownerIndex++;
        });

        $(document).on('click', '.remove-owner', function() {
            $(`#owner-row-${$(this).data('id')}`).remove();
            if ($('#joint-owners-body tr').length === 0) $('#no-owners-msg').show();
        });

        // AJAX Cascading selects for Project/Tower/Floor/Unit
        $('#project_id').change(function() {
            let pid = $(this).val();
            if (!pid) return;
            $.post('{{ route('deals.towers.json') }}', {
                project_id: pid,
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#tower_id').empty().append(
                    '<option value="">{{ __('Select Tower') }}</option>');
                $.each(data, (id, name) => $('#tower_id').append(
                    `<option value="${id}">${name}</option>`));
            });
            $.post('{{ route('deals.payment.plans.json') }}', {
                project_id: pid,
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#payment_plan_id').empty().append(
                    '<option value="">{{ __('-- Select Template --') }}</option>');
                $.each(data, (i, plan) => {
                    $('#payment_plan_id').append(
                        `<option value="${plan.id}" data-down-percent="${plan.down_payment_percentage}" data-installments="${plan.num_installments}" data-frequency="${plan.frequency}">${plan.plan_name}</option>`
                        );
                });
            });
        });

        $('#tower_id').change(function() {
            $.post('{{ route('deals.floors.json') }}', {
                tower_id: $(this).val(),
                project_id: $('#project_id').val(),
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#floor_id').empty().append(
                    '<option value="">{{ __('Select Floor') }}</option>');
                $.each(data, (id, name) => $('#floor_id').append(
                    `<option value="${id}">${name}</option>`));
            });
        });

        $('#floor_id').change(function() {
            $.post('{{ route('deals.units.json') }}', {
                floor_id: $(this).val(),
                _token: '{{ csrf_token() }}'
            }, function(data) {
                $('#unit_id').empty().append(
                    '<option value="">{{ __('Select Unit') }}</option>');
                $.each(data, (id, name) => $('#unit_id').append(
                    `<option value="${id}">${name}</option>`));
            });
        });

        // Financial Logic
        $('#payment_plan_id').change(function() {
            let opt = $(this).find(':selected');
            if (opt.val() && !hasInvoices) {
                let downP = parseFloat(opt.data('down-percent')) || 0;
                let inst = parseInt(opt.data('installments')) || 0;
                let saleP = parseFloat($('#sale_price').val()) || 0;
                $('#down_payment_amount').val(((saleP * downP) / 100).toFixed(2));
                $('#installment_count').val(inst);
                $('#installment_frequency').val(opt.data('frequency'));
                calculateTotal();
            }
        });

        function calculateTotal() {
            let saleP = parseFloat($('#sale_price').val()) || 0;
            let downA = parseFloat($('#down_payment_amount').val()) || 0;
            let instC = parseInt($('#installment_count').val()) || 1;
            let perInst = instC > 0 ? (saleP - downA) / instC : 0;
            $('#installment_amount').val(perInst.toFixed(2));
            $('#total_payable_display').text(saleP.toLocaleString(undefined, {
                minimumFractionDigits: 2
            }));
            if (!hasInvoices) generatePreview();
        }

        function generatePreview() {
            let saleP = parseFloat($('#sale_price').val()) || 0;
            let downA = parseFloat($('#down_payment_amount').val()) || 0;
            let instC = parseInt($('#installment_count').val()) || 0;
            let freq = $('#installment_frequency').val();
            let start = new Date($('#booking_date').val());
            if (!start || saleP <= 0) return;

            let html = '';
            let count = 1;
            if (downA > 0) {
                let dDate = new Date(start);
                dDate.setDate(dDate.getDate() + 7);
                html +=
                    `<tr><td>${count++}</td><td><span class="badge bg-success">Down Payment</span></td><td>${downA.toFixed(2)}</td><td>${dDate.toISOString().split('T')[0]}</td></tr>`;
            }
            let freqMap = {
                'Monthly': 1,
                'Quarterly': 3,
                'Half-Yearly': 6,
                'Yearly': 12
            };
            let step = freqMap[freq] || 1;
            for (let i = 0; i < instC; i++) {
                let iDate = new Date(start);
                iDate.setMonth(iDate.getMonth() + (i * step));
                let dDate = new Date(iDate);
                dDate.setDate(dDate.getDate() + 15);
                let amt = (i === instC - 1) ? (saleP - (downA + (perInst * (instC - 1)))) : ((saleP - downA) /
                    instC);
                html +=
                    `<tr><td>${count++}</td><td><span class="badge bg-primary">Installment ${i+1}</span></td><td>${amt.toFixed(2)}</td><td>${dDate.toISOString().split('T')[0]}</td></tr>`;
            }
            $('#installment-preview-body').html(html ||
                '<tr><td colspan="4" class="text-center">No schedule</td></tr>');
        }

        $('#sale_price, #down_payment_amount, #installment_count, #installment_frequency, #booking_date').on(
            'input change', calculateTotal);
        $('#payment_plan_type').change(function() {
            $('#installment-section').toggle($(this).val() === 'Installment');
            calculateTotal();
        });

        calculateTotal();
    });
</script>
