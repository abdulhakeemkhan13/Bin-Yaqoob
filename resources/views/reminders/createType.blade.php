<div class="modal-body">
  <form method="POST" action="{{ route('reminder-types.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">{{ __('Type Name') }}</label>
      <input type="text" name="name" class="form-control" required placeholder="{{ __('e.g., Tax Filing, EOBI, Social Security') }}">
    </div>
    <div class="text-end">
      <button type="submit" class="btn btn-secondary">{{ __('Create Type') }}</button>
    </div>
  </form>
</div>
