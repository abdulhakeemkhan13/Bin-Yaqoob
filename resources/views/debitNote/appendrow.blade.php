@php
  // Accept either 'debitNote' or a stray 'debit'
  $debitNote = $debitNote ?? ($debit ?? null);

  // Ensure we have a Bill model with vendor loaded
  $billModel = isset($bill) && $bill ? $bill : \App\Models\Bill::with('vender')->find($debitNote->bill);

  $billNumber   = $billModel ? Auth::user()->billNumberFormat($billModel->bill_id) : '';
  $vendorName   = $billModel && $billModel->vender ? $billModel->vender->name : '-';
@endphp

<tr data-row_id="{{ $debitNote->id }}">
  <td class="Id">
    @if($billModel)
      <a href="{{ route('bill.show', \Crypt::encrypt($debitNote->bill)) }}" class="btn btn-outline-primary">
        {{ $billNumber }}
      </a>
    @else
      -
    @endif
  </td>
  <td>{{ $vendorName }}</td>
  <td>{{ Auth::user()->dateFormat($debitNote->date) }}</td>
  <td>{{ Auth::user()->priceFormat($debitNote->amount) }}</td>
  <td>{{ $debitNote->description ?: '-' }}</td>
  <td class="Action">
    <span>
      @can('edit debit note')
        <div class="action-btn bg-primary ms-2">
          <a data-url="{{ route('bill.edit.debit.note', [$debitNote->bill, $debitNote->id]) }}"
             data-ajax-popup="true"
             data-title="{{ __('Edit Debit Note') }}"
             href="#"
             class="mx-3 btn btn-sm align-items-center"
             data-bs-toggle="tooltip"
             title="{{ __('Edit') }}">
             <i class="ti ti-pencil text-white"></i>
          </a>
        </div>
      @endcan

      @can('edit debit note')
        <div class="action-btn bg-danger ms-2">
          {!! Form::open([
              'method' => 'DELETE',
              'route'  => ['bill.delete.debit.note', $debitNote->bill, $debitNote->id],
              'id'     => 'delete-form-'.$debitNote->id
          ]) !!}
            <a href="#"
               class="mx-3 btn btn-sm align-items-center bs-pass-para"
               data-bs-toggle="tooltip"
               title="{{ __('Delete') }}"
               data-confirm="{{ __('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?') }}"
               data-confirm-yes="document.getElementById('delete-form-{{ $debitNote->id }}').submit();">
               <i class="ti ti-trash text-white"></i>
            </a>
          {!! Form::close() !!}
        </div>
      @endcan
    </span>
  </td>
</tr>
