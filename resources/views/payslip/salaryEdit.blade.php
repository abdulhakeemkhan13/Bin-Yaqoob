<div class="col-form-label">
    <div class="row px-3">
        <div class="col-md-4 mb-3">
            <h6 class="emp-title mb-0">{{ __('Employee') }}</h6>
            <h6 class="emp-title black-text">
                {{ !empty($payslip->employees) ? \Auth::user()->employeeIdFormat($payslip->employees->employee_id) : '' }}
            </h6>
        </div>
        <div class="col-md-4 mb-3">
            <h6 class="emp-title mb-0">{{ __('Basic Salary') }}</h6>
            <h6 class="emp-title black-text">{{ \Auth::user()->priceFormat($payslip->basic_salary) }}</h6>
        </div>
        <div class="col-md-4 mb-3">
            <h6 class="emp-title mb-0">{{ __('Payroll Month') }}</h6>
            <h6 class="emp-title black-text">{{ \Auth::user()->dateFormat($payslip->salary_month) }}</h6>
        </div>

        <div class="col-lg-12 our-system">
            {{ Form::open(['route' => ['payslip.updateemployee', $payslip->employee_id], 'method' => 'post']) }}
            {!! Form::hidden('payslip_id', $payslip->id) !!}

            @php
                // Decode JSON safely with defaults
                $allowances = json_decode($payslip->allowance ?: '[]');
                $commissions = json_decode($payslip->commission ?: '[]');
                $loans = json_decode($payslip->loan ?: '[]');
                $saturation_deductions = json_decode($payslip->saturation_deduction ?: '[]');
                $other_payments = json_decode($payslip->other_payment ?: '[]');
                $overtimes = json_decode($payslip->overtime ?: '[]');
                $salarytaxs = json_decode($payslip->tax ?: '[]');
                $eobis = json_decode($payslip->eobi ?: '[]');
                // NEW: per-slip adhoc deductions (affect this payslip only)
                $other_deductions = json_decode($payslip->other_deduction ?? '[]', true);
                // $leavesObj              = json_decode($payslip->leaves ?: '{"total_leave":0,"paid":0,"unpaid":0,"late":0}');
            @endphp

            <div class="row">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#allowance"
                            role="tab">{{ __('Allowance') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#commission"
                            role="tab">{{ __('Commission') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#loan" role="tab">{{ __('Loan') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#deduction"
                            role="tab">{{ __('Saturation Deduction') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#salarytax"
                            role="tab">{{ __('Salary Tax') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#eobi" role="tab">{{ __('EOBI') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#payment"
                            role="tab">{{ __('Other Payment') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#overtime"
                            role="tab">{{ __('Overtime') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#otherDeduction"
                            role="tab">{{ __('Other Deduction') }}</a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#leaves" role="tab">{{ __('Leaves') }}</a>
                    </li> --}}
                </ul>

                <div class="tab-content pt-4">

                    {{-- Allowance --}}
                    <div id="allowance" class="tab-pane fade show active">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($allowances as $allowance)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $allowance->title ?? __('Allowance'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('allowance[]', $allowance->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('allowance_id[]', $allowance->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No allowances found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Commission --}}
                    <div id="commission" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($commissions as $commission)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $commission->title ?? __('Commission'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('commission[]', $commission->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('commission_id[]', $commission->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No commissions found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Loan --}}
                    <div id="loan" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($loans as $loan)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $loan->title ?? __('Loan'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('loan[]', $loan->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('loan_id[]', $loan->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No loans found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Saturation Deduction --}}
                    <div id="deduction" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($saturation_deductions as $deduction)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $deduction->title ?? __('Deduction'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('saturation_deductions[]', $deduction->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('saturation_deductions_id[]', $deduction->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No deductions found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Salary Tax --}}
                    <div id="salarytax" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($salarytaxs as $tax)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $tax->title ?? __('Salary Tax'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('salarytax[]', $tax->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('salarytax_id[]', $tax->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No salary tax rows found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- EOBI --}}
                    <div id="eobi" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($eobis as $eobi)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $eobi->title ?? __('EOBI'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('eobi[]', $eobi->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('eobi_id[]', $eobi->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No EOBI rows found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Other Payment --}}
                    <div id="payment" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($other_payments as $payment)
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('title', $payment->title ?? __('Other Payment'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('other_payment[]', $payment->amount ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('other_payment_id[]', $payment->id ?? null) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No other payments found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Overtime --}}
                    <div id="overtime" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @forelse($overtimes as $overtime)
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('rate', ($overtime->title ?? __('Overtime')) . ' ' . __('Rate'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('rate[]', $overtime->rate ?? 0, ['class' => 'form-control']) !!}
                                        {!! Form::hidden('rate_id[]', $overtime->id ?? null) !!}
                                    </div>
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('hours', ($overtime->title ?? __('Overtime')) . ' ' . __('Hours'), [
                                            'class' => 'col-form-label',
                                        ]) !!}
                                        {!! Form::text('hours[]', $overtime->hours ?? 0, ['class' => 'form-control']) !!}
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No overtime rows found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Other Deduction (adhoc, this payslip only) --}}
                    <div id="otherDeduction" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                @if (!empty($other_deductions))
                                    @foreach ($other_deductions as $od)
                                        <div class="col-md-6 form-group">
                                            {!! Form::label('other_deduction_title[]', __('Title'), ['class' => 'col-form-label']) !!}
                                            {!! Form::text('other_deduction_title[]', $od['title'] ?? '', [
                                                'class' => 'form-control',
                                                'placeholder' => __('e.g. Short Pay Adjustment'),
                                            ]) !!}
                                        </div>
                                        <div class="col-md-6 form-group">
                                            {!! Form::label('other_deduction_amount[]', __('Amount'), ['class' => 'col-form-label']) !!}
                                            {!! Form::number('other_deduction_amount[]', isset($od['amount']) ? (float) $od['amount'] : null, [
                                                'class' => 'form-control',
                                                'step' => '0.01',
                                                'placeholder' => '0.00',
                                            ]) !!}
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Provide one empty row so user can add a new adhoc deduction --}}
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('other_deduction_title[]', __('Title'), ['class' => 'col-form-label']) !!}
                                        {!! Form::text('other_deduction_title[]', null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('e.g. Short Pay Adjustment'),
                                        ]) !!}
                                    </div>
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('other_deduction_amount[]', __('Amount'), ['class' => 'col-form-label']) !!}
                                        {!! Form::number('other_deduction_amount[]', null, [
                                            'class' => 'form-control',
                                            'step' => '0.01',
                                            'placeholder' => '0.00',
                                        ]) !!}
                                    </div>
                                @endif

                                <div class="col-12">
                                    <small class="text-muted">
                                        {{ __('These deductions affect only this payslip and do not change the base salary or master settings.') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Leaves --}}
                    {{-- <div id="leaves" class="tab-pane fade">
                        <div class="card bg-none mb-0">
                            <div class="row px-3">
                                <div class="col-md-3 form-group">
                                    {!! Form::label('total_leave', __('Total Leave'), ['class'=>'col-form-label']) !!}
                                    {!! Form::number('total_leave', $leavesObj->total_leave ?? 0, ['class' => 'form-control', 'step' => '0.5']) !!}
                                </div>
                                <div class="col-md-3 form-group">
                                    {!! Form::label('paid', __('Paid'), ['class'=>'col-form-label']) !!}
                                    {!! Form::number('paid', $leavesObj->paid ?? 0, ['class' => 'form-control', 'step' => '0.5']) !!}
                                </div>
                                <div class="col-md-3 form-group">
                                    {!! Form::label('unpaid', __('Un-Paid'), ['class'=>'col-form-label']) !!}
                                    {!! Form::number('unpaid', $leavesObj->unpaid ?? 0, ['class' => 'form-control', 'step' => '0.5']) !!}
                                </div>
                                <div class="col-md-3 form-group">
                                    {!! Form::label('late', __('Late'), ['class'=>'col-form-label']) !!}
                                    {!! Form::number('late', $leavesObj->late ?? 0, ['class' => 'form-control', 'step' => '0.25']) !!}
                                </div>
                            </div>
                        </div>
                    </div> --}}

                </div> {{-- /tab-content --}}
            </div> {{-- /row --}}

            <div class="modal-footer">
                <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
                <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
            </div>

            {{ Form::close() }}
        </div>
    </div>
</div>
