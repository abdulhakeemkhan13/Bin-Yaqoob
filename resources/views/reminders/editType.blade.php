<div class="modal-body">
    <form method="POST" action="{{ route('reminder-types.update', $type->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">{{ __('Type Name') }}</label>
            <input type="text" name="name" class="form-control" required value="{{ $type->name }}">
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
    </form>
</div>
