@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h4>Create Weekly Availability</h4>

    <form id="availabilityForm">
        @csrf

        {{-- Select User --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Choose User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Select Days --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Select Days</label>
                <div class="form-check">
                    @foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                        <input type="checkbox" name="days[]" value="{{ $day }}" class="form-check-input day-check" id="day_{{ $day }}">
                        <label for="day_{{ $day }}" class="form-check-label">{{ $day }}</label><br>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Select Shift --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="shift" class="form-label">Select Shift</label>
                <select name="shift" id="shift" class="form-select" required>
                    <option value="">-- Choose Shift --</option>
                    <option value="day">Day</option>
                    <option value="night">Night</option>
                </select>
            </div>
        </div>

        {{-- Time and Day Off --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" id="start_time" class="form-control" value="10:30">
            </div>
            <div class="col-md-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" id="end_time" class="form-control">
            </div>
            <div class="col-md-3 mt-4">
                <div class="form-check">
                    <input type="checkbox" id="is_day_off" class="form-check-input">
                    <label class="form-check-label" for="is_day_off">Mark all as Day Off</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit Availability</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('user_id').addEventListener('change', function () {
    document.getElementById('submitBtn').disabled = this.value === '';
});

document.getElementById('availabilityForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const userId = document.getElementById('user_id').value;
    const shift = document.getElementById('shift').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const isDayOff = document.getElementById('is_day_off').checked;

    // Collect selected days
    const selectedDays = [];
    document.querySelectorAll('.day-check:checked').forEach(cb => {
        selectedDays.push(cb.value);
    });

    if (!userId || selectedDays.length === 0 || !shift) return;

    const entries = [{
        user_id: parseInt(userId),
        days: selectedDays,
        shift: shift,
        start_time: isDayOff ? null : (startTime ? startTime + ':00' : null),
        end_time: isDayOff ? null : (endTime ? endTime + ':00' : null),
        is_day_off: isDayOff
    }];

    fetch("{{ route('availability.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ availabilities: entries })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
            // Redirect after alert closes
            window.location.href = "{{ route('availability.index') }}";
        });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'Failed to save availability.',
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Unexpected Error',
            text: 'Something went wrong.',
        });
        console.error(err);
    });
});
</script>
@endsection
