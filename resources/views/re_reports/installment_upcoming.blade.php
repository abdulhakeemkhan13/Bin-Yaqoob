@extends('re_reports.layout', ['title' => __('Installment Due Report (Upcoming 30 Days)')])

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
                <a href="{{ route('re-reports.installment-upcoming') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>
    <h5 class="text-primary mb-3" style="margin-left: 15px;">{{ __('Upcoming Installments (Next 30 Days)') }}</h5>
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
            @forelse($upcomingInstallments as $installment)
                <tr>
                    <td>{{ $installment->issue_date->format('d-m-Y') }}</td>
                    <td>{{ $installment->due_date->format('d-m-Y') }}</td>
                    <td>{{ \Auth::user()->contractNumberFormat($installment->contract->id) }}</td>
                    <td>{{ $installment->contract->customer->name ?? ($installment->contract->client_name ?? '-') }}</td>
                    <td>{{ $installment->contract->re_project->name ?? '-' }}</td>
                    <td>{{ $installment->contract->tower->tower_name ?? '-' }} /
                        {{ $installment->contract->floor->floor_name ?? '-' }}</td>
                    <td>{{ $installment->contract->unit->unit_number ?? '-' }}</td>
                    <td>{{ $installment->installment_number }}</td>
                    <td>{{ \Auth::user()->priceFormat($installment->amount) }}</td>
                    <td>
                        <span class="badge bg-warning text-dark">{{ ucfirst($installment->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">{{ __('No upcoming installments found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
