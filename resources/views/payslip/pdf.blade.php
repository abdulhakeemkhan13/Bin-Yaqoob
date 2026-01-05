@php
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $company_logo = \App\Models\Utility::GetLogo();

    // ------- Helpers -------
    $basic = (float) ($payslip->basic_salary ?? 0);

    // Decode earnings containers from $payslipDetail
    $earningAllowance = $payslipDetail['earning']['allowance'] ?? [];
    $earningCommission = $payslipDetail['earning']['commission'] ?? [];
    $earningOtherPay = $payslipDetail['earning']['otherPayment'] ?? [];
    $earningOverTime = $payslipDetail['earning']['overTime'] ?? [];

    // Decode deduction containers from $payslipDetail
    $dedLoanContainers = $payslipDetail['deduction']['loan'] ?? [];
    $dedSaturationContainers = $payslipDetail['deduction']['deduction'] ?? [];

    // TAX + EOBI (prefer $payslipDetail if you add them there; otherwise fallback to payslip JSON)
    $dedTaxContainers = $payslipDetail['deduction']['salarytax'] ?? [];
    $fallbackTaxRows = json_decode($payslip->tax ?? '[]');
    $dedEobiContainers = $payslipDetail['deduction']['eobi'] ?? [];
    $fallbackEobiRows = json_decode($payslip->eobi ?? '[]');

    // NEW: Other Deduction (per-slip only)
    $dedOtherDeductionContainers = $payslipDetail['deduction']['other_deduction'] ?? []; // if you ever add to detail
    $fallbackOtherDeductionRows = json_decode($payslip->other_deduction ?? '[]', true); // stored on pay_slips

    // ------- Amount calculators -------
    $asAmount = function ($row) use ($basic) {
        // row has ->type and ->amount. If percentage, calc on basic.
        $type = strtolower($row->type ?? '');
        $amount = (float) ($row->amount ?? 0);
        return $type === 'percentage' ? ($amount * $basic) / 100 : $amount;
    };

    $sumAllowance = 0;
    foreach ($earningAllowance as $container) {
        foreach (json_decode($container->allowance ?? '[]') as $a) {
            $sumAllowance += $asAmount($a);
        }
    }

    $sumCommission = 0;
    foreach ($earningCommission as $container) {
        foreach (json_decode($container->commission ?? '[]') as $c) {
            $sumCommission += $asAmount($c);
        }
    }

    $sumOtherPayment = 0;
    foreach ($earningOtherPay as $container) {
        foreach (json_decode($container->other_payment ?? '[]') as $op) {
            $sumOtherPayment += $asAmount($op);
        }
    }

    $sumOvertime = 0;
    foreach ($earningOverTime as $container) {
        foreach (json_decode($container->overtime ?? '[]') as $ot) {
            $days = (float) ($ot->number_of_days ?? 0);
            $hours = (float) ($ot->hours ?? 0);
            $rate = (float) ($ot->rate ?? 0);
            $sumOvertime += $days * $hours * $rate;
        }
    }

    // Deductions (Loan + Saturation)
    $sumLoan = 0;
    foreach ($dedLoanContainers as $container) {
        foreach (json_decode($container->loan ?? '[]') as $l) {
            $sumLoan += $asAmount($l);
        }
    }

    $sumSaturation = 0;
    foreach ($dedSaturationContainers as $container) {
        foreach (json_decode($container->saturation_deduction ?? '[]') as $d) {
            $sumSaturation += $asAmount($d);
        }
    }

    // Tax
    $sumTax = 0;
    if (!empty($dedTaxContainers)) {
        foreach ($dedTaxContainers as $container) {
            foreach (json_decode($container->tax ?? '[]') as $t) {
                $sumTax += (float) ($t->amount ?? 0);
            }
        }
    } else {
        foreach ($fallbackTaxRows as $t) {
            $sumTax += (float) ($t->amount ?? 0);
        }
    }

    // EOBI
    $sumEobi = 0;
    if (!empty($dedEobiContainers)) {
        foreach ($dedEobiContainers as $container) {
            foreach (json_decode($container->eobi ?? '[]') as $e) {
                $sumEobi += (float) ($e->amount ?? 0);
            }
        }
    } else {
        foreach ($fallbackEobiRows as $e) {
            $sumEobi += (float) ($e->amount ?? 0);
        }
    }

    // NEW: Other Deduction (sum)
    $sumOtherDeduction = 0;
    if (!empty($dedOtherDeductionContainers)) {
        // if you ever include it in $payslipDetail
        foreach ($dedOtherDeductionContainers as $container) {
            foreach ((array) ($container['rows'] ?? []) as $od) {
                $sumOtherDeduction += (float) ($od['amount'] ?? 0);
            }
        }
    } else {
        foreach ((array) $fallbackOtherDeductionRows as $od) {
            $sumOtherDeduction += (float) ($od['amount'] ?? 0);
        }
    }

    // ------- Totals (Leaves intentionally excluded) -------
    $totalEarning = $basic + $sumAllowance + $sumCommission + $sumOtherPayment + $sumOvertime;
    $totalDeduction = $sumLoan + $sumSaturation + $sumTax + $sumEobi + $sumOtherDeduction; // NEW include per-slip other deduction
    $netSalaryView = $totalEarning - $totalDeduction; // matches column logic; may differ if backend rounds differently
