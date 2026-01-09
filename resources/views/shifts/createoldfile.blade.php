@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h4>Create Weekly Shifts</h4>

    <form id="shiftForm">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Choose User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-role-id="{{ $user->role_id }}" data-name="{{ $user->name }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <input type="text" id="person_name" class="form-control mb-2" placeholder="Person Name" readonly>
            <input type="text" id="role_group" class="form-control" placeholder="Role Group" readonly>
        </div>

        <div id="shiftRows"></div>

        <button type="button" class="btn btn-outline-primary mb-3" id="addRow">Add 1+</button>
        <button type="button" class="btn btn-outline-secondary mb-3" id="addNextWeek">Add More Week</button>
        <br>

        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit Shifts</button>
    </form>

    <table id="example" class="table">
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Date</th>
                <th scope="col">Availability Time</th>
                <th scope="col">Day Off</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($availabilities as $availability)
                <tr>
                    <td>{{ $availability->user->name ?? 'No User' }}</td>
                    <td>{{ date('d-m-Y', strtotime($availability->date)) }} </td>
                    <td>{{ date('h:i A', strtotime($availability->start_time)) }} - {{ date('h:i A', strtotime($availability->end_time)) }} </td>
                    <td>{{ $availability->is_day_off ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    let shiftCounter = 0;

    // Create shift row with select and custom input
    function createShiftRow(date = '', selectedType = '', customType = '', slot = '') {
        const container = document.getElementById('shiftRows');
        const row = document.createElement('div');
        row.className = 'row mb-2 shift-row';
        row.dataset.row = shiftCounter;

        row.innerHTML = `
            <div class="col-md-2">
                <input type="date" class="form-control shift-date" value="${date}" required>
            </div>
            <div class="col-md-3">
                <select class="form-select shift-type-select" required>
                    <option value="LD" ${selectedType === 'LD' ? 'selected' : ''}>LD</option>
                    <option value="N" ${selectedType === 'N' ? 'selected' : ''}>N</option>
                    <option value="Other" ${selectedType === 'Other' ? 'selected' : ''}>Other</option>
                </select>
                <input type="text" class="form-control mt-2 shift-type-other ${selectedType === 'Other' ? '' : 'd-none'}" 
                       placeholder="Enter custom shift type" value="${customType}">
            </div>
            <div class="col-md-2">
                <select class="form-select shift-slot" required>
                    <option value="">Select Slot</option>
                    <option value="Day" ${slot === 'Day' ? 'selected' : ''}>Day</option>
                    <option value="Night" ${slot === 'Night' ? 'selected' : ''}>Night</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger removeRow">✖</button>
            </div>
        `;
        container.appendChild(row);
        shiftCounter++;
    }

    function generateInitialDates(count = 7) {
        const today = new Date();
        for (let i = 0; i < count; i++) {
            const date = new Date(today);
            date.setDate(date.getDate() + i);
            createShiftRow(date.toISOString().slice(0, 10));
        }
    }

    // Initial rows
    generateInitialDates();

    document.getElementById('addRow').addEventListener('click', () => {
        createShiftRow();
    });

    // Toggle Other input field
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('shift-type-select')) {
            const otherInput = e.target.parentElement.querySelector('.shift-type-other');
            if (e.target.value === 'Other') {
                otherInput.classList.remove('d-none');
                otherInput.required = true;
            } else {
                otherInput.classList.add('d-none');
                otherInput.required = false;
                otherInput.value = '';
            }
        }
    });

    // Remove row
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            e.target.closest('.shift-row').remove();
        }
    });

    // Handle user select
    document.getElementById('user_id').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const roleId = selectedOption.getAttribute('data-role-id');
        const userName = selectedOption.getAttribute('data-name');

        const roleMap = {
            1: 'Admin',
            2: 'Normal User',
            3: 'T/Leaders',
            4: 'Seniors',
            5: 'Carers',
            6: 'Bank'
        };

        document.getElementById('role_group').value = roleMap[roleId] || '';
        document.getElementById('person_name').value = userName || '';
        document.getElementById('submitBtn').disabled = !roleId;
    });

    // Submit form
    document.getElementById('shiftForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const userId = document.getElementById('user_id').value;
        const personName = document.getElementById('person_name').value.trim();
        const roleGroup = document.getElementById('role_group').value.trim();

        if (!userId || !personName) {
            return Swal.fire('Error', 'User and Person Name are required', 'error');
        }

        const shifts = [];

        document.querySelectorAll('.shift-row').forEach(row => {
            const date = row.querySelector('.shift-date').value;
            const select = row.querySelector('.shift-type-select');
            const otherInput = row.querySelector('.shift-type-other');
            const shiftSlot = row.querySelector('.shift-slot').value;

            const shiftType = select.value === 'Other' ? otherInput.value.trim() : select.value;

            if (date && shiftType && shiftSlot) {
                shifts.push({
                    user_id: parseInt(userId),
                    person_name: personName,
                    role_group: roleGroup,
                    date,
                    shift_type: shiftType,
                    shift_slot: shiftSlot
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

    // Add next week button
    document.getElementById('addNextWeek').addEventListener('click', function () {
        document.querySelectorAll('.shift-row').forEach(row => {
            const dateInput = row.querySelector('.shift-date');
            const select = row.querySelector('.shift-type-select');
            const otherInput = row.querySelector('.shift-type-other');
            const shiftSlot = row.querySelector('.shift-slot');

            const currentDate = new Date(dateInput.value);
            currentDate.setDate(currentDate.getDate() + 7);
            const newDateStr = currentDate.toISOString().slice(0, 10);

            createShiftRow(
                newDateStr,
                select.value,
                select.value === 'Other' ? otherInput.value : '',
                shiftSlot.value
            );
        });
    });
</script>
@endsection
