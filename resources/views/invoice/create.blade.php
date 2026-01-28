@extends('layouts.admin')
@section('page-title')
    {{ __('Invoice Create') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item">{{ __('Invoice Create') }}</li>
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function() {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    if ($('.select2').length) {
                        $('.select2').select2();
                    }

                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        }
                        $('.subTotal').html(subTotal.toFixed(2));
                        $('.totalAmount').html(subTotal.toFixed(2));
                    }
                },
                ready: function(setIndexes) {

                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }

        }

        $(document).on('change', '#customer', function() {
            $('#customer_detail').removeClass('d-none');
            $('#customer_detail').addClass('d-block');
            $('#customer-box').removeClass('d-block');
            $('#customer-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function(data) {
                    if (data != '') {
                        $('#customer_detail').html(data);
                    } else {
                        $('#customer-box').removeClass('d-none');
                        $('#customer-box').addClass('d-block');
                        $('#customer_detail').removeClass('d-block');
                        $('#customer_detail').addClass('d-none');
                    }

                },

            });

            // Fetch customer contracts
            if (id) {
                $.ajax({
                    url: '{{ route('invoice.customer.contracts') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('#token').val()
                    },
                    data: {
                        'customer_id': id
                    },
                    success: function(contracts) {
                        var options = '<option value="">{{ __('-- Select Contract --') }}</option>';
                        if (contracts.length > 0) {
                            contracts.forEach(function(contract) {
                                options += '<option value="' + contract.id + '" ' +
                                    'data-value="' + contract.value + '" ' +
                                    'data-type="' + contract.payment_plan_type + '" ' +
                                    'data-product="' + (contract.product_id || '') + '" ' +
                                    'data-installments=\'' + JSON.stringify(contract
                                        .installments) + '\' ' +
                                    'data-pending=\'' + JSON.stringify(contract
                                        .first_pending_installment || null) + '\'>' +
                                    contract.subject +
                                    ' - {{ \Auth::user()->currencySymbol() }}' + parseFloat(
                                        contract.value).toLocaleString() +
                                    '</option>';
                            });
                            $('#contract-box').removeClass('d-none');
                        } else {
                            $('#contract-box').addClass('d-none');
                        }
                        $('#contract_id').html(options);
                    }
                });
            } else {
                $('#contract-box').addClass('d-none');
                $('#contract_id').html('<option value="">{{ __('-- Select Contract --') }}</option>');
            }
        });

        // Function to reset table fields to default values
        function resetTableFields() {
            // Remove all rows except the first one
            $('[data-repeater-item]:not(:first)').remove();

            // Reset first row fields
            var firstRow = $('[data-repeater-item]').first();
            firstRow.find('.item').val('').trigger('change.select2');
            firstRow.find('.quantity').val('');
            firstRow.find('.price').val('');
            firstRow.find('.discount').val('');
            firstRow.find('.pro_description').val('');
            firstRow.find('.amount').html('0.00');
            firstRow.find('.tax').val('');
            firstRow.find('.taxes').html('');
            firstRow.find('.itemTaxPrice').val('');
            firstRow.find('.itemTaxRate').val('');

            // Reset totals
            $('.subTotal').html('0.00');
            $('.totalTax').html('0.00');
            $('.totalDiscount').html('0.00');
            $('.totalAmount').html('0.00');

            // Remove installment_id hidden field if exists
            $('input[name="installment_id"]').remove();
        }

        // Handle contract selection to auto-fill invoice items
        $(document).on('change', '#contract_id', function(e) {
            // Only skip if this page was loaded with a pre-filled installment AND this isn't a user change
            // Check if original page load had installment
            var isPrefilledPage = {{ isset($installment) && $installment ? 'true' : 'false' }};
            var isFirstLoad = !$(this).data('user-changed');

            if (isPrefilledPage && isFirstLoad) {
                $(this).data('user-changed', true);
                return;
            }
            $(this).data('user-changed', true);

            var opt = $(this).find(':selected');
            var contractValue = parseFloat(opt.data('value')) || 0;
            var planType = opt.data('type');
            var productId = opt.data('product');
            var pendingInstallment = opt.data('pending');

            // Reset table when contract changes
            resetTableFields();

            // If no contract selected, just return after reset
            if (!opt.val()) {
                return;
            }

            var firstRow = $('[data-repeater-item]').first();

            // Auto-select the unit's product if available
            if (productId) {
                firstRow.find('.item').val(productId).trigger('change.select2');
            }

            // If there's a pending installment, use its data
            if (pendingInstallment && pendingInstallment.id) {
                // Set dates
                $('input[name="issue_date"]').val(pendingInstallment.issue_date);
                $('input[name="due_date"]').val(pendingInstallment.due_date);

                // Set amount and description
                firstRow.find('.price').val(pendingInstallment.amount);
                firstRow.find('.quantity').val(1);
                firstRow.find('.pro_description').val(pendingInstallment.description);

                // Add hidden field for installment_id
                $('input[name="installment_id"]').remove(); // Remove any existing
                $('form').append('<input type="hidden" name="installment_id" value="' + pendingInstallment.id +
                    '">');

                // Trigger calculations
                firstRow.find('.quantity').trigger('keyup');

                // Update amount display
                firstRow.find('.amount').html(parseFloat(pendingInstallment.amount).toFixed(2));
                $('.subTotal').html(parseFloat(pendingInstallment.amount).toFixed(2));
                $('.totalAmount').html(parseFloat(pendingInstallment.amount).toFixed(2));
            } else if (contractValue > 0) {
                // Full payment mode
                firstRow.find('.price').val(contractValue);
                firstRow.find('.quantity').val(1);
                firstRow.find('.pro_description').val('{{ __('Contract Full Payment') }}');
                firstRow.find('.quantity').trigger('keyup');

                // Update amount display
                firstRow.find('.amount').html(contractValue.toFixed(2));
                $('.subTotal').html(contractValue.toFixed(2));
                $('.totalAmount').html(contractValue.toFixed(2));
            }
        });

        $(document).on('click', '#remove', function() {
            $('#customer-box').removeClass('d-none');
            $('#customer-box').addClass('d-block');
            $('#customer_detail').removeClass('d-block');
            $('#customer_detail').addClass('d-none');
            $('#contract-box').addClass('d-none');

            // Reset contract dropdown
            $('#contract_id').html('<option value="">{{ __('-- Select Contract --') }}</option>');

            // Reset table fields when customer is removed
            resetTableFields();
        })

        $(document).on('change', '.item', function() {

            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);

            // Skip product AJAX if this is an installment-based invoice (don't override amount)
            if ($('input[name="installment_id"]').length && $('input[name="installment_id"]').val()) {
                return;
            }

            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id
                },
                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);
                    console.log(el.parent().parent().find('.quantity'))
                    $(el.parent().parent().find('.quantity')).val(1);
                    $(el.parent().parent().find('.price')).val(item.product.sale_price);
                    $(el.parent().parent().parent().find('.pro_description')).val(item.product
                        .description);
                    // $('.pro_description').text(item.product.description);

                    var taxes = '';
                    var tax = [];

                    var totalItemTaxRate = 0;

                    if (item.taxes == 0) {
                        taxes += '-';
                    } else {
                        for (var i = 0; i < item.taxes.length; i++) {
                            taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name +
                                ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                            tax.push(item.taxes[i].id);
                            totalItemTaxRate += parseFloat(item.taxes[i].rate);
                        }
                    }
                    var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product
                        .sale_price * 1));
                    $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                    $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                    $(el.parent().parent().find('.taxes')).html(taxes);
                    $(el.parent().parent().find('.tax')).val(tax);
                    $(el.parent().parent().find('.unit')).html(item.unit);
                    $(el.parent().parent().find('.discount')).val(0);

                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }

                    var totalItemPrice = 0;
                    var priceInput = $('.price');
                    for (var j = 0; j < priceInput.length; j++) {
                        totalItemPrice += parseFloat(priceInput[j].value);
                    }

                    var totalItemTaxPrice = 0;
                    var itemTaxPriceInput = $('.itemTaxPrice');
                    for (var j = 0; j < itemTaxPriceInput.length; j++) {
                        totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                        $(el.parent().parent().find('.amount')).html(parseFloat(item.totalAmount) +
                            parseFloat(itemTaxPriceInput[j].value));
                    }

                    var totalItemDiscountPrice = 0;
                    var itemDiscountPriceInput = $('.discount');

                    for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                        totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                    }

                    $('.subTotal').html(totalItemPrice.toFixed(2));
                    $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                    $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(
                        totalItemDiscountPrice) + parseFloat(totalItemTaxPrice)).toFixed(2));


                },
            });
        });

        $(document).on('keyup', '.quantity', function() {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();

            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();
            if (discount.length <= 0) {
                discount = 0;
            }

            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

        })

        $(document).on('keyup change', '.price', function() {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();

            var discount = $(el.find('.discount')).val();
            if (discount.length <= 0) {
                discount = 0;
            }
            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));


        })

        $(document).on('keyup change', '.discount', function() {
            var el = $(this).parent().parent().parent();
            var discount = $(this).val();
            if (discount.length <= 0) {
                discount = 0;
            }

            var price = $(el.find('.price')).val();
            var quantity = $(el.find('.quantity')).val();
            var totalItemPrice = (quantity * price) - discount;


            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }


            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');

            for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }


            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));




        })

        var customerId = '{{ $customerId }}';
        if (customerId > 0) {
            $('#customer').val(customerId).change();
        }

        // Pre-fill from installment if provided
        @if (isset($installment) && $installment)
            $(document).ready(function() {
                // Set dates immediately
                $('input[name="issue_date"]').val('{{ $installment->issue_date->format('Y-m-d') }}');
                $('input[name="due_date"]').val('{{ $installment->due_date->format('Y-m-d') }}');

                setTimeout(function() {
                    var firstRow = $('[data-repeater-item]').first();

                    @if ($installment->unit && $installment->unit->product_id)
                        firstRow.find('.item').val('{{ $installment->unit->product_id }}');
                    @endif

                    firstRow.find('.price').val('{{ $installment->amount }}');
                    firstRow.find('.quantity').val(1);
                    firstRow.find('.pro_description').val(
                        '{{ $installment->description ?? ($installment->installment_type == 'down_payment' ? 'Down Payment' : 'Installment Payment') }}'
                    );

                    // Trigger calculations manually without triggering .item change
                    firstRow.find('.quantity').trigger('keyup');

                    // Update totals explicitly
                    $('.subTotal').html('{{ number_format($installment->amount, 2) }}');
                    $('.totalAmount').html('{{ number_format($installment->amount, 2) }}');
                    firstRow.find('.amount').html('{{ number_format($installment->amount, 2) }}');
                }, 500);
            });
        @endif
    </script>
    <script>
        $(document).on('click', '[data-repeater-delete]', function() {
            $(".price").change();
            $(".discount").change();
        });
    </script>
