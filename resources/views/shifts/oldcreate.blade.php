@extends('layouts.admin')

@section('content')
@php
$user = session('user'); 
$role_id = $user['role'];
@endphp
<div class="container mt-4">
    <h4>Create Weekly Shifts</h4>

    <form id="shiftForm">
        @csrf

        <!-- ✅ Users as checkboxes -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Select User(s)</label>

                <!-- Select All -->
                <div class="form-check mb-2">
                    <input type="checkbox" id="select-all" class="form-check-input">
                    <label for="select-all" class="form-check-label fw-bold">Select All Users</label>
                </div>

                <!-- Individual Users -->
                <div class="row">
                    @foreach ($users as $user)
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input user-checkbox"
                                       name="user_ids[]"
                                       value="{{ $user->id }}"
                                       data-role-id="{{ $user->role_id }}"
                                       data-name="{{ $user->name }}">
                                <label class="form-check-label">{{ $user->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Shift rows -->
        <div id="shiftRows"></div>
        @if($role_id == 1)
            <button type="button" class="btn btn-outline-primary mb-3" id="addRow">Add 1+</button>
            <button type="button" class="btn btn-outline-secondary mb-3" id="addNextWeek">Add More Week</button>
            <br>
    
            <button type="submit" class="btn btn-primary" id="submitBtn">Submit Shifts</button>
        @endif
    </form>

    <!-- Availability Table -->
    <table id="example" class="table mt-4">
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Date</th>
                <th scope="col">Shift Type</th>
                <th scope="col">Slot</th>
                <th scope="col">Shift Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($availabilities as $availability)
                <tr>
                    <td>{{ $availability->user->name ?? 'No User' }}</td>
                    <td>{{ date('d-m-Y', strtotime($availability->date)) }}</td>
                    <td>{{ $availability->shift_type }}</td>
                    <td>{{ $availability->shift_slot }}</td>
                    <td>{{ $availability->shift_time ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
let shiftCounter = 0;

// ✅ Create shift row
function createShiftRow(date = '', selectedType = '', customType = '', slot = '', time = '') {
    const container = document.getElementById('shiftRows');
    const row = document.createElement('div');
    row.className = 'row mb-2 shift-row';
    row.dataset.row = shiftCounter;

    row.innerHTML = `
        <div class="col-md-2">
            <input type="date" class="form-control shift-date" value="${date}" required>
        </div>
        <div class="col-md-2">
            <select class="form-select shift-type-select" required>
                <option value="LD" ${selectedType === 'LD' ? 'selected' : ''}>LD</option>
                <option value="N" ${selectedType === 'N' ? 'selected' : ''}>N</option>
                <option value="overtime" ${selectedType === 'overtime' ? 'selected' : ''}>Overtime</option>
                <option value="Other" ${selectedType === 'Other' ? 'selected' : ''}>Other</option>
            </select>
            <input type="text" class="form-control mt-2 shift-type-other ${selectedType === 'Other' ? '' : 'd-none'}" 
                   placeholder="Enter custom shift type" value="${customType}">
        </div>
        <div class="col-md-2">
            <select class="form-select shift-slot" required>
                <option value="">Select Slot</option>
                <option value="Day" ${slot === 'Day' ? 'selected' : ''}>Day 07:15AM - 08:30PM</option>
                <option value="Night" ${slot === 'Night' ? 'selected' : ''}>Night 08:15PM - 07:30AM</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control shift-time ${selectedType === 'overtime' ? '' : 'd-none'}" placeholder="Enter Overtime" value="${time}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger removeRow">✖</button>
        </div>
    `;
    container.appendChild(row);
    shiftCounter++;
}

// ✅ Generate first week
function generateInitialDates(count = 7) {
    const today = new Date();
    for (let i = 0; i < count; i++) {
        const date = new Date(today);
        date.setDate(date.getDate() + i);
        createShiftRow(date.toISOString().slice(0, 10));
    }
}
generateInitialDates();

// ✅ Add new row
document.getElementById('addRow').addEventListener('click', () => {
    createShiftRow();
});

// ✅ Toggle Other / Overtime fields
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('shift-type-select')) {
        const row = e.target.closest('.shift-row');
        const otherInput = row.querySelector('.shift-type-other');
        const shiftTimeInput = row.querySelector('.shift-time');

        // Toggle "Other" field
        if (e.target.value === 'Other') {
            otherInput.classList.remove('d-none');
            otherInput.required = true;
        } else {
            otherInput.classList.add('d-none');
            otherInput.required = false;
            otherInput.value = '';
        }

        // Toggle "Overtime" shift-time field
        if (e.target.value === 'overtime') {
            shiftTimeInput.classList.remove('d-none');
            shiftTimeInput.required = true;
        } else {
            shiftTimeInput.classList.add('d-none');
            shiftTimeInput.required = false;
            shiftTimeInput.value = '';
        }
    }
});

// ✅ Remove row
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('removeRow')) {
        e.target.closest('.shift-row').remove();
    }
});

// ✅ Select All toggle
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});

// ✅ Submit form
document.getElementById('shiftForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Collect checked users
    let selectedUsers = [];
    document.querySelectorAll('.user-checkbox:checked').forEach(cb => {
        selectedUsers.push(cb.value);
    });

    if (selectedUsers.length === 0) {
        return Swal.fire('Error', 'Please select at least one user', 'error');
    }

    const shifts = [];
    const roleMap = {
        1: 'Admin',
        2: 'Normal User',
        3: 'T/Leaders',
        4: 'Seniors',
        5: 'Carers',
        6: 'Bank'
    };

    document.querySelectorAll('.shift-row').forEach(row => {
        const date = row.querySelector('.shift-date').value;
        const select = row.querySelector('.shift-type-select');
        const otherInput = row.querySelector('.shift-type-other');
        const shiftSlot = row.querySelector('.shift-slot').value;
        const shiftTime = row.querySelector('.shift-time').value;

        const shiftType = select.value === 'Other' ? otherInput.value.trim() : select.value;

        if (date && shiftType && shiftSlot) {
            selectedUsers.forEach(uid => {
                const cb = document.querySelector(`.user-checkbox[value="${uid}"]`);
                const userName = cb.getAttribute("data-name");
                const roleId = cb.getAttribute("data-role-id");

                shifts.push({
                    user_id: parseInt(uid),
                    person_name: userName,
                    role_group: roleMap[roleId] || '',
                    date,
                    shift_type: shiftType,
                    shift_slot: shiftSlot,
                    shift_time: shiftTime
                });
            });
        }
    });

    if (shifts.length === 0) {
        return Swal.fire('Error', 'Please fill at least one shift row correctly.', 'error');
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

// ✅ Add next week
document.getElementById('addNextWeek').addEventListener('click', function () {
    document.querySelectorAll('.shift-row').forEach(row => {
        const dateInput = row.querySelector('.shift-date');
        const select = row.querySelector('.shift-type-select');
        const otherInput = row.querySelector('.shift-type-other');
        const shiftSlot = row.querySelector('.shift-slot');
        const shiftTime = row.querySelector('.shift-time').value;

        const currentDate = new Date(dateInput.value);
        currentDate.setDate(currentDate.getDate() + 7);
        const newDateStr = currentDate.toISOString().slice(0, 10);

        createShiftRow(
            newDateStr,
            select.value,
            select.value === 'Other' ? otherInput.value : '',
            shiftSlot.value,
            shiftTime
        );
    });
});
</script>
@endsection
