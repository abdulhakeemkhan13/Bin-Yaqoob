@extends('layouts.admin')

@section('page-title')
    {{ $title }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Real Estate Reports') }}</li>
    <li class="breadcrumb-item">{{ $title }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" onclick="window.print()" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Print') }}">
            <i class="ti ti-printer"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    @yield('report-filters')
                    <div class="table-responsive mt-4">
                        @yield('report-table')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
    <style>
        @media print {

            .dash-header,
            .dash-sidebar,
            .dash-footer,
            .action-btn,
            .breadcrumb,
            .page-header-title,
            .dash-container .dash-content .page-header {
                display: none !important;
            }

            .dash-container {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }
        }
    </style>
@endpush
