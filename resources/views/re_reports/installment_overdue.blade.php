@extends('re_reports.layout', ['title' => __('Installment Due & Overdue Report')])

@section('report-table')
    <form action="" method="GET" class="mb-4 no-print" style="margin-left: 15px;">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Project') }}</label>
                <select name="project_id" class="form-control select2">
                    <option value="all" {{ request('project_id') == 'all' ? 'selected' : '' }}>{{ __('All Projects') }}
                    </option>
                    @foreach ($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->status != 'Active' ? '(' . $p->status . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <a href="{{ route('re-reports.installment-overdue') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>
    <h5 class="text-danger mb-3" style="color: red; margin-left: 15px;">
        {{ __('Overdue Installments Across All Projects') }}</h5>
    <table class="table table-bordered table-striped datatable">
        <thead>
            <tr>
                <th>{{ __('Issue Date') }}</th>
                <th>{{ __('Due Date') }}</th>
                <th>{{ __('Contract #') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Tower / Floor') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Inst. #') }}</th>
                <th>{{ __('Amount Due') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($overdueInstallments as $installment)
                <tr>
                    <td>{{ $installment->issue_date->format('d-m-Y') }}</td>
                    <td class="{{ $installment->due_date < Carbon\Carbon::today() ? 'text-danger' : '' }}">
                        {{ $installment->due_date->format('d-m-Y') }}</td>
                    <td>{{ \Auth::user()->contractNumberFormat($installment->contract->id) }}</td>
                    <td>{{ $installment->contract->customer->name ?? ($installment->contract->client_name ?? '-') }}</td>
                    <td>{{ $installment->contract->re_project->name ?? '-' }}</td>
                    <td>{{ $installment->contract->tower->tower_name ?? '-' }} /
                        {{ $installment->contract->floor->floor_name ?? '-' }}</td>
                    <td>{{ $installment->contract->unit->unit_number ?? '-' }}</td>
                    <td>{{ $installment->installment_number }}</td>
                    <td>{{ \Auth::user()->priceFormat($installment->amount) }}</td>
                    <td>
                        <span
                            class="badge @if ($installment->status == 'overdue') bg-danger @else bg-warning @endif">{{ ucfirst($installment->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('No overdue installments found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
