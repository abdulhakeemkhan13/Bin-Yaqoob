{{ Form::open(['route' => ['deals.convert.contract.store', $deal->id], 'method' => 'post', 'id' => 'convert-contract-form', 'enctype' => 'multipart/form-data']) }}
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
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('client_type', __('Customer Type'), ['class' => 'form-label']) }}
                    {{ Form::select('client_type', ['Individual' => 'Individual', 'Company' => 'Company'], $prefill['client_type'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('full_name', __('Full Name'), ['class' => 'form-label']) }}
                    {{ Form::text('full_name', $prefill['full_name'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('father_or_spouse_name', __('Father / Spouse Name'), ['class' => 'form-label']) }}
                    {{ Form::text('father_or_spouse_name', $prefill['father_or_spouse_name'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('date_of_birth', __('Date of Birth'), ['class' => 'form-label']) }}
                    {{ Form::date('date_of_birth', null, ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('nationality', __('Nationality'), ['class' => 'form-label']) }}
                    {{ Form::text('nationality', $prefill['nationality'], ['class' => 'form-control']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('cnic_number', __('CNIC / NTN Number'), ['class' => 'form-label']) }}
                    {{ Form::text('cnic_number', $prefill['cnic_number'], ['class' => 'form-control', 'required' => 'required', 'data-inputmask' => "'mask': '99999-9999999-9'", 'data-mask' => 'true', 'minlength' => '13', 'maxlength' => '13']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('mobile_primary', __('Mobile (Primary)'), ['class' => 'form-label']) }}
                    {{ Form::text('mobile_primary', $prefill['mobile_primary'], ['class' => 'form-control', 'required' => 'required', 'data-inputmask' => "'mask': '9999999999'", 'data-mask' => 'true', 'minlength' => '9', 'maxlength' => '12']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                    {{ Form::email('email', $prefill['email'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('current_address', __('Current Address'), ['class' => 'form-label']) }}
                    {{ Form::textarea('current_address', $prefill['current_address'], ['class' => 'form-control', 'rows' => 2]) }}
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
                        <!-- Rows added via JS -->
                    </tbody>
                </table>
            </div>
            <div id="no-owners-msg" class="text-center text-muted py-3">
                {{ __('No joint owners added. Primary owner will have 100% ownership.') }}
            </div>
        </div>

        <!-- Step 3: Legal Terms -->
        <div class="tab-pane fade" id="step-3" role="tabpanel">
            <div class="row">
                <div class="form-group col-md-12">
                    {{ Form::label('subject', __('Contract Subject/Title'), ['class' => 'form-label']) }}
                    {{ Form::text('subject', $prefill['subject'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('project_id', __('Select Project'), ['class' => 'form-label']) }}
                    {{ Form::select('project_id_display', $projects, $deal->re_project_id, ['class' => 'form-control select', 'id' => 'project_id_display', 'disabled' => 'disabled']) }}
                    {{ Form::hidden('project_id', $deal->re_project_id, ['id' => 'project_id']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('tower_id_display', __('Tower / Block'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="tower_id_display" disabled>
                        <option value="{{ $deal->re_tower_id }}" selected>{{ $prefill['tower_name'] }}</option>
                    </select>
                    {{ Form::hidden('tower_id', $deal->re_tower_id, ['id' => 'tower_id']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('floor_id_display', __('Floor'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="floor_id_display" disabled>
                        <option value="{{ $deal->re_floor_id }}" selected>{{ $prefill['floor_name'] }}</option>
                    </select>
                    {{ Form::hidden('floor_id', $deal->re_floor_id, ['id' => 'floor_id']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('unit_id_display', __('Unit'), ['class' => 'form-label']) }}
                    <select class="form-control select" id="unit_id_display" disabled>
                        <option value="{{ $deal->re_unit_id }}" selected>{{ $prefill['unit_name'] }}</option>
                    </select>
                    {{ Form::hidden('unit_id', $deal->re_unit_id, ['id' => 'unit_id']) }}
                </div>

                <hr>

                <div class="form-group col-md-6">
                    {{ Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) }}
                    {{ Form::number('sale_price', $prefill['value'], ['class' => 'form-control', 'id' => 'sale_price', 'step' => '0.01', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('booking_date', __('Booking Date'), ['class' => 'form-label']) }}
                    {{ Form::date('booking_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('agreement_date', __('Agreement Date'), ['class' => 'form-label']) }}
                    {{ Form::date('agreement_date', null, ['class' => 'form-control']) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('possession_due_date', __('Possession Due Date'), ['class' => 'form-label']) }}
                    {{ Form::date('possession_due_date', $prefill['end_date'], ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <!-- Step 4: Financial Terms -->
        <div class="tab-pane fade" id="step-4" role="tabpanel">
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('payment_plan_type', __('Plan Type'), ['class' => 'form-label']) }}
                    {{ Form::select('payment_plan_type', ['Installment' => 'Installment', 'FullPayment' => 'Full Payment'], 'FullPayment', ['class' => 'form-control', 'id' => 'payment_plan_type']) }}
                </div>

                <div id="installment-section" class="col-12">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('payment_plan_id', __('Select Template Plan'), ['class' => 'form-label']) }}
                            <select class="form-control" id="payment_plan_id" name="payment_plan_id">
                                {{-- <option value="">{{ __('-- Select Payment Plan --') }}</option> --}}
                                @foreach ($payment_plans as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-down-percent="{{ $plan->down_payment_percentage }}"
                                        data-installments="{{ $plan->num_installments }}"
                                        data-frequency="{{ $plan->frequency }}"
                                        data-booking-charges="{{ $plan->booking_charges }}"
                                        data-possession-charges="{{ $plan->possession_charges }}"
                                        data-discount="{{ $plan->discount }}"
                                        data-extra-charges="{{ $plan->extra_charges }}">
                                        {{ $plan->plan_name }} ({{ $plan->frequency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            {{ Form::label('installment_frequency', __('Installment Frequency'), ['class' => 'form-label']) }}
                            {{ Form::text('installment_frequency', 'Monthly', ['class' => 'form-control', 'id' => 'installment_frequency', 'readonly' => 'readonly']) }}
                            <small class="text-muted">{{ __('Based on selected payment plan') }}</small>
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('down_payment_amount', __('Down Payment'), ['class' => 'form-label']) }}
                            {{ Form::number('down_payment_amount', 0, ['class' => 'form-control', 'id' => 'down_payment_amount', 'step' => '0.01']) }}
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('installment_count', __('No. of Installments'), ['class' => 'form-label']) }}
                            {{ Form::number('installment_count', 12, ['class' => 'form-control', 'id' => 'installment_count']) }}
                        </div>

                        <div class="form-group col-md-4">
                            {{ Form::label('installment_amount', __('Per Installment'), ['class' => 'form-label']) }}
                            {{ Form::number('installment_amount', 0, ['class' => 'form-control', 'id' => 'installment_amount', 'step' => '0.01', 'readonly' => 'readonly']) }}
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
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                    {{ __('Select a payment plan to see the schedule') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Fine Configuration Section -->
                <div class="col-12">
                    <h6 class="mb-3"><i class="ti ti-clock-exclamation"></i> {{ __('Fine Configuration') }}</h6>
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('fine_percentage', __('Fine Percentage (%)'), ['class' => 'form-label']) }}
                    {{ Form::number('fine_percentage', 0, ['class' => 'form-control', 'id' => 'fine_percentage', 'step' => '0.01', 'min' => '0', 'max' => '100', 'placeholder' => 'e.g., 2']) }}
                    <small class="text-muted">{{ __('Percentage to apply as fine on overdue amount') }}</small>
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('fine_apply_after_due_date', __('Apply After (Days)'), ['class' => 'form-label']) }}
                    {{ Form::number('fine_apply_after_due_date', 0, ['class' => 'form-control', 'id' => 'fine_apply_after_due_date', 'min' => '0', 'placeholder' => 'e.g., 5']) }}
                    <small class="text-muted">{{ __('Number of days after due date before fine applies') }}</small>
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('fine_frequency', __('Fine Frequency'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'fine_frequency',
                        [
                            'one_time' => 'One Time Only',
                            'every_month_after_due' => 'Every Month After Due',
                        ],
                        'one_time',
                        ['class' => 'form-control', 'id' => 'fine_frequency'],
                    ) }}
                    <small class="text-muted">{{ __('How often to apply the fine') }}</small>
                </div>

                <hr class="my-4">

                <!-- Discount Configuration Section -->
                <div class="col-12">
                    <h6 class="mb-3"><i class="ti ti-discount"></i> {{ __('Discount Configuration') }}</h6>
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('discount_type', __('Discount Type'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'discount_type',
                        [
                            '' => 'No Discount',
                            'percentage' => 'Percentage',
                            'fixed_amount' => 'Fixed Amount',
                        ],
                        '',
                        ['class' => 'form-control', 'id' => 'discount_type'],
                    ) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('discount_value', __('Discount Value'), ['class' => 'form-label']) }}
                    {{ Form::number('discount_value', 0, ['class' => 'form-control', 'id' => 'discount_value', 'step' => '0.01', 'min' => '0', 'placeholder' => 'Enter discount value']) }}
                    <small class="text-muted"
                        id="discount_hint">{{ __('Enter percentage or fixed amount based on type selected') }}</small>
                </div>

                <hr class="my-4">

                <!-- Possession Charge Section -->
                <div class="col-12">
                    <h6 class="mb-3"><i class="ti ti-home-check"></i> {{ __('Possession Charges') }}</h6>
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('possession_charge_percentage', __('Possession Charge (%)'), ['class' => 'form-label']) }}
                    {{ Form::number('possession_charge_percentage', 0, ['class' => 'form-control', 'id' => 'possession_charge_percentage', 'step' => '0.01', 'min' => '0', 'max' => '100']) }}
                    <small class="text-muted">{{ __('Percentage of sale price as possession charge') }}</small>
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('possession_charge_type', __('Possession Charge Type'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'possession_charge_type',
                        [
                            '' => 'Select Type',
                            'on_handover' => 'On Handover',
                            'installment' => 'Part of Installment Plan',
                        ],
                        '',
                        ['class' => 'form-control', 'id' => 'possession_charge_type'],
                    ) }}
                </div>

                <hr class="my-4">

                <div class="col-12 mt-3">
                    <div class="alert alert-secondary">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>{{ __('Sale Price:') }}</strong><br>
                                <span id="sale_price_display" class="h5">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Discount:') }}</strong><br>
                                <span id="discount_amount_display" class="h5 text-success">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Possession Charge:') }}</strong><br>
                                <span id="possession_charge_display" class="h5 text-warning">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Total Payable:') }}</strong><br>
                                <span id="total_payable_display" class="h5 text-primary">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
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
        style="display: none;">{{ __('Convert to Contract') }}</button>
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

    .step-title {
        font-size: 11px;
        color: #6c757d;
    }
</style>

<script>
    $(document).ready(function() {
        let currentStep = 1;
        const totalSteps = 5;

        // Validate current step required fields
        function validateStep(step) {
            let isValid = true;
            let firstInvalidField = null;

            $(`#step-${step} [required]`).each(function() {
                let field = $(this);
                let value = field.val();

                // Remove previous error styling
                field.removeClass('is-invalid');
                field.siblings('.invalid-feedback').remove();

                if (!value || value.trim() === '') {
                    isValid = false;
                    field.addClass('is-invalid');

                    // Add error message
                    let label = field.closest('.form-group').find('label').text() || 'This field';
                    field.after(
                        `<div class="invalid-feedback">{{ __('${label} is required') }}</div>`);

                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                }
            });

            if (!isValid && firstInvalidField) {
                firstInvalidField.focus();
                toastr.error('{{ __('Please fill all required fields') }}');
            }

            return isValid;
        }

        // Navigation
        $('#next-step').click(function() {
            // Validate current step before proceeding
            if (!validateStep(currentStep)) {
                return false;
            }

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

        // Joint Owners Repeater
        let ownerIndex = 0;
        $('#add-owner-row').click(function() {
            $('#no-owners-msg').hide();
            ownerIndex++;
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
        });

        $(document).on('click', '.remove-owner', function() {
            let id = $(this).data('id');
            $(`#owner-row-${id}`).remove();
            if ($('#joint-owners-body tr').length === 0) {
                $('#no-owners-msg').show();
            }
        });

        $('#payment_plan_id').change(function() {
            let opt = $(this).find(':selected');
            if (opt.val()) {
                let downP = parseFloat(opt.data('down-percent')) || 0;
                let inst = parseInt(opt.data('installments')) || 0;
                let frequency = opt.data('frequency') || 'Monthly';
                let saleP = parseFloat($('#sale_price').val()) || 0;

                let downAmt = (saleP * downP) / 100;
                $('#down_payment_amount').val(downAmt.toFixed(2));
                $('#installment_count').val(inst);
                $('#installment_frequency').val(frequency);

                calculateTotal();
                generateInstallmentPreview();
            }
        });

        function getFrequencyMonths(frequency) {
            switch (frequency) {
                case 'Monthly':
                    return 1;
                case 'Quarterly':
                    return 3;
                case 'Half-Yearly':
                    return 6;
                case 'Yearly':
                    return 12;
                default:
                    return 1;
            }
        }

        function addMonths(date, months) {
            let result = new Date(date);
            result.setMonth(result.getMonth() + months);
            return result;
        }

        function formatDate(date) {
            let year = date.getFullYear();
            let month = ('0' + (date.getMonth() + 1)).slice(-2);
            let day = ('0' + date.getDate()).slice(-2);
            return `${year}-${month}-${day}`;
        }

        function generateInstallmentPreview() {
            let saleP = parseFloat($('#sale_price').val()) || 0;
            let downA = parseFloat($('#down_payment_amount').val()) || 0;
            let instC = parseInt($('#installment_count').val()) || 0;
            let frequency = $('#installment_frequency').val() || 'Monthly';
            let bookingDate = $('#booking_date').val();

            if (!bookingDate || saleP <= 0) {
                $('#installment-preview-body').html(
                    '<tr><td colspan="4" class="text-center text-muted">{{ __('Enter sale price and booking date to see schedule') }}</td></tr>'
                );
                return;
            }

            let startDate = new Date(bookingDate);
            let frequencyMonths = getFrequencyMonths(frequency);

            // Calculate discount
            let discountType = $('#discount_type').val();
            let discountValue = parseFloat($('#discount_value').val()) || 0;
            let discountAmount = 0;
            if (discountType === 'percentage') {
                discountAmount = (saleP * discountValue) / 100;
            } else if (discountType === 'fixed_amount') {
                discountAmount = discountValue;
            }

            // Calculate possession charge
            let possessionChargePercent = parseFloat($('#possession_charge_percentage').val()) || 0;
            let possessionCharge = (saleP * possessionChargePercent) / 100;

            // Calculate installment base: Sale Price - Down Payment - Discount - Possession Charge
            let installmentBase = saleP - downA - discountAmount - possessionCharge;
            let perInst = instC > 0 ? installmentBase / instC : 0;

            let html = '';
            let installmentNumber = 1;

            // Down Payment row
            if (downA > 0) {
                let downDueDate = new Date(startDate);
                downDueDate.setDate(downDueDate.getDate() + 7); // 7 days grace
                html += `<tr>
                    <td>${installmentNumber}</td>
                    <td><span class="badge bg-success">Down Payment</span></td>
                    <td>${parseFloat(downA).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${formatDate(downDueDate)}</td>
                </tr>`;
                installmentNumber++;
            }

            // Installment rows
            let totalDisbursed = 0;
            for (let i = 0; i < instC; i++) {
                let issueDate = addMonths(startDate, frequencyMonths * i);
                let dueDate = new Date(issueDate);
                dueDate.setDate(dueDate.getDate() + 15); // 15 days grace

                // Last installment adjustment for rounding
                let amount = perInst;
                if (i === instC - 1) {
                    amount = installmentBase - totalDisbursed;
                }
                totalDisbursed += amount;

                html += `<tr>
                    <td>${installmentNumber}</td>
                    <td><span class="badge bg-primary">Installment ${i + 1}</span></td>
                    <td>${parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${formatDate(dueDate)}</td>
                </tr>`;
                installmentNumber++;
            }

            if (html === '') {
                html =
                    '<tr><td colspan="4" class="text-center text-muted">{{ __('No installments to show') }}</td></tr>';
            }

            // Add summary rows at the bottom for Discount and Possession Charge
            html += `
                <tr class="table-secondary">
                    <td colspan="4"><strong>{{ __('Deductions/Charges Summary') }}</strong></td>
                </tr>
                <tr class="table-light">
                    <td colspan="2"><strong>{{ __('(-) Discount') }}</strong></td>
                    <td colspan="2" class="text-end"><strong class="text-success">${discountAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                </tr>
                <tr class="table-light">
                    <td colspan="2"><strong>{{ __('(-) Possession Charge') }}</strong></td>
                    <td colspan="2" class="text-end"><strong class="text-warning">${possessionCharge.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                </tr>
            `;

            $('#installment-preview-body').html(html);
        }

        function calculateTotal() {
            let saleP = parseFloat($('#sale_price').val()) || 0;
            let downA = parseFloat($('#down_payment_amount').val()) || 0;
            let instC = parseInt($('#installment_count').val()) || 1;

            // Calculate discount
            let discountType = $('#discount_type').val();
            let discountValue = parseFloat($('#discount_value').val()) || 0;
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (saleP * discountValue) / 100;
            } else if (discountType === 'fixed_amount') {
                discountAmount = discountValue;
            }

            // Calculate possession charge
            let possessionChargePercent = parseFloat($('#possession_charge_percentage').val()) || 0;
            let possessionCharge = (saleP * possessionChargePercent) / 100;

            // Calculate net total
            let netTotal = saleP - discountAmount;

            // Update displays
            $('#sale_price_display').text(saleP.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Discount shows as negative (subtraction)
            $('#discount_amount_display').text(discountAmount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Possession charge shows as positive (addition)
            $('#possession_charge_display').text(possessionCharge.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            $('#total_payable_display').text(netTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));


            // Calculate installment amount: Sale Price - Down Payment - Discount - Possession Charge
            // Example: 1,200,000 - 120,000 - 60,000 - 60,000 = 960,000 / number of installments
            let installmentBase = saleP - downA - discountAmount - possessionCharge;
            let perInst = instC > 0 ? installmentBase / instC : 0;

            $('#installment_amount').val(perInst.toFixed(2));


            // Update preview when values change
            generateInstallmentPreview();
        }

        $('#sale_price, #down_payment_amount, #installment_count, #installment_frequency, #booking_date, #discount_type, #discount_value, #possession_charge_percentage')
            .on(
                'input change', calculateTotal);

        // Update discount hint based on type
        $('#discount_type').on('change', function() {
            let hint = '';
            if ($(this).val() === 'percentage') {
                hint = '{{ __('Enter percentage value (e.g., 10 for 10%)') }}';
            } else if ($(this).val() === 'fixed_amount') {
                hint = '{{ __('Enter fixed discount amount') }}';
            } else {
                hint = '{{ __('Enter percentage or fixed amount based on type selected') }}';
            }
            $('#discount_hint').text(hint);
        });

        // Toggle installment section based on plan type
        $('#payment_plan_type').change(function() {
            if ($(this).val() === 'FullPayment') {
                $('#installment-section').hide();
            } else {
                $('#installment-section').show();
            }
            calculateTotal();
        }).trigger('change');

        // Initialize total on page load
        calculateTotal();
    });
</script>
