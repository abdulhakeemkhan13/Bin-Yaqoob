{{-- resources/views/employee/increment_history_modal.blade.php --}}
<div class="modal-body">
    @if($rows->isEmpty())
        <div class="alert alert-warning mb-0">
            {{ __('No increment history found.') }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Increment Date') }}</th>
                        <th class="text-end">{{ __('Increment Amount') }}</th>
                        <th class="text-end">{{ __('Previous Salary') }}</th>
                        <th style="min-width:200px">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumInc = 0;
                    @endphp
                    @foreach($rows as $r)
                        @php
                            $sumInc += (float) $r->increment_amount;
                        @endphp
                        <tr>
                            <td>{{ \Auth::user()->dateFormat($r->increment_date) }}</td>
                            <td class="text-end">{{ \Auth::user()->priceFormat($r->increment_amount) }}</td>
                            <td class="text-end">{{ \Auth::user()->priceFormat($r->previous_salary) }}</td>
                            {{-- <td class="text-end">{{ \Auth::user()->priceFormat($r->new_salary) }}</td> --}}
                            <td class="text-wrap">
                                {{ $r->notes ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                {{-- <tfoot class="table-light">
                    <tr>
                        <th class="text-end">{{ __('Total Increments') }}</th>
                        <th class="text-end">{{ \Auth::user()->priceFormat($sumInc) }}</th>
                        <th colspan="4"></th>
                    </tr>
                </tfoot> --}}
            </table>
        </div>
    @endif
</div>

{{-- Inline, modal-scoped helpers only (no global scripts) --}}
<script>
(function () {
    // nothing required here right now; keeping an IIFE so this file can stay self-contained
})();
</script>
