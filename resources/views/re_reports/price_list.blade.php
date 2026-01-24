@extends('re_reports.layout', ['title' => __('Unit Price List Report')])

@section('report-filters')
    <form method="GET" action="{{ route('re-reports.price-list') }}" class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label>{{ __('Project') }}</label>
                <select name="project_id" class="form-control" data-toggle="select">
                    <option value="">{{ __('Select Project') }}</option>
                    <option value="all" {{ request('project_id') == 'all' ? 'selected' : '' }}>{{ __('All Projects') }}
                    </option>
                    @foreach ($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }} {{ $p->status != 'Active' ? '(' . $p->status . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>{{ __('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (\App\Models\ReUnit::$statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mt-4">
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                <a href="{{ route('re-reports.price-list') }}" class="btn btn-secondary btn-sm">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>
@endsection

@section('report-table')
    <table class="table table-bordered table-striped datatable">
        <thead>
            <tr>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Tower / Block') }}</th>
                <th>{{ __('Floor') }}</th>
                <th>{{ __('Unit Number') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Facing') }}</th>
                <th>{{ __('Area (Sq.ft)') }}</th>
                <th>{{ __('Rate/Sq.ft') }}</th>
                <th>{{ __('Total Price') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($units as $unit)
                <tr>
                    <td>{{ $unit->floor->project->project_name ?? '-' }}</td>
                    <td>{{ $unit->floor->tower->tower_name ?? '-' }}</td>
                    <td>{{ $unit->floor->floor_name ?? '-' }}</td>
                    <td>{{ $unit->unit_number }}</td>
                    <td>{{ $unit->unit_type }}</td>
                    <td>
                        <span
                            class="badge @if ($unit->status == 'Available') bg-success @elseif($unit->status == 'Booked') bg-warning @elseif($unit->status == 'Sold') bg-danger @else bg-dark @endif">
                            {{ $unit->status }}
                        </span>
                    </td>
                    <td>{{ $unit->facing }}</td>
                    <td>{{ number_format($unit->covered_area, 2) }}</td>
                    <td>{{ \Auth::user()->priceFormat($unit->price_per_sqft) }}</td>
                    <td>{{ \Auth::user()->priceFormat($unit->price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
