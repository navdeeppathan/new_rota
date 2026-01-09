@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Shift Report</h2>

    <!-- Date filter form -->
    <form method="GET" action="{{ route('reports.shifts') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control" 
                   value="{{ request('start_date') ?? now()->startOfWeek()->toDateString() }}">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" 
                   value="{{ request('end_date') ?? now()->endOfWeek()->toDateString() }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generate</button>
        </div>
        @if(isset($report))
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('reports.shifts.excel', request()->all()) }}" class="btn btn-success w-100">Export Excel</a>
        </div>
        @endif
    </form>

    @if(isset($report) && count($report))
        <!-- Wrapper for scrolling -->
        <div class="table-responsive" style="overflow-x:auto; white-space:nowrap;">
            <table class="table table-bordered text-center align-middle" style="min-width:1000px;">
                <thead class="table-dark">
                    <tr>
                        <th style="width:200px;">Employee</th>
                        @foreach($startDate->daysUntil($endDate->copy()->addDay()) as $day)
                            <th style="width:200px;">{{ $day->format('d/m/Y') }}</th>
                        @endforeach
                        <th style="width:200px;">Total Hours</th>
                        <th style="width:200px;">Total Overtime</th>
                        <th>Rate P/H (£)</th>
                        <th>OT Rate (£)</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $row)
                        <tr>
                            <td style="white-space:normal;">{{ $row['user'] }}</td>
                            @foreach($startDate->daysUntil($endDate->copy()->addDay()) as $day)
                                @php
                                    $entries = $row['daily'][$day->format('Y-m-d')] ?? [];
                                @endphp
                                <td>
                                    @if(count($entries))
                                        @foreach($entries as $e)
                                            <div>
                                                <span><b>{{ $e['shift_type'] }}</b></span><br>
                                                <span>{{ floor($e['minutes']/60) }}h {{ $e['minutes']%60 }}m</span><br>
                                                @if($e['overtime_minutes'] > 0)
                                                    <span class="badge bg-danger">
                                                        OT: {{ floor($e['overtime_minutes']/60) }}h {{ $e['overtime_minutes']%60 }}m
                                                    </span><br>
                                                @endif
                                                <span class="text-success">£{{ $e['cost'] }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            @endforeach
                            <td><b>{{ floor($row['total_minutes']/60) }}h {{ $row['total_minutes'] % 60 }}m</b></td>
                            <td>
                                @if($row['total_overtime'] > 0)
                                    <span class="badge bg-danger">
                                        {{ floor($row['total_overtime']/60) }}h {{ $row['total_overtime'] % 60 }}m
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>£{{ $row['rate'] }}</td>
                            <td>£{{ $row['overtime_rate'] }}</td>
                            <td><b>£{{ number_format($row['total_cost'], 2) }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th>Total (All Employees)</th>
                        @foreach($startDate->daysUntil($endDate->copy()->addDay()) as $day)
                            @php $minutes = $dailyTotals[$day->format('Y-m-d')] ?? 0; @endphp
                            <th>{{ $minutes ? floor($minutes/60).'h '.($minutes%60).'m' : '-' }}</th>
                        @endforeach
                        <th>{{ floor(array_sum($dailyTotals)/60) }}h {{ array_sum($dailyTotals)%60 }}m</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th><b>£{{ number_format($grandTotalCost, 2) }}</b></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