@endphp

<div class="card bg-none card-box">
    <div class="card-body">

        <div class="text-end">
            <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"><span class="ti ti-download"></span></a>
            <a title="Mail Send" href="{{ route('payslip.send', [$employee->id, $payslip->salary_month]) }}"
                class="btn btn-sm btn-warning">
                <span class="ti ti-send"></span>
            </a>
        </div>

        <div class="invoice" id="printableArea">
            <div class="invoice-number">
                <img src="{{ $logo . '/' . (!empty($company_logo) ? $company_logo : 'logo-dark.png') }}" width="120px;">
            </div>

            <div class="invoice-print">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="invoice-title"></div>
                        <hr>
                        <div class="row text-sm">
                            <div class="col-md-6">
                                <address>
                                    <strong>{{ __('Name') }} :</strong> {{ $employee->name }}<br>
                                    <strong>{{ __('Position') }} :</strong> {{ __('Employee') }}<br>
                                    <strong>{{ __('Salary Date') }}
                                        :</strong>{{ \Auth::user()->dateFormat($payslip->created_at) }}<br>
                                    <strong>{{ __('Last Increment Date') }}
                                        :</strong>{{ $lastIncrementDate ? \Auth::user()->dateFormat($lastIncrementDate) : '' }}<br>
                                    @php
                                        $L = is_array($leaves) ? $leaves : (array) $leaves;
                                        $workingDays = (int) ($L['working_days'] ?? 0);
                                    @endphp
                                    <strong>{{ __('Total Working Days') }} :</strong>
                                    {{ $workingDays }}<br>
                                </address>
                            </div>
                            <div class="col-md-6 text-end">
                                @php
                                    $cName = trim((string) \Utility::getValByName('company_name'));
                                    $cAddr = trim((string) \Utility::getValByName('company_address'));
                                    $cCity = trim((string) \Utility::getValByName('company_city'));
                                    $cState = trim((string) \Utility::getValByName('company_state'));
                                    $cZip = trim((string) \Utility::getValByName('company_zipcode'));
                                    $slipMon = trim((string) ($payslip->salary_month ?? ''));
                                @endphp

                                <address>
                                    @if ($cName !== '')
                                        <strong>{{ $cName }}</strong><br>
                                    @endif

                                    @if ($cAddr !== '' || $cCity !== '')
                                        {{ $cAddr }}
                                        @if ($cAddr !== '' && $cCity !== '')
                                            ,
                                        @endif
                                        {{ $cCity }}
                                        <br>
                                    @endif

                                    @if ($cState !== '' || $cZip !== '')
                                        {{ $cState }}
                                        @if ($cState !== '' && $cZip !== '')
                                            -
                                        @endif
                                        {{ $cZip }}
                                        <br>
                                    @endif

                                    @if ($slipMon !== '')
                                        <strong>{{ __('Salary Slip') }} :</strong> {{ $slipMon }}<br>
                                    @endif
                                </address>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EARNING TABLE --}}
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Earning') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Basic Salary') }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($basic) }}</td>
                                        </tr>

                                        {{-- Allowance --}}
                                        @foreach ($earningAllowance as $allowanceContainer)
                                            @php $rows = json_decode($allowanceContainer->allowance ?? '[]'); @endphp
                                            @foreach ($rows as $row)
                                                <tr>
                                                    <td>{{ __('Allowance') }}</td>
                                                    <td>{{ $row->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($row->type ?? '-') }}</td>
                                                    @php $display = $asAmount($row); @endphp
                                                    @if (strtolower($row->type ?? '') === 'percentage')
                                                        <td class="text-end">
                                                            {{ $row->amount ?? 0 }}%
                                                            ({{ \Auth::user()->priceFormat($display) }})
                                                        </td>
                                                    @else
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($display) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        {{-- Commission --}}
                                        @foreach ($earningCommission as $container)
                                            @php $rows = json_decode($container->commission ?? '[]'); @endphp
                                            @foreach ($rows as $row)
                                                <tr>
                                                    <td>{{ __('Commission') }}</td>
                                                    <td>{{ $row->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($row->type ?? '-') }}</td>
                                                    @php $display = $asAmount($row); @endphp
                                                    @if (strtolower($row->type ?? '') === 'percentage')
                                                        <td class="text-end">
                                                            {{ $row->amount ?? 0 }}%
                                                            ({{ \Auth::user()->priceFormat($display) }})
                                                        </td>
                                                    @else
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($display) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        {{-- Other Payment --}}
                                        @foreach ($earningOtherPay as $container)
                                            @php $rows = json_decode($container->other_payment ?? '[]'); @endphp
                                            @foreach ($rows as $row)
                                                <tr>
                                                    <td>{{ __('Other Payment') }}</td>
                                                    <td>{{ $row->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($row->type ?? '-') }}</td>
                                                    @php $display = $asAmount($row); @endphp
                                                    @if (strtolower($row->type ?? '') === 'percentage')
                                                        <td class="text-end">
                                                            {{ $row->amount ?? 0 }}%
                                                            ({{ \Auth::user()->priceFormat($display) }})
                                                        </td>
                                                    @else
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($display) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        {{-- Overtime --}}
                                        @foreach ($earningOverTime as $container)
                                            @php $rows = json_decode($container->overtime ?? '[]'); @endphp
                                            @foreach ($rows as $row)
                                                @php
                                                    $days = (float) ($row->number_of_days ?? 0);
                                                    $hours = (float) ($row->hours ?? 0);
                                                    $rate = (float) ($row->rate ?? 0);
                                                    $amt = $days * $hours * $rate;
                                                @endphp
                                                <tr>
                                                    <td>{{ __('OverTime') }}</td>
                                                    <td>{{ $row->title ?? '-' }}</td>
                                                    <td>-</td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($amt) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- DEDUCTION TABLE (Tax + EOBI + Other Deduction included) --}}
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Deduction') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                        </tr>

                                        {{-- TAX --}}
                                        @if (!empty($dedTaxContainers))
                                            @foreach ($dedTaxContainers as $container)
                                                @foreach (json_decode($container->tax ?? '[]') as $t)
                                                    <tr>
                                                        <td>{{ __('Tax') }}</td>
                                                        <td>{{ $t->title ?? '-' }}</td>
                                                        <td>-</td>
                                                        <td class="text-end">
                                                            {{ \Auth::user()->priceFormat($t->amount ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @foreach ($fallbackTaxRows as $t)
                                                <tr>
                                                    <td>{{ __('Tax') }}</td>
                                                    <td>{{ $t->title ?? '-' }}</td>
                                                    <td>-</td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($t->amount ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        {{-- EOBI --}}
                                        @if (!empty($dedEobiContainers))
                                            @foreach ($dedEobiContainers as $container)
                                                @foreach (json_decode($container->eobi ?? '[]') as $e)
                                                    <tr>
                                                        <td>{{ __('EOBI') }}</td>
                                                        <td>{{ $e->title ?? '-' }}</td>
                                                        <td>-</td>
                                                        <td class="text-end">
                                                            {{ \Auth::user()->priceFormat($e->amount ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @foreach ($fallbackEobiRows as $e)
                                                <tr>
                                                    <td>{{ __('EOBI') }}</td>
                                                    <td>{{ $e->title ?? '-' }}</td>
                                                    <td>-</td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($e->amount ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        {{-- NEW: Other Deduction (per-slip only) --}}
                                        @if (!empty($dedOtherDeductionContainers))
                                            @foreach ($dedOtherDeductionContainers as $container)
                                                @foreach ((array) ($container['rows'] ?? []) as $od)
                                                    <tr>
                                                        <td>{{ __('Other Deduction') }}</td>
                                                        <td>{{ $od['title'] ?? '-' }}</td>
                                                        <td>-</td>
                                                        <td class="text-end">
                                                            {{ \Auth::user()->priceFormat($od['amount'] ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            @foreach ((array) $fallbackOtherDeductionRows as $od)
                                                <tr>
                                                    <td>{{ __('Other Deduction') }}</td>
                                                    <td>{{ $od['title'] ?? '-' }}</td>
                                                    <td>-</td>
                                                    <td class="text-end">
                                                        {{ \Auth::user()->priceFormat($od['amount'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        {{-- Loan --}}
                                        @foreach ($dedLoanContainers as $container)
                                            @foreach (json_decode($container->loan ?? '[]') as $l)
                                                @php $display = $asAmount($l); @endphp
                                                <tr>
                                                    <td>{{ __('Loan') }}</td>
                                                    <td>{{ $l->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($l->type ?? '-') }}</td>
                                                    @if (strtolower($l->type ?? '') === 'percentage')
                                                        <td class="text-end">
                                                            {{ $l->amount ?? 0 }}%
                                                            ({{ \Auth::user()->priceFormat($display) }})
                                                        </td>
                                                    @else
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($display) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        {{-- Saturation Deduction --}}
                                        @foreach ($dedSaturationContainers as $container)
                                            @foreach (json_decode($container->saturation_deduction ?? '[]') as $d)
                                                @php $display = $asAmount($d); @endphp
                                                <tr>
                                                    <td>{{ __('Saturation Deduction') }}</td>
                                                    <td>{{ $d->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($d->type ?? '-') }}</td>
                                                    @if (strtolower($d->type ?? '') === 'percentage')
                                                        <td class="text-end">
                                                            {{ $d->amount ?? 0 }}%
                                                            ({{ \Auth::user()->priceFormat($display) }})
                                                        </td>
                                                    @else
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($display) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ATTENDANCE SUMMARY (header row + single values row) --}}
                        @php
                            $L = is_array($leaves) ? $leaves : (array) $leaves;

                            $paid = (int) ($L['paid'] ?? 0);
                            $unpaid = (int) ($L['unpaid'] ?? 0);
                            $late = (float) ($L['late'] ?? 0);
                            $workingDays = (int) ($L['working_days'] ?? 0);
                            $leaveDays = (int) ($L['leave_days'] ?? 0);

                            $fmtLate = fmod($late, 1) === 0.0 ? number_format($late, 0) : number_format($late, 2);
                        @endphp

                        <div class="card-body table-border-style mt-2">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <thead>
                                        <tr class="font-weight-bold">
                                            <th class="text-center">{{ __('Paid Leaves') }}</th>
                                            <th class="text-center">{{ __('Unpaid Leaves') }}</th>
                                            <th class="text-center">{{ __('Late') }}</th>
                                            <th class="text-center">{{ __('Working Days') }}</th>
                                            <th class="text-center">{{ __('Leave Days') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">{{ $paid }}</td>
                                            <td class="text-center">{{ $unpaid }}</td>
                                            <td class="text-center">{{ $fmtLate }}</td>
                                            <td class="text-center">{{ $workingDays }}</td>
                                            <td class="text-center">{{ $leaveDays }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TOTALS (computed here; Leaves excluded) --}}
                        <div class="row mt-4">
                            <div class="col-lg-8"></div>
                            <div class="col-lg-4 text-end text-sm">
                                <div class="invoice-detail-item pb-2">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Earning') }}</div>
                                    <div class="invoice-detail-value">{{ \Auth::user()->priceFormat($totalEarning) }}
                                    </div>
                                </div>
                                <div class="invoice-detail-item">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Deduction') }}</div>
                                    <div class="invoice-detail-value">{{ \Auth::user()->priceFormat($totalDeduction) }}
                                    </div>
                                </div>
                                <hr class="mt-2 mb-2">
                                <div class="invoice-detail-item">
                                    <div class="invoice-detail-name font-bold">{{ __('Net Salary') }}</div>
                                    <div class="invoice-detail-value invoice-detail-value-lg">
                                        {{ \Auth::user()->priceFormat($netSalaryView) }}
                                    </div>
                                </div>
                                {{-- If you prefer to display the saved DB net_payble instead of recomputed: --}}
                                {{-- <div class="invoice-detail-value invoice-detail-value-lg">{{ \Auth::user()->priceFormat($payslip->net_payble) }}</div> --}}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <hr>
            <div class="text-md-right pb-2 text-sm">
                <div class="float-lg-left mb-lg-0 mb-3">
                    <p class="mt-2">{{ __('Employee Signature') }}</p>
                </div>
                <p class="mt-2 ">{{ __('Paid By') }}</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    var filename = document.getElementById('filename') ? document.getElementById('filename').value : 'payslip.pdf';

    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: filename,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A2'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
