@php
    /** Accept either $credit or $creditNote */
    $creditNote = $creditNote ?? ($credit ?? null);

    // Load the related invoice (since $creditNote->invoice is a FK int)
    $invoice = \App\Models\Invoice::with('customer')->find($creditNote->invoice);

    $invoiceNumber = $invoice
        ? Auth::user()->invoiceNumberFormat($invoice->invoice_id)
        : '';

    $customerName = $invoice && $invoice->customer
        ? $invoice->customer->name
        : '-';
@endphp

<tr data-row_id="{{ $creditNote->id }}">
    <td class="Id">
        @if($invoice)
            <a href="{{ route('invoice.show', \Crypt::encrypt($creditNote->invoice)) }}"
               class="btn btn-outline-primary">
                {{ $invoiceNumber }}
            </a>
        @else
            -
        @endif
    </td>
    <td>{{ $customerName }}</td>
    <td>{{ Auth::user()->dateFormat($creditNote->date) }}</td>
    <td>{{ Auth::user()->priceFormat($creditNote->amount) }}</td>
    <td>{{ $creditNote->description ?: '-' }}</td>
    <td>
        @can('edit credit note')
            <div class="action-btn bg-primary ms-2">
                <a data-url="{{ route('invoice.edit.credit.note', [$creditNote->invoice, $creditNote->id]) }}"
                   data-ajax-popup="true"
                   data-title="{{ __('Edit Credit Note') }}"
                   href="#"
                   class="mx-3 btn btn-sm align-items-center"
                   data-bs-toggle="tooltip"
                   title="{{ __('Edit') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endcan

        @can('edit credit note')
            <div class="action-btn bg-danger ms-2">
                {!! Form::open([
                    'method' => 'DELETE',
                    'route'  => ['invoice.delete.credit.note', $creditNote->invoice, $creditNote->id],
                    'id'     => 'delete-form-'.$creditNote->id
                ]) !!}
                    <button type="button"
                            class="mx-3 btn btn-sm align-items-center delete-confirm"
                            data-form="delete-form-{{ $creditNote->id }}"
                            title="{{ __('Delete') }}">
                        <i class="ti ti-trash text-white"></i>
                    </button>
                {!! Form::close() !!}
            </div>
        @endcan
    </td>
</tr>
