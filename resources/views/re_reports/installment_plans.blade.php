@extends('re_reports.layout', ['title' => __('Installment Plan List Report')])

@section('report-table')
    <table class="table table-bordered table-striped datatable">
        <thead>
            <tr>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Total Price') }}</th>
                <th>{{ __('Plan Name') }}</th>
                <th>{{ __('Installments') }}</th>
                <th>{{ __('Frequency') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_number }}</td>
                    <td>{{ $booking->customer_name }}</td>
                    <td>{{ $booking->unit->floor->project->project_name ?? '-' }}</td>
                    <td>{{ $booking->unit->unit_number ?? '-' }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->total_price) }}</td>
                    <td>{{ $booking->paymentPlan->name ?? '-' }}</td>
                    <td>{{ $booking->paymentPlan->num_installments ?? '-' }}</td>
                    <td>{{ $booking->paymentPlan->frequency ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
