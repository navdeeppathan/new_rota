@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h4>Shift Definition</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('shift-definitions.store') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ auth()->id() }}" />

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Shift Slot</label>
                <select name="shift_slot" class="form-select" id="shift_slot" required>
                    <option value="">-- Select --</option>
                    <option value="Day">Day</option>
                    <option value="Night">Night</option>
                </select>
            </div>
        </div>

        <div id="dayFields" class="row mb-3">
            <div class="col-md-3">
                <label>Day Start</label>
                <input type="time" name="day_start" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Day End</label>
                <input type="time" name="day_end" class="form-control">
            </div>
        </div>

        <div id="nightFields" class="row mb-3">
            <div class="col-md-3">
                <label>Night Start</label>
                <input type="time" name="night_start" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Night End</label>
                <input type="time" name="night_end" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Break Start</label>
                <input type="time" name="break_start" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Break End</label>
                <input type="time" name="break_end" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Break Duration</label>
                <input type="text" name="break_duration" class="form-control" readonly>
            </div>
            <div class="col-md-3">
                <label>Total Break Time</label>
                <input type="text" name="total_break_time" class="form-control" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Total Working Time</label>
                <input type="text" name="total_working_time" class="form-control" readonly>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const userId = {{ auth()->id() }};

    fetch(`/user/shift-definitions?user_id=${userId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status && res.data) {
                for (const field in res.data) {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input && res.data[field]) {
                        input.value = res.data[field];
                    }
                }
                toggleShiftFields(); // show/hide after fill
            }
        })
        .catch(err => console.error('Error fetching shift data:', err));

    const shiftSlot     = document.getElementById('shift_slot');
    const dayFields     = document.getElementById('dayFields');
    const nightFields   = document.getElementById('nightFields');

    const breakStart    = document.querySelector('[name="break_start"]');
    const breakEnd      = document.querySelector('[name="break_end"]');
    const breakDuration = document.querySelector('[name="break_duration"]');
    const totalBreakTime = document.querySelector('[name="total_break_time"]');
    const totalWorkingTime = document.querySelector('[name="total_working_time"]');

    const dayStart      = document.querySelector('[name="day_start"]');
    const dayEnd        = document.querySelector('[name="day_end"]');
    const nightStart    = document.querySelector('[name="night_start"]');
    const nightEnd      = document.querySelector('[name="night_end"]');

    shiftSlot.addEventListener('change', () => {
        toggleShiftFields();
        updateWorkingDuration();
    });

    function toggleShiftFields() {
        if (shiftSlot.value === 'Day') {
            dayFields.style.display = 'flex';
            nightFields.style.display = 'none';
        } else if (shiftSlot.value === 'Night') {
            nightFields.style.display = 'flex';
            dayFields.style.display = 'none';
        } else {
            dayFields.style.display = 'none';
            nightFields.style.display = 'none';
        }
    }

    function parseTime(str) {
        const [h, m] = str.split(':');
        const date = new Date();
        date.setHours(+h);
        date.setMinutes(+m);
        date.setSeconds(0);
        date.setMilliseconds(0);
        return date;
    }

    function diffInMinutes(start, end) {
        let diff = (end - start) / 60000;
        if (diff < 0) diff += 1440; // next day
        return diff;
    }

    function formatDuration(minutes) {
        const h = Math.floor(minutes / 60);
        const m = Math.round(minutes % 60);
        return `${h} hr ${m} min`;
    }

    function updateBreakDuration() {
        if (breakStart.value && breakEnd.value) {
            const start = parseTime(breakStart.value);
            const end = parseTime(breakEnd.value);
            const minutes = diffInMinutes(start, end);
            const duration = formatDuration(minutes);
            breakDuration.value = duration;
            totalBreakTime.value = duration;
        } else {
            breakDuration.value = '';
            totalBreakTime.value = '';
        }
    }

    function updateWorkingDuration() {
        let start, end;
        if (shiftSlot.value === 'Day' && dayStart.value && dayEnd.value) {
            start = parseTime(dayStart.value);
            end = parseTime(dayEnd.value);
        } else if (shiftSlot.value === 'Night' && nightStart.value && nightEnd.value) {
            start = parseTime(nightStart.value);
            end = parseTime(nightEnd.value);
        } else {
            totalWorkingTime.value = '';
            return;
        }

        let minutes = diffInMinutes(start, end);
        totalWorkingTime.value = formatDuration(minutes);
    }

    [breakStart, breakEnd].forEach(input => {
        input.addEventListener('change', updateBreakDuration);
    });

    [dayStart, dayEnd, nightStart, nightEnd].forEach(input => {
        input.addEventListener('change', updateWorkingDuration);
    });

    // Initialize visibility
    toggleShiftFields();
});
</script>
@endsection
