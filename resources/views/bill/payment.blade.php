{{ Form::open(['route' => ['bill.payment', $bill->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            {{ Form::date('date', '', ['class' => 'form-control ', 'required' => 'required']) }}

        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', $bill->getDue(), ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}

        </div>
        <div class="form-group col-md-6">
            {{ Form::label('account_id', __('Account'), ['class' => 'form-label']) }}
            {{ Form::select('account_id', $accounts, null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', '', ['class' => 'form-control']) }}

        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => 3]) }}
        </div>


        <div class="col-md-6 form-group">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            <div class="choose-file ">
                <label for="file" class="form-label">
                    <input type="file" name="add_receipt" id="image" class="form-control">
                </label>
                <p class="upload_file"></p>
            </div>
        </div>

        {{-- NEW: Payment Type --}}
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $types, null, ['id' => 'type', 'class' => 'form-control select', 'required' => 'required']) }}
        </div>

{{-- Cheque Number (only when type = cheque) --}}
<div class="form-group col-md-6" id="cheque-number-wrap" style="display:none;">
  {{ Form::label('cheque_number', __('Cheque Number'), ['class' => 'form-label']) }}
  {{ Form::text('cheque_number', '', ['id' => 'cheque_number', 'class' => 'form-control', 'placeholder' => __('Enter Cheque Number')]) }}
  <small class="text-muted">{{ __('Required when Type is Cheque') }}</small>
</div>

{{-- Title of Account (Cheque Account Title Name) — only when type = cheque --}}
<div class="form-group col-md-6" id="title-of-account-wrap" style="display:none;">
  {{ Form::label('title_of_account', __('Title of Account'), ['class' => 'form-label']) }}
  {{ Form::text('title_of_account', '', ['id' => 'title_of_account', 'class' => 'form-control', 'placeholder' => __('Enter cheque account title')]) }}
  <small class="text-muted">{{ __('Required when Type is Cheque') }}</small>
</div>

    </div>
    <div class="modal-footer">

        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Add') }}" class="btn  btn-primary">
    </div>

</div>
{{ Form::close() }}

<script>
(function () {
  function toggleCheque() {
    var typeEl   = document.getElementById("type");
    var numWrap  = document.getElementById("cheque-number-wrap");
    var titleWrap= document.getElementById("title-of-account-wrap");
    var num      = document.getElementById("cheque_number");
    var title    = document.getElementById("title_of_account");
    if (!typeEl || !numWrap || !titleWrap || !num || !title) return;

    var isCheque = ((typeEl.value || "").toLowerCase() === "cheque");

    numWrap.style.display   = isCheque ? "" : "none";
    titleWrap.style.display = isCheque ? "" : "none";

    if (isCheque) {
      num.setAttribute("required", "required");
      title.setAttribute("required", "required");
    } else {
      num.removeAttribute("required");   num.value = "";
      title.removeAttribute("required"); title.value = "";
    }
  }

  document.addEventListener("DOMContentLoaded", toggleCheque);
  document.addEventListener("shown.bs.modal", toggleCheque);
  document.addEventListener("change", function (e) {
    if (e.target && e.target.id === "type") toggleCheque();
  });
  if (window.jQuery && window.jQuery(document).on) {
    window.jQuery(document).on("select2:select", "#type", toggleCheque);
  }
})();
</script>
