@extends('layouts.admin')

@section('page-title')
    {{ __('Minutes Sheet Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('minutes-sheet.index') }}">{{ __('Minutes Sheets') }}</a></li>
    <li class="breadcrumb-item">{{ $minutesSheet->reference_no }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('minutes-sheet.print', $minutesSheet->id) }}" class="btn btn-sm btn-info" target="_blank"
            data-bs-toggle="tooltip" title="{{ __('Print Minute Sheet') }}">
            <i class="ti ti-printer"></i> {{ __('Print') }}
        </a>
        @if ($canApprove)
            <a href="#" data-size="lg" data-url="{{ route('minutes-sheet.action', $minutesSheet->id) }}"
                data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Take Action') }}"
                data-title="{{ __('Approval Action') }}" class="btn btn-sm btn-warning">
                <i class="ti ti-checks"></i> {{ __('Approve/Reject') }}
            </a>
        @endif
        @if ($canEdit)
            <a href="#" data-size="lg" data-url="{{ route('minutes-sheet.edit', $minutesSheet->id) }}"
                data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                data-title="{{ __('Edit Minutes Sheet') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i> {{ __('Edit') }}
            </a>
        @endif
        <a href="{{ route('minutes-sheet.index') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- Main Details --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>{{ $minutesSheet->reference_no }}</h5>
                        <div>
                            @if ($minutesSheet->status == 'pending')
                                <span class="badge bg-warning fs-6">{{ ucfirst($minutesSheet->status) }}</span>
                            @elseif($minutesSheet->status == 'approved')
                                <span class="badge bg-success fs-6">{{ ucfirst($minutesSheet->status) }}</span>
                            @else
                                <span class="badge bg-danger fs-6">{{ ucfirst($minutesSheet->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">{{ __('Date') }}</p>
                            <h6>{{ \Auth::user()->dateFormat($minutesSheet->date) }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">{{ __('Amount') }}</p>
                            <h6>{{ \Auth::user()->priceFormat($minutesSheet->amount) }}</h6>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">{{ __('Bank Account') }}</p>
                            <h6>{{ $minutesSheet->bankAccount->holder_name ?? '-' }}
                                ({{ $minutesSheet->bankAccount->bank_name ?? '-' }})</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">{{ __('Created By') }}</p>
                            <h6>{{ $minutesSheet->creator->name ?? '-' }} <small
                                    class="text-muted">({{ $minutesSheet->designation }})</small></h6>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <p class="mb-1 text-muted">{{ __('Subject') }}</p>
                            <h6>{{ $minutesSheet->subject }}</h6>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <p class="mb-1 text-muted">{{ __('Description') }}</p>
                            <p>{{ $minutesSheet->description }}</p>
                        </div>
                    </div>

                    {{-- Current Stage Info --}}
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-info-circle me-2"></i>
                            <div>
                                @if ($minutesSheet->status == 'pending')
                                    <strong>{{ __('Current Stage:') }}</strong> {{ $minutesSheet->current_stage }}
                                    @if ($canApprove)
                                        <br><small>{{ __('You can approve or reject this minutes sheet.') }}</small>
                                    @else
                                        <br><small>{{ __('Waiting for approval from') }}
                                            {{ $minutesSheet->current_stage }}.</small>
                                    @endif
                                @elseif($minutesSheet->status == 'approved')
                                    <strong>{{ __('Fully approved by Chairman') }}</strong>
                                    <br><small>{{ __('Approved at:') }}
                                        {{ $minutesSheet->approved_at ? \Auth::user()->DateTimeFormat($minutesSheet->approved_at) : '-' }}</small>
                                @else
                                    <strong>{{ __('Rejected at stage:') }}</strong> {{ $minutesSheet->current_stage }}
                                    <br><small>{{ __('Rejected at:') }}
                                        {{ $minutesSheet->rejected_at ? \Auth::user()->DateTimeFormat($minutesSheet->rejected_at) : '-' }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Checks Section (only for approved minutes sheets) --}}
            @if ($minutesSheet->status == 'approved')
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>{{ __('Checks') }}</h5>
                            <div>
                                @php
                                    $remainingAmount = $minutesSheet->getRemainingAmount();
                                @endphp
                                @if ($remainingAmount > 0)
                                    <a href="{{ route('cheque.create', ['minutes_sheet_id' => $minutesSheet->id]) }}"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        title="{{ __('Create Check') }}">
                                        <i class="ti ti-plus"></i> {{ __('Create Check') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                {{ __('Total Amount:') }}
                                <strong>{{ \Auth::user()->priceFormat($minutesSheet->amount) }}</strong> |
                                {{ __('Used:') }}
                                <strong>{{ \Auth::user()->priceFormat($minutesSheet->getTotalChecksAmount()) }}</strong> |
                                {{ __('Remaining:') }} <strong
                                    class="text-success">{{ \Auth::user()->priceFormat($remainingAmount) }}</strong>
                            </small>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Check #') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Payee Name') }}</th>
                                        <th>{{ __('Bank') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th width="120px">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($minutesSheet->checks as $check)
                                        <tr>
                                            <td>{{ $check->cheque_number }}</td>
                                            <td>{{ \Auth::user()->dateFormat($check->date) }}</td>
                                            <td>{{ $check->account_title }}</td>
                                            <td>{{ $minutesSheet->bankAccount->bank_name ?? '-' }}</td>
                                            <td>{{ \Auth::user()->priceFormat($check->amount) }}</td>
                                            <td>
                                                <span class="badge {{ $check->getStatusBadgeClass() }}">
                                                    {{ ucfirst($check->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('cheque.show', $check->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                @if ($check->status === 'Pending')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('cheque.edit', $check->id) }}"
                                                            class="mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            onclick="deleteCheque({{ $check->id }})">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    </div>
                                                    {{ Form::open(['route' => ['cheque.destroy', $check->id], 'method' => 'delete', 'id' => 'delete-form-' . $check->id, 'style' => 'display:none;']) }}
                                                    {{ Form::close() }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                {{ __('No checks created yet') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Approval History --}}
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Approval History') }}</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($minutesSheet->logs as $log)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <span class="badge {{ $log->getActionBadgeClass() }}">
                                    @if ($log->action == 'created')
                                        <i class="ti ti-plus"></i>
                                    @elseif($log->action == 'approved')
                                        <i class="ti ti-check"></i>
                                    @else
                                        <i class="ti ti-x"></i>
                                    @endif
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ ucfirst($log->action) }}</h6>
                                <small class="text-muted">{{ $log->actionByUser->name ?? '-' }}</small>
                                <p class="mb-1">{{ $log->remarks }}</p>
                                @if ($log->details)
                                    <small class="text-muted">{{ $log->details }}</small>
                                @endif
                                <br>
                                <small class="text-muted"><i class="ti ti-clock"></i>
                                    {{ \Auth::user()->DateTimeFormat($log->created_at) }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">{{ __('No history available') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Approval Chain Visual --}}
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Approval Chain') }}</h5>
                </div>
                <div class="card-body">
                    @php
                        $chain = \App\Models\MinutesSheet::APPROVAL_CHAIN;
                        $currentIndex = array_search($minutesSheet->current_stage, $chain);
                        $creatorIndex = array_search($minutesSheet->designation, $chain);
                    @endphp
                    <ul class="list-group">
                        @foreach ($chain as $index => $stage)
                            @php
                                $isCreator = $stage == $minutesSheet->designation;
                                $isCurrent =
                                    $stage == $minutesSheet->current_stage && $minutesSheet->status == 'pending';
                                $isPassed = $index < $currentIndex || $minutesSheet->status == 'approved';
                                $isRejectedStage =
                                    $stage == $minutesSheet->current_stage && $minutesSheet->status == 'rejected';
                                $isBeforeCreator = $index <= $creatorIndex;
                            @endphp
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center
                                @if ($isRejectedStage) list-group-item-danger
                                @elseif($isCurrent) list-group-item-warning
                                @elseif($isPassed && !$isBeforeCreator) list-group-item-success @endif">
                                {{ $stage }}
                                @if ($isCreator)
                                    <span class="badge bg-primary">{{ __('Creator') }}</span>
                                @elseif($isRejectedStage)
                                    <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                @elseif($isCurrent)
                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                @elseif($isPassed && !$isBeforeCreator)
                                    <span class="badge bg-success">{{ __('Approved') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            // Approve check button
            $('.approve-check').on('click', function(e) {
                e.preventDefault();
                var checkId = $(this).data('check-id');

                if (confirm('Are you sure you want to approve this check?')) {
                    $.ajax({
                        url: '/minutes-sheet/check/' + checkId + '/approve',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                show_toastr('Success', response.message, 'success');
                                window.location.reload();
                            } else {
                                show_toastr('Error', response.error || 'Something went wrong',
                                    'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = xhr.responseJSON?.error ||
                                'Something went wrong';
                            show_toastr('Error', errorMessage, 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush
