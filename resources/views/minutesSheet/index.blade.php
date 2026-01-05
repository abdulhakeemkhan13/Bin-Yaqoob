@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Minutes Sheets') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Minutes Sheets') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if ($canCreate)
            <a href="#" data-size="lg" data-url="{{ route('minutes-sheet.create') }}" data-ajax-popup="true"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create Minutes Sheet') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5>{{ __('Minutes Sheets') }}</h5>
                            <small class="text-muted">{{ __('Your current designation:') }}
                                <strong>{{ $userDesignation ?? '-' }}</strong></small>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable" id="minutes-sheet-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Reference No') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Current Stage') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="150px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($minutesSheets as $sheet)
                                    <tr>
                                        <td>{{ $sheet->reference_no }}</td>
                                        <td>{{ \Auth::user()->dateFormat($sheet->date) }}</td>
                                        <td>{{ Str::limit($sheet->subject, 30) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($sheet->amount) }}</td>
                                        <td>{{ $sheet->creator->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $sheet->current_stage }}</span>
                                        </td>
                                        <td>
                                            @if ($sheet->status == 'pending')
                                                <span class="badge bg-warning">{{ ucfirst($sheet->status) }}</span>
                                            @elseif($sheet->status == 'approved')
                                                <span class="badge bg-success">{{ ucfirst($sheet->status) }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($sheet->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- View Button --}}
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('minutes-sheet.show', $sheet->id) }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                            {{-- Approval Action Button (for current approver OR rejected sheets) --}}
                                            @php
                                                $isCurrentApprover = $sheet->isCurrentApprover($userDesignation);
                                                $canResubmit =
                                                    $sheet->status == 'rejected' &&
                                                    $sheet->current_stage == $userDesignation;
                                            @endphp
                                            @if (($isCurrentApprover && $sheet->status == 'pending') || $canResubmit)
                                                <div
                                                    class="action-btn {{ $canResubmit ? 'bg-danger' : 'bg-warning' }} ms-2">
                                                    <a href="#"
                                                        data-url="{{ route('minutes-sheet.action', $sheet->id) }}"
                                                        data-size="lg" data-ajax-popup="true"
                                                        data-title="{{ $canResubmit ? __('Resubmit Action') : __('Approval Action') }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ $canResubmit ? __('Resubmit/Send Back') : __('Approve/Reject') }}">
                                                        <i
                                                            class="ti ti-{{ $canResubmit ? 'refresh' : 'checks' }} text-white"></i>
                                                    </a>
                                                </div>
                                            @endif

                                            {{-- Edit/Delete only for creator, when not approved, and still at early stages --}}
                                            @php
                                                $canEdit =
                                                    $sheet->created_by == Auth::user()->id &&
                                                    $sheet->status != 'approved' &&
                                                    in_array($sheet->current_stage, [
                                                        'Company',
                                                        'Senior Executive Finance',
                                                    ]);
                                            @endphp
                                            @if ($canEdit)
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#"
                                                        data-url="{{ route('minutes-sheet.edit', $sheet->id) }}"
                                                        data-size="lg" data-ajax-popup="true"
                                                        data-title="{{ __('Edit Minutes Sheet') }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['minutes-sheet.destroy', $sheet->id],
                                                        'id' => 'delete-form-' . $sheet->id,
                                                    ]) !!}
                                                    <a href="#"
                                                        class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('delete-form-{{ $sheet->id }}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
