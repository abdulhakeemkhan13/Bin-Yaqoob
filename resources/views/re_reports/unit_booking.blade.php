@extends('re_reports.layout', ['title' => __('Unit Booking Report ')])

@section('report-table')
    <table class="table table-bordered table-striped datatable">
        <thead>
            <tr>
                <th>{{ __('S/N') }}</th>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Tower / Floor') }}</th>
                <th>{{ __('Unit #') }}</th>
                <th>{{ __('Customer Name') }}</th>
                <th>{{ __('Phone #') }}</th>
                <th>{{ __('CNIC') }}</th>
                <th>{{ __('Total Price') }}</th>
                <th>{{ __('Down Payment') }}</th>
                <th>{{ __('Net Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $index => $booking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $booking->booking_number }}</td>
                    <td>{{ $booking->booking_date->format('d-m-Y') }}</td>
                    <td>{{ $booking->unit->floor->project->name ?? '-' }}</td>
                    <td>{{ $booking->unit->floor->tower->tower_name ?? '-' }} / {{ $booking->unit->floor->floor_name ?? '-' }}</td>
                    <td>{{ $booking->unit->unit_number ?? '-' }}</td>
                    <td>{{ $booking->customer_name }}</td>
                    <td>{{ $booking->customer_phone }}</td>
                    <td>{{ $booking->customer_cnic }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->total_price) }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->down_payment) }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->net_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
