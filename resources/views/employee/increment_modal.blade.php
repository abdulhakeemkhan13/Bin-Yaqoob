{{-- resources/views/employee/increment_modal.blade.php --}}
<div class="modal-body">
    @if($employees->isEmpty())
        <div class="alert alert-warning mb-0">
            {{ __('No employees found.') }}
        </div>
    @else
        {{ Form::open(['route' => 'employee.increment', 'method' => 'post', 'id' => 'incrementForm']) }}
        @csrf

        {{-- Row: Employee selector --}}
        <div class="row g-3">
            <div class="col-md-12">
                {!! Form::label('employee_id', __('Select Employee'), ['class' => 'form-label']) !!}
                <select name="employee_id" id="employee_id" class="form-control" required>
                    <option value="">{{ __('Choose...') }}</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                    {{ __('Choosing an employee will auto-fill details below.') }}
                </small>
            </div>
        </div>

        <hr class="my-3">

        {{-- Auto-filled details (3 per row) --}}
        <div class="row g-3">
            <div class="col-md-4">
                {!! Form::label('disp_name', __('Name'), ['class' => 'form-label']) !!}
                <input type="text" id="disp_name" class="form-control" value="" readonly>
            </div>

            <div class="col-md-4">
                {!! Form::label('disp_previous_salary', __('Previous Salary'), ['class' => 'form-label']) !!}
                <input type="text" id="disp_previous_salary" class="form-control" value="" readonly>
            </div>

            <div class="col-md-4">
                {!! Form::label('disp_last_increment', __('Last Increment Date'), ['class' => 'form-label']) !!}
                <input type="text" id="disp_last_increment" class="form-control" value="" readonly>
            </div>
        </div>

        {{-- Hidden store of numeric previous salary for preview math --}}
        <input type="hidden" id="previous_salary_numeric" value="0">

        <hr class="my-3">

        {{-- Inputs (Increment Date / Amount / Notes) + New Salary preview (3 per row) --}}
        <div class="row g-3">
            <div class="col-md-4">
                {!! Form::label('increment_date', __('Increment Date'), ['class' => 'form-label']) !!}
                {!! Form::date('increment_date', now()->toDateString(), ['class' => 'form-control', 'required' => true]) !!}
            </div>

            <div class="col-md-4">
                {!! Form::label('increment_amount', __('Increment Amount'), ['class' => 'form-label']) !!}
                <input type="number" name="increment_amount" id="increment_amount" class="form-control"
                       step="0.01" min="0.01" required placeholder="0.00">
            </div>

            <div class="col-md-4">
                {!! Form::label('disp_new_salary', __('New Salary (Preview)'), ['class' => 'form-label']) !!}
                <input type="text" id="disp_new_salary" class="form-control" value="" readonly>
            </div>

            <div class="col-md-12">
                {!! Form::label('notes', __('Notes (optional)'), ['class' => 'form-label']) !!}
                <textarea name="notes" id="notes" class="form-control" rows="2"
                          placeholder="{{ __('E.g., Annual increment, performance-based, etc.') }}"></textarea>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Save Increment') }}</button>
        </div>

        {{ Form::close() }}
    @endif
</div>

{{-- Inline, modal-scoped JS (NO extra AJAX) --}}
<script>
(function () {
    'use strict';

    // All data we need is pre-rendered from the controller:
    // { [employeeId]: { id, name, previous_salary, last_increment_at } }
    const EMP_DATA = @json($empPayload, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);

    const $emp     = $('#employee_id');
    const $name    = $('#disp_name');
    const $prevTxt = $('#disp_previous_salary');
    const $prevNum = $('#previous_salary_numeric');
    const $lastInc = $('#disp_last_increment');
    const $incAmt  = $('#increment_amount');
    const $newSal  = $('#disp_new_salary');

    function fmtCurrency(n) {
        n = isNaN(n) ? 0 : Number(n);
        return new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(n);
    }

    function recalc() {
        const prev = parseFloat($prevNum.val() || '0');
        const inc  = parseFloat($incAmt.val() || '0');
        const sum  = prev + (isNaN(inc) ? 0 : inc);
        $newSal.val(fmtCurrency(sum));
    }

    $incAmt.on('input', recalc);

    $emp.on('change', function () {
        const id = $(this).val();
        if (!id || !EMP_DATA[id]) {
            $name.val('');
            $prevTxt.val('');
            $prevNum.val('0');
            $lastInc.val('');
            recalc();
            return;
        }

        const info = EMP_DATA[id];
        $name.val(info.name || '');
        const prev = parseFloat(info.previous_salary || 0);
        $prevNum.val(prev.toFixed(2));
        $prevTxt.val(fmtCurrency(prev));
        $lastInc.val(info.last_increment_at || '-');
        recalc();
    });

    // Optional: auto-select first employee for convenience
    // const firstId = Object.keys(EMP_DATA)[0];
    // if (firstId) { $emp.val(firstId).trigger('change'); }
})();
</script>
