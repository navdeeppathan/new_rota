<!DOCTYPE html>
<html>
<head>
    <title>Shift Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
        .ot { background: #ff9999; font-weight: bold; }
        .subtotal { font-weight: bold; background: #ffffe6; }
        .total-row { font-weight: bold; background: #e6ffe6; }
    </style>
</head>
<body>
    <h2>Shift Report ({{ $start_date }} - {{ $end_date }})</h2>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Shift Type</th>
                <th>Worked Hours</th>
                <th>Overtime</th>
            </tr>
        </thead>
        <tbody>
        @php
            $grandMinutes = 0;
            $grandOT = 0;
        @endphp

        @foreach($users as $user)
            @php
                $empMinutes = 0;
                $empOT = 0;
            @endphp

            @if($user->shifts->count() > 0)
                @foreach($user->shifts as $shift)
                    @php
                        $ot = $shift->overtime_minutes ?? 0;

                        if ($shift->start_time && $shift->end_time) {
                            // Custom times exist
                            $start = \Carbon\Carbon::parse($shift->start_time);
                            $end   = \Carbon\Carbon::parse($shift->end_time);
                            $minutes = $end->diffInMinutes($start);
                        } else {
                            // Default shift timings
                            if ($shift->shift_type === 'LD') {
                                $start = \Carbon\Carbon::parse($shift->date . ' 07:15');
                                $end   = \Carbon\Carbon::parse($shift->date . ' 20:30');
                                $minutes = $end->diffInMinutes($start);
                            } elseif ($shift->shift_type === 'N') {
                                $start = \Carbon\Carbon::parse($shift->date . ' 20:15');
                                $end   = \Carbon\Carbon::parse($shift->date)->addDay()->setTime(7,30);
                                $minutes = $end->diffInMinutes($start);
                            } else {
                                $minutes = 0; // no shift data
                            }
                        }

                        // Add to totals
                        $empMinutes += $minutes;
                        $grandMinutes += $minutes;
                        $empOT += $ot;
                        $grandOT += $ot;
                    @endphp

                    <tr class="{{ $ot > 0 ? 'ot' : '' }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $shift->date }}</td>
                        <td>{{ $shift->shift_type }}</td>
                        <td>{{ floor($minutes/60) }}h {{ $minutes%60 }}m</td>
                        <td>{{ $ot > 0 ? floor($ot/60).'h '.($ot%60).'m' : '-' }}</td>
                    </tr>
                @endforeach

                {{-- Employee total row --}}
                <tr class="subtotal">
                    <td colspan="3">{{ $user->name }} Total</td>
                    <td>{{ floor($empMinutes/60) }}h {{ $empMinutes%60 }}m</td>
                    <td>{{ floor($empOT/60) }}h {{ $empOT%60 }}m</td>
                </tr>
            @endif
        @endforeach

        {{-- Grand total row --}}
        <tr class="total-row">
            <td colspan="3">GRAND TOTAL</td>
            <td>{{ floor($grandMinutes/60) }}h {{ $grandMinutes%60 }}m</td>
            <td>{{ floor($grandOT/60) }}h {{ $grandOT%60 }}m</td>
        </tr>
        </tbody>
    </table>
</body>
</html>
