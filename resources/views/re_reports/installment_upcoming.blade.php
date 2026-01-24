@extends('re_reports.layout', ['title' => __('Installment Due Report (Upcoming 30 Days)')])

@section('report-table')
    <h5 class="text-primary mb-3">{{ __('Upcoming Installments (Next 30 Days)') }}</h5>
    <table class="table table-bordered table-striped datatable">
        <thead>
            <tr>
                <th>{{ __('Due Date') }}</th>
                <th>{{ __('Contract #') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Tower') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Inst. #') }}</th>
                <th>{{ __('Amount Due') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($upcomingInstallments as $installment)
                <tr>
                    <td>{{ $installment->due_date->format('d-m-Y') }}</td>
                    <td>{{ $installment->contract->contract_no ?? '-' }}</td>
                    <td>{{ $installment->contract->customer->name ?? ($installment->contract->client_name ?? '-') }}</td>
                    <td>{{ $installment->contract->re_project->project_name ?? '-' }}</td>
                    <td>{{ $installment->contract->tower->tower_name ?? '-' }}</td>
                    <td>{{ $installment->contract->unit->unit_number ?? '-' }}</td>
                    <td>{{ $installment->installment_number }}</td>
                    <td>{{ \Auth::user()->priceFormat($installment->amount) }}</td>
                    <td>
                        <span class="badge bg-warning text-dark">{{ ucfirst($installment->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('No upcoming installments found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
