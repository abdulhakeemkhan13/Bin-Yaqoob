<div class="modal-body">
  <form method="POST" action="{{ route('reminders.update', $r->id) }}">
    @csrf
    @method('PUT')
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ $r->title }}" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Type') }}</label>
        <select name="reminder_type_id" class="form-select" required>
          <option value="">{{ __('Select Type') }}</option>
          @foreach($types as $t)
            <option value="{{ $t->id }}" @selected($r->reminder_type_id == $t->id)>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-12 mb-3">
        <label class="form-label">{{ __('Description') }}</label>
        <textarea name="description" class="form-control" rows="3">{{ $r->description }}</textarea>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Due Date') }}</label>
        <input type="date" name="due_date" class="form-control" value="{{ $r->due_date->format('Y-m-d') }}" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Before Days') }}</label>
        <input type="number" name="before_days" min="0" max="365" class="form-control" value="{{ $r->before_days }}" required>
        <div class="form-text">{{ __('Start reminding this many days before the due date.') }}</div>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Mark as Completed') }}</label>
        <select name="is_completed" class="form-select">
          <option value="0" @selected(!$r->is_completed)>{{ __('No') }}</option>
          <option value="1" @selected($r->is_completed)>{{ __('Yes') }}</option>
        </select>
      </div>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
  </form>
</div>
