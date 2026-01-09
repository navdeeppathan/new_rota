@extends('layouts.admin')

@section('content')
<style>
    .main-content { padding: 0px !important; }
    td[data-user] { cursor: pointer; }
    td span.ot { color: red; font-weight: bold; font-size: 12px; }
    td.day { background-color: #e7f8e7; }   /* light green */
    td.night { background-color: #e7ecf8; } /* light blue */
</style>

<div class="container mt-4">
    <div class="row">
        <h4>Weekly Shift Overview</h4>
        <form id="filterForm" class="mb-3">
            <input type="date" name="start_date" value="{{ today()->toDateString() }}" class="form-control d-inline w-auto">
            <button type="submit" class="btn btn-primary">Load Week</button>
        </form>

        {{-- Custom Tabs --}}
        <ul class="nav nav-tabs mt-4" id="shiftTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-tab="kitchenTabContent" type="button">Kitchen & Housekeeping</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link " data-tab="careTabContent" type="button">Care Department</button>
            </li>
        </ul>

        <div class="tab-content mt-3">
            {{-- Kitchen Tab --}}
            <div class="tab-pane show active" id="kitchenTabContent">
                <h5 class="mt-4">Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-success" id="dayGridHeadKitchen"></thead>
                        <tbody id="dayGridBodyKitchen"></tbody>
                    </table>
                </div>
            </div>

            {{-- Care Tab --}}
            <div class="tab-pane" id="careTabContent">
                <h5 class="mt-4">Day Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-success" id="dayGridHeadCare"></thead>
                        <tbody id="dayGridBodyCare"></tbody>
                    </table>
                </div>

                <h5 class="mt-4">Night Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-info" id="nightGridHeadCare"></thead>
                        <tbody id="nightGridBodyCare"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Legend Info --}}
        <div class="mt-5" id="legendSection" style="font-size: 14px;"></div>
    </div>
</div>

