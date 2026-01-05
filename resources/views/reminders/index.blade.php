@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Reminders') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reminders') }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex gap-2">
        <a href="#" data-url="{{ route('reminder-types.create') }}" data-ajax-popup="true" data-size="md"
            data-bs-toggle="tooltip" title="{{ __('Create Type') }}" data-title="{{ __('Create Reminder Type') }}"
            class="btn btn-sm btn-secondary">
            {{ __('Create Type') }}
            <i class="ti ti-category-plus"></i>
        </a>
        <a href="#" data-url="{{ route('reminders.create') }}" data-ajax-popup="true" data-size="lg"
            data-bs-toggle="tooltip" title="{{ __('Create Reminder') }}" data-title="{{ __('Create Reminder') }}"
            class="btn btn-sm btn-primary">
            {{ __('Create Reminder') }}
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Toaster will read session("success") automatically --}}
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable" id="reminders-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Before Days') }}</th>
                                    <th>{{ __('Remind From') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reminders as $r)
                                    @php
                                        $today = now()->toDateString();
                                        $status = 'Scheduled';
                                        $badge = 'secondary';
                                        if ($r->is_completed) {
                                            $status = 'Completed';
                                            $badge = 'success';
                                        } elseif ($today > $r->due_date->toDateString()) {
                                            $status = 'Overdue';
                                            $badge = 'danger';
                                        } elseif (
                                            $today >= $r->remind_from_date->toDateString() &&
                                            $today <= $r->due_date->toDateString()
                                        ) {
                                            $status = 'In Reminder Window';
                                            $badge = 'warning';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $r->title }}</td>
                                        <td>{{ $r->type?->name ?? '-' }}</td>
                                        <td>{{ $r->due_date->format('Y-m-d') }}</td>
                                        <td>{{ $r->before_days }}</td>
                                        <td>{{ $r->remind_from_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-{{ $badge }}">{{ __($status) }}</span></td>
                                        <td class="Action">
                                            <div class="d-flex">
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('reminders.edit', $r->id) }}"
                                                        data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Reminder') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{ route('reminders.destroy', $r->id) }}"
                                                        id="delete-form-{{ $r->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $r->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    </form>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">{{ __('No reminders yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
