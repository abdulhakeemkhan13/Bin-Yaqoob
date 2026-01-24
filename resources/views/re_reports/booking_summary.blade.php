@extends('re_reports.layout', ['title' => __('Booking Summary Report')])

@section('report-filters')
    <form method="GET" action="{{ route('re-reports.booking-summary') }}" class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label>{{ __('Start Date') }}</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label>{{ __('End Date') }}</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label>{{ __('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('All') }}</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}
                    </option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>
                        {{ __('Cancelled') }}</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>
                        {{ __('Completed') }}</option>
                </select>
            </div>
            <div class="col-md-3 mt-4">
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                <a href="{{ route('re-reports.booking-summary') }}"
                    class="btn btn-secondary btn-sm">{{ __('Reset') }}</a>
            </div>
        </div>
    </form>
@endsection

@section('report-table')
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Project') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Total Price') }}</th>
                <th>{{ __('Net Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_number }}</td>
                    <td>{{ $booking->booking_date->format('d-m-Y') }}</td>
                    <td>{{ $booking->customer_name }}</td>
                    <td>{{ $booking->unit->floor->project->project_name ?? '-' }}</td>
                    <td>{{ $booking->unit->unit_number ?? '-' }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->total_price) }}</td>
                    <td>{{ \Auth::user()->priceFormat($booking->net_amount) }}</td>
                    <td>
                        <span
                            class="badge @if ($booking->status == 'Active') bg-success @elseif($booking->status == 'Cancelled') bg-danger @else bg-info @endif">
                            {{ $booking->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">{{ __('No bookings found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
