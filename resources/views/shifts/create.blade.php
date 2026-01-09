@extends('layouts.admin')

@section('content')
@php
    $user = session('user'); 
    $role_id = $user['role'];
    $startOfWeek = now()->startOfWeek();
@endphp

<div class="container mt-4">
    <h4>Create Weekly Shifts</h4>

    <!-- 🔹 Tabs -->
    <ul class="nav nav-tabs" id="shiftTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="kitchen-tab" data-bs-toggle="tab"
                    data-bs-target="#kitchenTabContent" type="button" role="tab">Kitchen Department</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="care-tab" data-bs-toggle="tab"
                    data-bs-target="#careTabContent" type="button" role="tab">Care Department</button>
        </li>
    </ul>

    <!-- 🔹 Tab Content -->
    <div class="tab-content mt-3" id="shiftTabsContent">
        <div class="tab-pane fade show active" id="kitchenTabContent" role="tabpanel">
            <form id="shiftForm_kitchen">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                @for ($i = 0; $i < 7; $i++)
                                    @php $day = $startOfWeek->copy()->addDays($i); @endphp
                                    <th @if($day->isToday()) class="bg-warning text-dark" @endif>
                                        <span class="day-name">{{ $day->format('l') }}</span><br>
                                        @if($role_id == 1)
                                            <input type="date" class="form-control form-control-sm day-date"
                                                value="{{ $day->toDateString() }}" data-day-index="{{ $i }}">
                                        @else
                                            <small>{{ $day->format('d-m-Y') }}</small>
                                        @endif
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kitchenUsers as $u)
                                <tr>
                                    <td>
                                        <strong>{{ $u->name }}</strong><br>
                                        <small>{{ $u->role->name ?? '' }}</small>
                                        <input type="hidden" class="user-meta"
                                               data-id="{{ $u->id }}"
                                               data-name="{{ $u->name }}"
                                               data-role-id="{{ $u->role_id }}">
                                    </td>
                                    @for ($i = 0; $i < 7; $i++)
                                        @php $day = $startOfWeek->copy()->addDays($i); @endphp
                                        <td>
                                            <select class="form-select shift-slot mb-1">
                                                <option value="">Slot</option>
                                                <option value="Day">Day</option>
                                                <option value="Night">Night</option>
                                            </select>
                                            <select class="form-select shift-type-select mb-1"
                                                    data-user="{{ $u->id }}"
                                                    data-day-index="{{ $i }}"
                                                    data-date="{{ $day->toDateString() }}">
                                                <option value="">Shift Type</option>
                                                <option value="LD">LD</option>
                                                <option value="N">N</option>
                                                <option value="overtime">Overtime</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <input type="text" class="form-control shift-type-other d-none mb-1"
                                                   placeholder="Enter custom type">
                                            <input type="text" class="form-control shift-time d-none"
                                                   placeholder="Overtime">
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($role_id == 1)
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Submit Kitchen Shifts</button>
                    </div>
                @endif
            </form>

            <h5 class="mt-5">Existing Kitchen Availabilities</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Shift Type</th>
                        <th>Slot</th>
                        <th>Shift Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kitchenAvailabilities as $availability)
                        <tr>
                            <td>{{ $availability->user->name ?? 'No User' }}</td>
                            <td>{{ date('d-m-Y', strtotime($availability->date)) }}</td>
                            <td>{{ $availability->shift_type }}</td>
                            <td>{{ $availability->shift_slot }}</td>
                            <td>{{ $availability->shift_time ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No shifts recorded yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= CARE TAB ================= --}}
        <div class="tab-pane fade" id="careTabContent" role="tabpanel">
            <form id="shiftForm_care">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                @for ($i = 0; $i < 7; $i++)
                                    @php $day = $startOfWeek->copy()->addDays($i); @endphp
                                    <th @if($day->isToday()) class="bg-warning text-dark" @endif>
                                        <span class="day-name">{{ $day->format('l') }}</span><br>
                                        @if($role_id == 1)
                                            <input type="date" class="form-control form-control-sm day-date"
                                                value="{{ $day->toDateString() }}"
                                                data-day-index="{{ $i }}">
                                        @else
                                            <small>{{ $day->format('d-m-Y') }}</small>
                                        @endif
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($careUsers as $u)
                                <tr>
                                    <td>
                                        <strong>{{ $u->name }}</strong><br>
                                        <small>{{ $u->role->name ?? '' }}</small>
                                        <input type="hidden" class="user-meta"
                                               data-id="{{ $u->id }}"
                                               data-name="{{ $u->name }}"
                                               data-role-id="{{ $u->role_id }}">
                                    </td>
                                    @for ($i = 0; $i < 7; $i++)
                                        @php $day = $startOfWeek->copy()->addDays($i); @endphp
                                        <td>
                                            <select class="form-select shift-slot mb-1">
                                                <option value="">Slot</option>
                                                <option value="Day">Day</option>
                                                <option value="Night">Night</option>
                                            </select>
                                            <select class="form-select shift-type-select mb-1"
                                                    data-user="{{ $u->id }}"
                                                    data-day-index="{{ $i }}"
                                                    data-date="{{ $day->toDateString() }}">
                                                <option value="">Shift Type</option>
                                                <option value="LD">LD</option>
                                                <option value="N">N</option>
                                                <option value="overtime">Overtime</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <input type="text" class="form-control shift-type-other d-none mb-1"
                                                   placeholder="Enter custom type">
                                            <input type="text" class="form-control shift-time d-none"
                                                   placeholder="Overtime">
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($role_id == 1)
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Submit Care Shifts</button>
                    </div>
                @endif
            </form>

            <h5 class="mt-5">Existing Care Availabilities</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Shift Type</th>
                        <th>Slot</th>
                        <th>Shift Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($careAvailabilities as $availability)
                        <tr>
                            <td>{{ $availability->user->name ?? 'No User' }}</td>
                            <td>{{ date('d-m-Y', strtotime($availability->date)) }}</td>
                            <td>{{ $availability->shift_type }}</td>
                            <td>{{ $availability->shift_slot }}</td>
                            <td>{{ $availability->shift_time ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No shifts recorded yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table thead th {
    vertical-align: middle;
}
</style>
@endpush

@section('scripts')
<script>
const roleMap = {
    1: 'Admin',
    2: 'Normal User',
    3: 'T/Leaders',
    4: 'Seniors',
    5: 'Carers',
    6: 'Bank'
};

// 🔹 Common function to handle form submission for both tabs
function handleShiftForm(formId, sectionName) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const shifts = [];
        form.querySelectorAll('tbody tr').forEach(row => {
            const meta = row.querySelector('.user-meta');
            if (!meta) return;

            const userId = meta.dataset.id;
            const userName = meta.dataset.name;
            const roleId = meta.dataset.roleId;

            row.querySelectorAll('td').forEach(cell => {
                const select = cell.querySelector('.shift-type-select');
                if (!select) return;

                let date = select.dataset.date;
                const dayIndex = select.dataset.dayIndex;
                const dateInput = form.querySelector(`input.day-date[data-day-index="${dayIndex}"]`);
                if (dateInput) date = dateInput.value;

                const shiftType = select.value === 'Other'
                    ? (cell.querySelector('.shift-type-other')?.value || '').trim()
                    : select.value;

                const shiftSlot = cell.querySelector('.shift-slot')?.value || '';
                const shiftTime = cell.querySelector('.shift-time')?.value || '';

                if (shiftType && shiftSlot) {
                    shifts.push({
                        user_id: parseInt(userId),
                        person_name: userName,
                        role_group: roleMap[roleId] || '',
                        date,
                        shift_type: shiftType,
                        shift_slot: shiftSlot,
                        shift_time: shiftTime,
                        section: sectionName
                    });
                }
            });
        });

        if (shifts.length === 0) {
            return Swal.fire('Error', 'Please fill at least one shift row.', 'error');
        }

        fetch("{{ route('shifts.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ shifts })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Unexpected error occurred.', 'error');
            console.error(err);
        });
    });
}

// Initialize both forms
handleShiftForm('shiftForm_kitchen', 'Kitchen');
handleShiftForm('shiftForm_care', 'Care');

// Toggle Other/Overtime fields
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('shift-type-select')) {
        const cell = e.target.closest('td');
        const otherInput = cell.querySelector('.shift-type-other');
        const shiftTimeInput = cell.querySelector('.shift-time');

        if (e.target.value === 'Other') otherInput.classList.remove('d-none');
        else { otherInput.classList.add('d-none'); otherInput.value = ''; }

        if (e.target.value === 'overtime') shiftTimeInput.classList.remove('d-none');
        else { shiftTimeInput.classList.add('d-none'); shiftTimeInput.value = ''; }
    }
});

// Update day names on date change
document.querySelectorAll('.day-date').forEach(input => {
    input.addEventListener('change', function() {
        const th = this.closest('th');
        const selectedDate = new Date(this.value);
        const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
        th.querySelector('.day-name').textContent = dayName;
    });
});
</script>
@endsection
