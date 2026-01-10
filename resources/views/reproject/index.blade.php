@extends('layouts.admin')

@section('page-title')
    {{ __('Real Estate Projects') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Real Estate Projects') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('re-projects.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Create New Project') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Project Name') }}</th>
                                    <th>{{ __('City') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Floors') }}</th>
                                    <th>{{ __('Units') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $project)
                                    <tr>
                                        <td>{{ $project->code }}</td>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->city }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $project->type }}</span>
                                        </td>
                                        <td>{{ $project->total_floors }}</td>
                                        <td>{{ $project->total_units }}</td>
                                        <td>
                                            @if ($project->status == 'Planning')
                                                <span class="badge bg-warning">{{ $project->status }}</span>
                                            @elseif($project->status == 'Active')
                                                <span class="badge bg-success">{{ $project->status }}</span>
                                            @else
                                                <span class="badge bg-info">{{ $project->status }}</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{ route('re-projects.show', $project->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                @if ($project->status == 'Planning')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route('re-projects.create') }}?project_id={{ $project->id }}"
                                                            class="mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip" title="{{ __('Continue Setup') }}">
                                                            <i class="ti ti-edit text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </span>
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