@endpush
@section('content')
    <div class="row">
        {{ Form::open(['url' => 'invoice', 'class' => 'w-100']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            @if (isset($installment) && $installment)
                <input type="hidden" name="installment_id" value="{{ $installment->id }}">
            @endif
            @if (isset($contract) && $contract)
                <input type="hidden" name="contract_id" value="{{ $contract->id }}">
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group {{ isset($prefillCustomer) && $prefillCustomer ? 'd-none' : '' }}"
                                id="customer-box">
                                {{ Form::label('customer_id', __('Customer'), ['class' => 'form-label']) }}
                                {{ Form::select('customer_id', $customers, $customerId, ['class' => 'form-control select', 'id' => 'customer', 'data-url' => route('invoice.customer'), 'required' => 'required']) }}

                            </div>

                            <div id="customer_detail"
                                class="{{ isset($prefillCustomer) && $prefillCustomer ? 'd-block' : 'd-none' }}">
                                @if (isset($prefillCustomer) && $prefillCustomer)
                                    @include('invoice.customer_detail', ['customer' => $prefillCustomer])
                                @endif
                            </div>

                            <div class="form-group d-none" id="contract-box">
                                {{ Form::label('contract_id', __('Contract (Optional)'), ['class' => 'form-label']) }}
                                <select class="form-control select" id="contract_id" name="contract_id">
                                    <option value="">{{ __('-- Select Contract --') }}</option>
                                </select>
                                <small
                                    class="text-muted">{{ __('Select a contract to auto-fill installment details') }}</small>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            {{ Form::date('issue_date', null, ['class' => 'form-control', 'required' => 'required']) }}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            {{ Form::date('due_date', null, ['class' => 'form-control', 'required' => 'required']) }}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            <input type="text" class="form-control" value="{{ $invoice_number }}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}
                                        {{ Form::select('category_id', $category, null, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            <span><i class="ti ti-joint"></i></span>
                                            {{ Form::text('ref_number', '', ['class' => 'form-control', 'placeholder' => __('Enter Ref NUmber')]) }}
                                        </div>
                                    </div>
                                </div>
                                {{--                                <div class="col-md-6"> --}}
                                {{--                                    <div class="form-check custom-checkbox mt-4"> --}}
                                {{--                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply"> --}}
                                {{--                                        <label class="form-check-label " for="discount_apply">{{__('Discount Apply')}}</label> --}}
                                {{--                                    </div> --}}
                                {{--                                </div> --}}
                                {{--                                <div class="col-md-6"> --}}
                                {{--                                    <div class="form-group"> --}}
                                {{--                                        {{Form::label('sku',__('SKU')) }} --}}
                                {{--                                        {!!Form::text('sku', null,array('class' => 'form-control','required'=>'required')) !!} --}}
                                {{--                                    </div> --}}
                                {{--                                </div> --}}
                                @if (!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{ __('Product & Services') }}</h5>
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal"
                                    data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }} </th>
                                    <th>{{ __('Discount') }}</th>
                                    <th class="d-none">{{ __('Tax') }} (%)</th>
                                    <th class="text-end">{{ __('Amount') }} <br><small
                                            class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>

                                    <td width="25%" class="form-group pt-0">
                                        {{ Form::select('item', $product_services, '', ['class' => 'form-control select2 item', 'data-url' => route('invoice.product'), 'required' => 'required']) }}
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', '', ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'required' => 'required']) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>


                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', '', ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price'), 'required' => 'required']) }}
                                            <span
                                                class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('discount', '', ['class' => 'form-control discount', 'required' => 'required', 'placeholder' => __('Discount')]) }}
                                            <span
                                                class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>



                                    <td class="d-none">
                                        <div class="form-group">
                                            <div class="input-group colorpickerinput">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax text-dark']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-end amount">0.00</td>
                                    <td>
                                        <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2"
                                            data-repeater-delete></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="form-group">
                                            {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'rows' => '2', 'placeholder' => __('Description')]) }}
                                        </div>
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="d-none">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }}
                                            ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount blue-text">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('invoice.index') }}';"
                class="btn btn-light">
            <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection
