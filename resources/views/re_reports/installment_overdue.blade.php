@extends('re_reports.layout', ['title' => __('Installment Due & Overdue Report')])

@section('report-table')
    <h5 class="text-danger mb-3">{{ __('Overdue Installments Across All Projects') }}</h5>
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
            @forelse($overdueInstallments as $installment)
                <tr>
                    <td class="text-danger">{{ $installment->due_date->format('d-m-Y') }}</td>
                    <td>{{ $installment->contract->contract_no ?? '-' }}</td>
                    <td>{{ $installment->contract->customer->name ?? ($installment->contract->client_name ?? '-') }}</td>
                    <td>{{ $installment->contract->re_project->project_name ?? '-' }}</td>
                    <td>{{ $installment->contract->tower->tower_name ?? '-' }}</td>
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
