@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Reminder Types') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reminder Types') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
            <a href="#"
               data-url="{{ route('reminder-types.create') }}"
               data-ajax-popup="true"
               data-title="{{ __('Create Reminder Type') }}"
               data-bs-toggle="tooltip" title="{{ __('Create') }}"
               class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable" id="reminder-types-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type Name') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style" id="reminder-types-table-body">
                                @foreach ($types as $type)
                                    <tr data-row_id="{{ $type->id }}">
                                        <td>{{ $type->name }}</td>
                                        <td class="Action">
                                            <span>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#"
                                                           class="mx-3 btn btn-sm align-items-center"
                                                           data-url="{{ route('reminder-types.edit', $type->id) }}"
                                                           data-ajax-popup="true"
                                                           data-title="{{ __('Edit Reminder Type') }}"
                                                           data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['reminder-types.destroy', $type->id], 'id' => 'delete-form-'.$type->id]) !!}
                                                            <a href="#"
                                                               class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                               data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                               data-confirm="{{ __('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?') }}"
                                                               data-confirm-yes="document.getElementById('delete-form-{{ $type->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> <!-- table-responsive -->
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col-9 -->
    </div> <!-- row -->
@endsection
