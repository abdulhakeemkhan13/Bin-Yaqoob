@extends('re_reports.layout', ['title' => __('Installment Plan List Report')])

@section('report-table')
    <form action="" method="GET" class="mb-4 no-print" style="margin-left: 20px;">
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
            <div class="col-md-3 mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                <a href="{{ route('re-reports.installment-plans') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>

    @foreach ($projectsWithPlans as $project)
        @if ($project->paymentPlans->count() > 0)
            <div class="project-section mb-5">
                <h5 class="bg-primary text-white p-2">{{ __('Project') }}: {{ $project->name }}</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Plan Name') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Down Payment') }}</th>
                            <th>{{ __('Installments') }}</th>
                            <th>{{ __('Frequency') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->paymentPlans as $plan)
                            <tr>
                                <td>{{ $plan->plan_name }}</td>
                                <td>{{ $plan->duration_months }} {{ __('Months') }}</td>
                                <td>
                                    @if ($plan->down_payment_percentage > 0)
                                        {{ $plan->down_payment_percentage }}%
                                    @else
                                        {{ \Auth::user()->priceFormat($plan->down_payment_amount) }}
                                    @endif
                                </td>
                                <td>{{ $plan->num_installments }}</td>
                                <td>{{ $plan->frequency }}</td>
                                <td>
                                    @if ($plan->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
@endsection
