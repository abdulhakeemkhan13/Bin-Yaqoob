@extends('re_reports.layout', ['title' => __('Project/ Floor/ Unit Availability Report')])

@section('report-table')
    @foreach ($projects as $project)
        <div class="project-section mb-5">
            <h5 class="bg-primary text-white p-2">{{ __('Project') }}: {{ $project->project_name }}</h5>
            @foreach ($project->floors as $floor)
                @if ($floor->units->count() > 0)
                    <div class="floor-section ms-3 mb-3">
                        <h6 class="bg-light p-2 border">{{ __('Floor') }}: {{ $floor->floor_name }}</h6>
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Unit Number') }}</th>
                                    <th>{{ __('Unit Type') }}</th>
                                    <th>{{ __('Covered Area (Sq.ft)') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($floor->units as $unit)
                                    <tr>
                                        <td>{{ $unit->unit_number }}</td>
                                        <td>{{ $unit->unit_type }}</td>
                                        <td>{{ number_format($unit->covered_area, 2) }}</td>
                                        <td>{{\Auth::user()->priceFormat($unit->price) }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $unit->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
@endsection