<!-- Shift Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="shiftForm">
          <input type="hidden" id="cellUserId">
          <input type="hidden" id="cellDate">

          <div class="mb-2">
            <label class="form-label">Shift Type</label>
            <select class="form-select" id="shiftType" required>
              <option value="">Select</option>
              <option value="Day">LD</option>
              <option value="Night">N</option>
            </select>
          </div>

        <div class="row g-2 mb-3">
            <div class="col">
                <label for="startTime" class="form-label">Start Time</label>
                <input type="time" id="startTime" class="form-control" required>
            </div>
            <div class="col">
                <label for="endTime" class="form-label">End Time</label>
                <input type="time" id="endTime" class="form-control" required>
            </div>
         </div>

          <div class="form-check mb-2">
            <input type="checkbox" id="isOvertime" class="form-check-input">
            <label class="form-check-label">Overtime</label>
          </div>

          <div id="overtimeFields" class="d-flex gap-2 d-none">
            <input type="number" id="otHours" class="form-control" placeholder="Hours" min="0">
            <input type="number" id="otMinutes" class="form-control" placeholder="Minutes" min="0" step="15">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveShiftBtn" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Tabs
        const tabs = document.querySelectorAll("#shiftTab .nav-link");
        const panes = document.querySelectorAll(".tab-pane");
        tabs.forEach(tab => {
            tab.addEventListener("click", function () {
                tabs.forEach(t => t.classList.remove("active"));
                panes.forEach(p => p.classList.remove("show","active"));
                this.classList.add("active");
                document.getElementById(this.dataset.tab).classList.add("show","active");
            });
        });

        // Filter form
        document.getElementById('filterForm').addEventListener('submit', e => {
            e.preventDefault();
            const date = new FormData(e.target).get('start_date');
            loadGrids(date);
        });

        // Overtime toggle
        document.getElementById('isOvertime').addEventListener('change', e => {
            document.getElementById('overtimeFields').classList.toggle('d-none', !e.target.checked);
        });

        let activeCell = null;

        // Open modal on cell click
        document.addEventListener('click', function (e) {
            if (e.target.tagName === 'TD' && e.target.dataset.user) {
                activeCell = e.target;
                document.getElementById('cellUserId').value = e.target.dataset.user;
                document.getElementById('cellDate').value = e.target.dataset.date;
                document.getElementById('shiftForm').reset();
                document.getElementById('overtimeFields').classList.add('d-none');
                new bootstrap.Modal(document.getElementById('shiftModal')).show();
            }
        });

        // Save shift
        document.getElementById('saveShiftBtn').addEventListener('click', function () {
            const data = {
                user_id: document.getElementById('cellUserId').value,
                date: document.getElementById('cellDate').value,
                shift_type: document.getElementById('shiftType').value,
                start_time: document.getElementById('startTime').value,
                end_time: document.getElementById('endTime').value,
                overtime: document.getElementById('isOvertime').checked,
                overtime_hours: document.getElementById('otHours').value,
                overtime_minutes: document.getElementById('otMinutes').value,
            };

            fetch('/api/save-shift', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;

                let display = `${data.shift_type}`;
                if (data.start_time && data.end_time) {
                    display += ` (${data.start_time}-${data.end_time})`;
                }
                if (data.overtime) {
                    display += `<br><span class="ot">OT ${data.overtime_hours}h ${data.overtime_minutes}m</span>`;
                }

                activeCell.innerHTML = display;
                activeCell.classList.remove('day','night');
                if (data.shift_type === 'Day') activeCell.classList.add('day');
                if (data.shift_type === 'Night') activeCell.classList.add('night');

                bootstrap.Modal.getInstance(document.getElementById('shiftModal')).hide();
            })
            .catch(e => alert(e.message || 'Save failed'));
        });

        // Initial load
        loadGrids(new Date().toISOString().slice(0,10));
        loadLegend();
    });

    function loadGrids(startDate) {
        fetch(`/api/weekly-shifts?start_date=${startDate}`)
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;

                const days = [];
                for (let i = 0; i < 7; i++) {
                    const d = new Date(res.start_date);
                    d.setDate(d.getDate() + i);
                    days.push(`${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`);
                }

                const columns = ['MASTER', ...days];
                const headHtml = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;

                document.getElementById('dayGridHeadKitchen').innerHTML = headHtml;
                document.getElementById('dayGridHeadCare').innerHTML = headHtml;
                document.getElementById('nightGridHeadCare').innerHTML = headHtml;

                // Example: you must adapt roleMap to your data
                const roleMap = {3:'T/Leaders',4:'Seniors',5:'Carers',6:'Bank'};

                buildGrid(res.kitchen_housekeeping_grid, 'Kitchen', roleMap, columns, true);
                buildGrid(res.care_grid, 'Care', roleMap, columns, false);
            })
            .catch(e => alert(e.message || 'Error loading shifts'));
    }
    function buildGrid(gridData, prefix, roleMap, columns, kitchenOnlyDay = false) {
        const dayRows = [], nightRows = [];

        Object.values(roleMap).forEach(label => {
            dayRows.push(`<tr class="bg-light fw-bold text-start"><td colspan="${columns.length+1}">${label}</td></tr>`);
            if (!kitchenOnlyDay) {
                nightRows.push(`<tr class="bg-light fw-bold text-start"><td colspan="${columns.length+1}">${label}</td></tr>`);
            }
        });

        gridData.forEach(row => {
            const sectionLabel = roleMap[row.role_id] || '';

            // ---- Day cells ----
            const dayCells = columns.slice(1).map((date, idx) => {
                const slot = row.slots[idx] || null;
                let display = '';

                if (slot && slot.slot === 'Day') {
                    display = `${slot.type}`;
                    if (slot.time) {
                        display += ` (${slot.time})`;
                    }
                    if (slot.overtime) {
                        let otH = slot.overtime_hours || 0;
                        let otM = slot.overtime_minutes || 0;
                        display += `<br><span class="ot">OT ${otH}h ${otM}m</span>`;
                    }
                }

                return `<td data-user="${row.user_id}" data-date="${date}" class="clickable">${display}</td>`;
            });

            const idx = dayRows.findIndex(r => r.includes(sectionLabel));
            dayRows.splice(idx + 1, 0, `
                <tr>
                    <td>
                        <button class="btn btn-sm btn-success clone-user" data-user="${row.user_id}">
                            Clone →
                        </button>
                        ${row.name}
                        ${dayCells.join('')}
                    </td>
                
                </tr>
            `);

            // ---- Night cells ----
            if (!kitchenOnlyDay) {
                const nightCells = columns.slice(1).map((date, idx) => {
                    const slot = row.slots[idx] || null;
                    let display = '';

                    if (slot && slot.slot === 'Night') {
                        display = `${slot.type}`;
                        if (slot.time) {
                            display += ` (${slot.time})`;
                        }
                        if (slot.overtime) {
                            display += `<br><span class="ot">OT ${slot.overtime_hours || 0}h ${slot.overtime_minutes || 0}m</span>`;
                        }
                    }

                    return `<td data-user="${row.user_id}" data-date="${date}" class="clickable">${display}</td>`;
                });

                const idxN = nightRows.findIndex(r => r.includes(sectionLabel));
                nightRows.splice(idxN + 1, 0, `
                    <tr>
                        <td>
                            <button class="btn btn-sm btn-success clone-user" data-user="${row.user_id}">
                                Clone →
                            </button>
                            ${row.name}
                        ${nightCells.join('')}
                        </td>
                        
                    </tr>
                `);
            }
        });

        document.getElementById(`dayGridBody${prefix}`).innerHTML = dayRows.join('');
        if (!kitchenOnlyDay) {
            document.getElementById(`nightGridBody${prefix}`).innerHTML = nightRows.join('');
        }
    }


    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('clone-user')) {
            const userId = e.target.dataset.user;
            const startDate = document.querySelector('[name="start_date"]').value;

            if (!confirm("Clone this user’s shifts to the next 7 days?")) return;

            fetch('/api/clone-user-week', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ user_id: userId, start_date: startDate })
            })
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;
                alert("✅ User’s week cloned successfully!");
                loadGrids(startDate);
            })
            .catch(e => alert(e.message || "Clone failed"));
        }
    });

    function loadLegend() {
        fetch('/api/shift-legend')
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;
                const { day_shift, night_shift, leaves } = res.data;
                let html = `
                    <div class="row">
                        <div class="col-md-4">
                            <strong style="color:red;">${day_shift.note}</strong><br>
                            <span style="color:purple;">*${day_shift.break} break</span>
                        </div>
                        <div class="col-md-4">
                            ${leaves.map(l => `<span style="color:red;"><strong>${l.code}</strong></span> - ${l.name}<br>`).join('')}
                        </div>
                        <div class="col-md-4">
                            <span><strong>LD =</strong> ${day_shift.time}</span><br>
                            <span><strong>N1 =</strong> 08:15 AM - 07:30 PM</span><br>
                            <span><strong>N2 =</strong> 08:30 AM - 07:00 PM</span>
                        </div>
                    </div>
                `;
                document.getElementById('legendSection').innerHTML = html;
            })
            .catch(e => console.error('Failed to load shift legend', e));
    }
</script>
@endsection
