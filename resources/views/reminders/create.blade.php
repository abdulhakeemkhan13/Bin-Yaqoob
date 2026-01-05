<div class="modal-body">
  <form method="POST" action="{{ route('reminders.store') }}">
    @csrf
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Title') }}</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Type') }}</label>
        <select id="reminder-type-select" name="reminder_type_id" class="form-select" required>
          <option value="">{{ __('Select Type') }}</option>
          <option value="__create_type__">+ {{ __('Add new type') }}</option>
          @foreach($types as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
          @endforeach
        </select>
        <div class="form-text">
          {{ __('Missing a type? Choose “Add new type” and create it quickly.') }}
        </div>
      </div>

      <div class="col-md-12 mb-3">
        <label class="form-label">{{ __('Description') }}</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Due Date') }}</label>
        <input type="date" name="due_date" class="form-control" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Before Days') }}</label>
        <input type="number" name="before_days" min="0" max="365" class="form-control" value="0" required>
        <div class="form-text">{{ __('Start reminding this many days before the due date.') }}</div>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
    </div>
  </form>
</div>

{{-- JS: open “Create Type” modal from inside this modal and append the result --}}
<script>
(function() {
  const TYPE_CREATE_URL = @json(route('reminder-types.create'));
  const TYPE_STORE_URL  = @json(route('reminder-types.store'));
  const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const typeSelect = document.getElementById('reminder-type-select');

  function openInlineModal({ id, title, bodyHtml, size = 'md' }) {
    const existing = document.getElementById(id);
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = id;
    modal.tabIndex = -1;
    modal.innerHTML = `
      <div class="modal-dialog modal-${size} modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
          </div>
          <div class="modal-body">${bodyHtml}</div>
        </div>
      </div>`;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal, { backdrop: 'static' });
    bsModal.show();
    return { modal, bsModal };
  }

  function insertAfterPlaceholder(selectEl, optEl) {
    const placeholder = selectEl.querySelector('option[value=""]') || selectEl.options[0];
    if (!placeholder) { selectEl.insertBefore(optEl, selectEl.firstChild); return; }
    const after = placeholder.nextSibling;
    if (after) selectEl.insertBefore(optEl, after); else selectEl.appendChild(optEl);
  }

  async function showCreateTypeModal() {
    const res = await fetch(TYPE_CREATE_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const html = await res.text();

    const { modal, bsModal } = openInlineModal({
      id: 'inlineCreateTypeModal',
      title: @json(__('Create Reminder Type')),
      bodyHtml: html,
      size: 'md'
    });

    // SAFER: just take the FIRST form in the modal rather than matching by action string
    const form = modal.querySelector('form');
    if (!form) return;

    form.setAttribute('novalidate', 'novalidate');
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      e.stopPropagation();

      const btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;

      try {
        const fd = new FormData(form);
        const resp = await fetch(TYPE_STORE_URL, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken || form.querySelector('input[name="_token"]')?.value || ''
          },
          body: fd
        });

        const ct = resp.headers.get('content-type') || '';
        if (!resp.ok || !ct.includes('application/json')) {
          // (Optional) parse and render inline validation if HTML came back
          if (btn) btn.disabled = false;
          return;
        }

        const data = await resp.json();
        if (data?.success && data.id && data.name) {
          // build or find option
          let opt = typeSelect.querySelector(`option[value="${data.id}"]`);
          if (!opt) {
            opt = document.createElement('option');
            opt.value = String(data.id);
            opt.textContent = data.name;
            insertAfterPlaceholder(typeSelect, opt);   // << place AFTER "Select Type"
          }
          typeSelect.value = String(data.id);          // auto-select new type

          // close ONLY the type modal
          bsModal.hide();
          modal.addEventListener('hidden.bs.modal', () => modal.remove(), { once: true });
        } else {
          if (btn) btn.disabled = false;
        }
      } catch (err) {
        console.error(err);
        if (btn) btn.disabled = false;
      }
    }, { once: true });
  }

  // Open the type modal when sentinel is selected
  typeSelect?.addEventListener('change', function() {
    if (this.value === '__create_type__') {
      showCreateTypeModal().then(() => {
        const handler = () => {
          if (typeSelect.value === '__create_type__') typeSelect.value = '';
          document.getElementById('inlineCreateTypeModal')?.removeEventListener('hidden.bs.modal', handler);
        };
        document.getElementById('inlineCreateTypeModal')?.addEventListener('hidden.bs.modal', handler);
      });
    }
  });
})();
</script>

