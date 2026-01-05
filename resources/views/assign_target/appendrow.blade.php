<tr data-row_id="{{ $target->id }}">
    <td>{{ $target->user ? $target->user->name : __('User Deleted') }}</td>
    <td>{{ date('F', strtotime($target->month)) }}</td>
    <td>{{ $target->lead_target }}</td>
    <td>{{ $target->deal_target }}</td>
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch')
        <td class="action text-end">
            <div class="action-btn bg-info ms-2">
                <a href="#"
                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                    data-url="{{ route('assign-target.edit', $target->id) }}"
                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                    title="{{ __('Edit') }}"
                    data-title="{{ __('Edit Target') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
            <div class="action-btn bg-danger ms-2">
                {!! Form::open(['method' => 'DELETE', 'route' => ['assign-target.destroy', $target->id]]) !!}
                <a href="#"
                    class="mx-3 btn btn-sm align-items-center bs-pass-para"
                    data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i
                        class="ti ti-trash text-white"></i></a>
                {!! Form::close() !!}
            </div>
        </td>
    @endif
</tr>