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
                    {{ Form::text('father_or_spouse_name', $prefill['father_or_spouse_name'], ['class' => 'form-control']) }}
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
                    {{ Form::text('cnic_number', $prefill['cnic_number'], ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('mobile_primary', __('Mobile (Primary)'), ['class' => 'form-label']) }}
                    {{ Form::text('mobile_primary', $prefill['mobile_primary'], ['class' => 'form-control', 'required' => 'required']) }}
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
                    {{ Form::select('payment_plan_type', ['Installment' => 'Installment', 'FullPayment' => 'Full Payment'], 'Installment', ['class' => 'form-control', 'id' => 'payment_plan_type']) }}
                </div>

                <div id="installment-section" class="col-12">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('payment_plan_id', __('Select Template Plan'), ['class' => 'form-label']) }}
                            <select class="form-control" id="payment_plan_id" name="payment_plan_id">
                                <option value="">{{ __('-- Select Payment Plan --') }}</option>
                                @foreach ($payment_plans as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-down-percent="{{ $plan->down_payment_percentage }}"
                                        data-installments="{{ $plan->num_installments }}">
                                        {{ $plan->plan_name }}
                                    </option>
                                @endforeach
                            </select>
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
                            {{ Form::number('installment_amount', 0, ['class' => 'form-control', 'id' => 'installment_amount', 'step' => '0.01']) }}
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="alert alert-secondary text-center">
                        <strong>{{ __('Total Payable:') }}</strong> <span id="total_payable_display">0.00</span>
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

        // Navigation
        $('#next-step').click(function() {
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
                let saleP = parseFloat($('#sale_price').val()) || 0;

                let downAmt = (saleP * downP) / 100;
                $('#down_payment_amount').val(downAmt.toFixed(2));
                $('#installment_count').val(inst);

                calculateTotal();
            }
        });

        function calculateTotal() {
            let saleP = parseFloat($('#sale_price').val()) || 0;
            let downA = parseFloat($('#down_payment_amount').val()) || 0;
            let instC = parseInt($('#installment_count').val()) || 1;

            let remaining = saleP - downA;
            let perInst = instC > 0 ? remaining / instC : 0;

            $('#installment_amount').val(perInst.toFixed(2));
            $('#total_payable_display').text(saleP.toLocaleString());
        }

        $('#sale_price, #down_payment_amount, #installment_count').on('input', calculateTotal);

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
