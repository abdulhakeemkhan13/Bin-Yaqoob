{{ Form::open(['route' => ['deals.convert.contract.store', $deal->id], 'method' => 'post', 'data-ajax' => 'true', 'id' => 'convert-contract-form']) }}
<div class="modal-body">
    <div class="alert alert-info mb-3">
        <div class="row">
            <div class="col-md-6">
                <strong>{{ __('Converting Deal:') }}</strong> {{ $deal->name }}<br>
                @if ($deal->email)
                    <small>{{ __('Email:') }} {{ $deal->email }}</small><br>
                @endif
                @if ($deal->phone)
                    <small>{{ __('Phone:') }} {{ $deal->phone }}</small>
                @endif
            </div>
            <div class="col-md-6">
                @if ($deal->project)
                    <strong>{{ __('Project:') }}</strong> {{ $deal->project->name }}<br>
                @endif
                @if ($deal->floor)
                    <strong>{{ __('Floor:') }}</strong> {{ $deal->floor->floor_name }}<br>
                @endif
                @if ($deal->unit)
                    <strong>{{ __('Unit:') }}</strong> {{ $deal->unit->unit_number }}
                    ({{ \Auth::user()->priceFormat($deal->unit->price) }})<br>
                @endif
            </div>
        </div>
    </div>
    {{-- start for ai module --}}
    @php
        $plan = \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if ($plan->chatgpt == 1)
        <div class="text-end">
            <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['contract']) }}" data-bs-placement="top"
                data-title="{{ __('Generate content with AI') }}">
                <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
            </a>
        </div>
    @endif
    {{-- end for ai module --}}
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', $prefill['subject'] ?? '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('client_name', __('Client'), ['class' => 'form-label']) }}
            {{ Form::select('client_name', $clients, $prefill['client_id'] ?? null, ['class' => 'form-control select client_select', 'id' => 'client_select']) }}
            @if ($deal->email && empty($prefill['client_id']))
                <small class="text-muted">{{ __('Leave empty to auto-create client from deal email') }}</small>
            @endif
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('projects', __('Projects'), ['class' => 'form-label']) }}
            <select class="form-control select project_select" id="project_id" name="project_id">
                <option value="">{{ __('Select Project') }}</option>
            </select>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('type', __('Contract Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $contractTypes, null, ['class' => 'form-control', 'data-toggle="select"', 'required' => 'required']) }}
            @if (count($contractTypes) <= 0 && Auth::user()->type == 'company')
                <div class="text-muted text-xs">
                    {{ __('Please create contract type') }} <a
                        href="{{ route('contractType.index') }}">{{ __('here') }}</a>.
                </div>
            @endif
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('value', __('Contract Value'), ['class' => 'form-label']) }}
            {{ Form::number('value', $prefill['value'] ?? '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
            {{ Form::date('start_date', $prefill['start_date'] ?? '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
            {{ Form::date('end_date', $prefill['end_date'] ?? '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        @if (!$customFields->isEmpty())
            <div class="col-6 form-group">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {!! Form::textarea('description', $prefill['description'] ?? null, ['class' => 'form-control', 'rows' => '2']) !!}
        </div>
    </div>

    {{-- Installment Plan Section --}}
    <div class="row mt-3">
        <div class="col-12">
            <hr>
            <h6>{{ __('Installment Plan') }}</h6>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('payment_plan_id', __('Select Payment Plan'), ['class' => 'form-label']) }}
            <select class="form-control" id="payment_plan_id" name="payment_plan_id">
                <option value="">{{ __('-- Select Payment Plan --') }}</option>
                @if ($deal->project)
                    @foreach ($deal->project->paymentPlans()->where('is_active', true)->get() as $plan)
                        <option value="{{ $plan->id }}" data-down-percent="{{ $plan->down_payment_percentage }}"
                            data-installments="{{ $plan->num_installments }}" data-frequency="{{ $plan->frequency }}">
                            {{ $plan->plan_name }} ({{ $plan->num_installments }} {{ $plan->frequency }} -
                            {{ $plan->down_payment_percentage }}% Down)
                        </option>
                    @endforeach
                @endif
            </select>
            @if (!$deal->project)
                <small class="text-muted">{{ __('No project selected on deal') }}</small>
            @endif
        </div>

        <div class="form-group col-md-4">
            {{ Form::label('installments', __('Number of Installments'), ['class' => 'form-label']) }}
            {{ Form::number('installments', 12, ['class' => 'form-control', 'id' => 'installments', 'min' => 1]) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('down_payment_percent', __('Down Payment %'), ['class' => 'form-label']) }}
            {{ Form::number('down_payment_percent', 20, ['class' => 'form-control', 'id' => 'down_payment_percent', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('down_payment', __('Down Payment Amount'), ['class' => 'form-label']) }}
            {{ Form::number('down_payment', '', ['class' => 'form-control', 'id' => 'down_payment', 'step' => '0.01', 'readonly' => 'readonly']) }}
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-secondary">
                <div class="row text-center">
                    <div class="col-md-4">
                        <strong>{{ __('Contract Value') }}</strong><br>
                        <span id="display_total" class="h5">0.00</span>
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('Remaining After Down Payment') }}</strong><br>
                        <span id="display_remaining" class="h5">0.00</span>
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('Per Installment') }}</strong><br>
                        <span id="display_per_installment" class="h5 text-success">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Convert to Contract') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>

<script type="text/javascript">
    $(".client_select").change(function() {

        var client_id = $(this).val();
        getparent(client_id);
    });

    // Trigger on load if client is pre-selected
    @if (!empty($prefill['client_id']))
        $(document).ready(function() {
            getparent({{ $prefill['client_id'] }});
        });
    @endif

    function getparent(bid) {

        $.ajax({
            url: `{{ url('contract/clients/select') }}/${bid}`,
            type: 'GET',
            success: function(data) {
                console.log(data);
                $("#project_id").html('');
                $('#project_id').append(
                    '<select class="form-control" id="project_id" name="project_id[]"  ></select>');
                $.each(data, function(i, item) {
                    console.log(item);
                    $('#project_id').append('<option value="' + item.id + '">' + item.name +
                        '</option>');
                });

                if (data == '') {
                    $('#project_id').empty();
                }
            }
        });
    }

    // Installment calculation
    function calculateInstallments() {
        var totalValue = parseFloat($('#value').val()) || 0;
        var downPaymentPercent = parseFloat($('#down_payment_percent').val()) || 0;
        var numInstallments = parseInt($('#installments').val()) || 1;

        var downPayment = (totalValue * downPaymentPercent) / 100;
        var remaining = totalValue - downPayment;
        var perInstallment = numInstallments > 0 ? remaining / numInstallments : 0;

        $('#down_payment').val(downPayment.toFixed(2));
        $('#display_total').text(totalValue.toLocaleString('en-US', {
            minimumFractionDigits: 2
        }));
        $('#display_remaining').text(remaining.toLocaleString('en-US', {
            minimumFractionDigits: 2
        }));
        $('#display_per_installment').text(perInstallment.toLocaleString('en-US', {
            minimumFractionDigits: 2
        }));
    }

    // Bind events
    $(document).ready(function() {
        // Initial calculation
        calculateInstallments();

        // Recalculate on value changes
        $('#value, #down_payment_percent, #installments').on('input change', function() {
            calculateInstallments();
        });

        // When payment plan is selected, populate fields
        $('#payment_plan_id').on('change', function() {
            var selected = $(this).find(':selected');
            if (selected.val()) {
                var downPercent = selected.data('down-percent') || 0;
                var installments = selected.data('installments') || 12;
                var frequency = selected.data('frequency') || 'Monthly';

                $('#down_payment_percent').val(downPercent);
                $('#installments').val(installments);
                $('#frequency').val(frequency);

                calculateInstallments();
            }
        });
    });
</script>
