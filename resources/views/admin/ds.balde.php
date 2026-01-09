@extends('layouts.admin')

@section('content')
<style>
    .main-content { padding: 0px !important; }
    td[data-user] { cursor: pointer; }
    td span.ot { color: #47f147ff; font-weight: bold; font-size: 12px; }
    td.day { background-color: #e7f8e7; }   /* light green */
    td.night { background-color: #e7ecf8; }
    .hide-ld {
        display: none !important;
    }
</style>
@php
    $user = session('user'); 
    $role_id = $user['role'];
@endphp

<div class="container mt-4">
    <div class="row">
        <h4>Weekly Shift Overview</h4>

        {{-- Month & Week Selector --}}
        <form id="filterForm" class="mb-3">
            <select name="month" id="monthSelect" class="form-select d-inline w-auto">
                <option value="">Select Month</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
            <select name="week" id="weekSelect" class="form-select d-inline w-auto" style="display:none;"></select>
            <button type="submit" class="btn btn-primary" style="display:none;" id="viewWeekBtn">View Shifts for Week</button>
            <button type="button" class="btn btn-success" style="display:none;" id="publishWeekBtn">Publish Week</button>

        </form>


        {{-- Clone Week --}}
        <div class="mt-3 mb-3 p-2 col-md-5" style="padding: 10px; margin-left: 11px; border: 2px solid;">
            <h6>Clone Shifts to Another Week</h6>
            <form id="cloneWeekForm" class="row g-2">
                <div class="col-auto">
                    <select id="sourceWeek" class="form-select" required>
                        <option value="">Source Week</option>
                        {{-- weeks will be loaded dynamically --}}
                    </select>
                </div>
                <div class="col-auto">
                    <select id="targetWeek" class="form-select" required>
                        <option value="">Target Week</option>
                        {{-- weeks will be loaded dynamically --}}
                    </select>
                </div>

                <div class="col-12 mt-2">
                    <label class="fw-bold">Clone Type:</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cloneType" value="all" checked>
                        <label class="form-check-label">All Staff</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cloneType" value="kitchen">
                        <label class="form-check-label">Kitchen Staff</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cloneType" value="care_day">
                        <label class="form-check-label">Care Day Staff</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cloneType" value="care_night">
                        <label class="form-check-label">Care Night Staff</label>
                    </div>
                </div>

                <div class="col-auto mt-2">
                    <button type="submit" class="btn btn-warning">Clone Week</button>
                </div>
            </form>
        </div>

        {{-- Tabs --}}
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
            <!-- <tbody id="dayGridBodyKitchen"></tbody>
                <tbody id="dayGridBodyCareDay"></tbody>
                <tbody id="dayGridBodyCareNight"></tbody>-->
            {{-- Care Tab --}}
            <div class="tab-pane" id="careTabContent">
                <h5 class="mt-4">Day Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-success" id="dayGridHeadCare"></thead>
                        <tbody id="dayGridBodyCareDay"></tbody>
                    </table>
                </div>

                <h5 class="mt-4">Night Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-info" id="nightGridHeadCare"></thead>
                        <tbody id="dayGridBodyCareNight"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Legend --}}
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
            <label class="form-label">Select Shift Type</label>
            <select class="form-select" id="shiftType" required>
                <option value="Day">LD</option>
                <option value="Night">N</option>
                <option value="Day">A/L</option>
                <option value="Night">S</option>
                <option value="Night">CL</option>
                <option value="Night">UL</option>
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
          <div class="col">
            <label for="msg"> Message</lable>
            <input type="text" id="msg" class="form-control" >
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
    const ROLE_ID = {{ $role_id }};
</script>
<script>
    
    document.addEventListener("DOMContentLoaded", function() {
    
     fetch('/api/weeks')
            .then(r => r.json())
            .then(res => {
                if (!res.status) return;

                const sourceWeek = document.getElementById('sourceWeek');
                const targetWeek = document.getElementById('targetWeek');

                Object.entries(res.weeks).forEach(([month, weeks]) => {
                    const optGroup1 = document.createElement('optgroup');
                    optGroup1.label = month;
                    const optGroup2 = document.createElement('optgroup');
                    optGroup2.label = month;

                    weeks.forEach(week => {
                        const opt1 = new Option(week.label, week.value);
                        const opt2 = new Option(week.label, week.value);
                        optGroup1.appendChild(opt1);
                        optGroup2.appendChild(opt2);
                    });

                    sourceWeek.appendChild(optGroup1);
                    targetWeek.appendChild(optGroup2);
                });
            });
    });

    function populateCloneWeeks(month) {
        const year = new Date().getFullYear();
        const weeks = getWeeksInMonth(year, month);

        const sourceWeek = document.getElementById('sourceWeek');
        const targetWeek = document.getElementById('targetWeek');

        sourceWeek.innerHTML = '<option value="">Source Week</option>';
        targetWeek.innerHTML = '<option value="">Target Week</option>';

        weeks.forEach((w, idx) => {
            const optionText = `Week ${idx + 1}: ${w.start} - ${w.end}`;
            const optionValue = `${w.start}|${w.end}`;
            sourceWeek.append(new Option(optionText, optionValue));
            targetWeek.append(new Option(optionText, optionValue));
        });
    }

    publishWeekBtn.addEventListener('click', function() {
        const [start, end] = weekSelect.value.split('|');
        if (!start || !end) return alert("Please select a week.");

        if (!confirm(`Are you sure you want to publish shifts for ${start} → ${end}?`)) return;

        fetch(`/api/publish-week`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ start_date: start, end_date: end })
        })
        .then(res => res.json())
        .then(data => {
              alert(data.message || "Week published successfully!");
            const savedWeek = localStorage.getItem('selectedWeek');
            if (savedWeek) {
                const [start] = savedWeek.split('|');
                loadGrids(start);
            } else {
                loadGrids(date);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Something went wrong while publishing!");
        });
    });
    monthSelect.addEventListener('change', function() {
        const month = this.value;
        if (!month) return;
        // populateCloneWeeks(month);
    });


    document.getElementById('cloneWeekForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const source = document.getElementById('sourceWeek').value;
        const target = document.getElementById('targetWeek').value;
        const cloneType = document.querySelector('input[name="cloneType"]:checked').value;

        if (!source || !target) return alert('Select both source and target week');

        const confirmClone = confirm("Are you sure? Target week shifts will be overridden.");
        if (!confirmClone) return;

        fetch('/api/clone-week', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ 
                source_week: source, 
                target_week: target, 
                type: cloneType 
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.status){
                alert('✅ Week cloned successfully!');
            } else {
                alert(res.message || '❌ Failed to clone week');
            }
        })
        .catch(e => alert(e.message || '❌ Error cloning week'));
    });


    function updateShiftTypeOptions(activeTab) {
        const shiftSelect = document.getElementById("shiftType");
        shiftSelect.innerHTML = ""; // Clear existing options

        let options = [];

        if (activeTab === "careTabContent") {
            // CARE rota → LD, N, A/L, S, CL, UL
            options = ["LD", "N","E","L", "A/L", "S", "CL", "UL"];
        } else if (activeTab === "kitchenTabContent" || activeTab === "housekeepingTabContent") {
            // KITCHEN & HOUSEKEEPING rota → A/L, S, CL, UL
            options = ["A/L","IN", "S", "CL", "UL"];
        }

        options.forEach(opt => {
            const option = document.createElement("option");
            option.value = opt;
            option.textContent = opt;
            shiftSelect.appendChild(option);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        
        // --- Load legend ---
        function loadLegend() {
            fetch('/api/shift-legend')
                .then(r => r.json())
                .then(res => {
                    if (!res.status) throw res;
                    const { day_shift, night_shift, leaves } = res.data;

                    document.getElementById('legendSection').innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <strong style="color:red;"  class="night-legend ">${day_shift.note}</strong><br>
                                <span style="color:purple;"  class="night-legend">*${day_shift.break} break</span>
                            </div>
                            <div class="col-md-4">
                                ${leaves.map(l=>`<span style="color:red;"><strong>${l.code}</strong></span> - ${l.name}<br>`).join('')}
                            </div>
                            <div class="col-md-4">
                                <span class="night-legend"><strong>LD =</strong> 07:15 AM - 08:30 PM</span><br>
                                <span class="night-legend"><strong>N1 = </strong>08:15 PM - 07:30 AM</span><br>
                                <span class="night-legend"><strong>N2 =</strong> 08:30 PM - 07:00 AM</span>
                            </div>
                        </div>
                    `;

                    // Initially hide N1 & N2 for Kitchen
                    document.querySelectorAll('.night-legend').forEach(el => el.classList.remove('hide-ld'));
                })
                .catch(e => console.error('Failed to load shift legend', e));
        }
        document.querySelectorAll('.night-legend').forEach(el => el.classList.add('hide-ld'));
        // --- Tabs logic ---
        const tabs = document.querySelectorAll("#shiftTab .nav-link");
        const panes = document.querySelectorAll(".tab-pane");
        const legend = document.getElementById("legendSection");

        // tabs.forEach(tab => {
        //     tab.addEventListener("click", function() {
        //         tabs.forEach(t => t.classList.remove("active"));
        //         panes.forEach(p => p.classList.remove("show","active"));
        //         this.classList.add("active");
        //         document.getElementById(this.dataset.tab).classList.add("show","active");
        //         // --- Toggle legend visibility (only legend, no table changes) ---
        //         if(this.dataset.tab === 'careTabContent'){
        //             // CARE → show LD + N1 + N2
        //             legend.querySelectorAll('.night-legend')
        //                 .forEach(el => el.classList.remove('d-none'));
        //         }
        //         else if(this.dataset.tab === 'kitchenTabContent'){
        //             // KKCIHED → show only LD
        //             legend.querySelectorAll('.day-legend')
        //                 .forEach(el => el.classList.remove('d-none'));
        //             legend.querySelectorAll('.night-legend')
        //                 .forEach(el => el.classList.add('d-none'));
        //         }
        //         else {
        //             // Other tabs → hide all
        //             legend.querySelectorAll('.night-legend')
        //                 .forEach(el => el.classList.add('d-none'));
        //         }
        //     });
        // });
                
        // Attach to tab clicks
        tabs.forEach(tab => {
            tab.addEventListener("click", function() {
                tabs.forEach(t => t.classList.remove("active"));
                panes.forEach(p => p.classList.remove("show","active"));
                this.classList.add("active");
                document.getElementById(this.dataset.tab).classList.add("show","active");

                // Update shift type list when switching tabs
                updateShiftTypeOptions(this.dataset.tab);

                // Adjust legend visibility (your existing logic)
                if (this.dataset.tab === "careTabContent") {
                    legend.querySelectorAll(".night-legend").forEach(el => el.classList.remove("d-none"));
                } else if (this.dataset.tab === "kitchenTabContent" || this.dataset.tab === "housekeepingTabContent") {
                    legend.querySelectorAll(".night-legend").forEach(el => el.classList.add("d-none"));
                } else {
                    legend.querySelectorAll(".night-legend").forEach(el => el.classList.add("d-none"));
                }
            });
        });

        updateShiftTypeOptions("careTabContent");
        loadLegend();


        // --- Month & Week Selection ---
        const monthSelect = document.getElementById('monthSelect');
        const weekSelect = document.getElementById('weekSelect');
        const viewWeekBtn = document.getElementById('viewWeekBtn');

        // monthSelect.addEventListener('change', function () {
        //     const month = this.value;
        //     if (!month) {
        //         weekSelect.style.display = 'none';
        //         viewWeekBtn.style.display = 'none';
        //         return;
        //     }

        //     const year = new Date().getFullYear();
        //     const weeks = getWeeksInMonth(year, month);

        //     weekSelect.innerHTML = '';
        //     weeks.forEach((w, idx) => {
        //         const option = document.createElement('option');
        //         option.value = `${w.start}|${w.end}`;
        //         option.textContent = `Week ${idx+1}: ${w.start} - ${w.end}`;
        //         weekSelect.appendChild(option);
        //     });

        //     weekSelect.style.display = 'inline-block';
        //     viewWeekBtn.style.display = 'inline-block';
        // });
         monthSelect.addEventListener('change', function () {
            const month = this.value;
            if (!month) {
                weekSelect.style.display = 'none';
                viewWeekBtn.style.display = 'none';
                return;
            }

            const year = new Date().getFullYear();
            const weeks = getWeeksInMonth(year, month);

            // Helper function to format date as dd/mm/yy
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = String(date.getFullYear()).slice(-2);
                return `${day}-${month}-${year}`;
            }

            weekSelect.innerHTML = '';
            weeks.forEach((w, idx) => {
                const start = formatDate(w.start);
                const end = formatDate(w.end);
                const option = document.createElement('option');
                option.value = `${w.start}|${w.end}`; // keep full ISO for data use
                option.textContent = `Week ${idx + 1}: ${start} - ${end}`;
                weekSelect.appendChild(option);
            });

            weekSelect.style.display = 'inline-block';
            viewWeekBtn.style.display = 'inline-block';
            publishWeekBtn.style.display = 'inline-block';
        });

        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const week = weekSelect.value;
            // if (!week) return alert('Please select a week');
            const month = monthSelect.value;
            const weekRange = weekSelect.value;
            if (!month || !weekRange) {
                alert("Please select both month and week");
                return;
            }
            const [start, end] = week.split('|');
            if (!start || !end) return alert('Invalid week selected');

            loadGrids(start, end);
               // Save selection to localStorage
            localStorage.setItem('selectedMonth', month);
            localStorage.setItem('selectedWeek', weekRange);
        });

        // --- Overtime toggle ---
        document.getElementById('isOvertime').addEventListener('change', e => {
            document.getElementById('overtimeFields').classList.toggle('d-none', !e.target.checked);
        });

        // --- Shift modal click ---
        let activeCell = null;
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

        // --- Save shift ---
        document.getElementById('saveShiftBtn').addEventListener('click', function () {
            const userId   = document.getElementById('cellUserId').value;
            const date     = document.getElementById('cellDate').value;
            const shift    = document.getElementById('shiftType').value;
            const start    = document.getElementById('startTime').value;
            const end      = document.getElementById('endTime').value;
            const overtime = document.getElementById('isOvertime').checked;
            const otHours  = document.getElementById('otHours').value;
            const otMins   = document.getElementById('otMinutes').value;
            const msg      = document.getElementById('msg').value;
            

            // ---- Validation ----
            if (!userId || !date || !shift || !start || !end) {
                alert("All fields are required.");
                return;
            }

            if (overtime) {
                if (otHours === "" && otMins === "") {
                    alert("Please enter overtime hours or minutes.");
                    return;
                }
            }

            const data = {
                user_id: userId,
                date: date,
                shift_type: shift,
                start_time: start,
                end_time: end,
                overtime: overtime,
                overtime_hours: otHours,
                overtime_minutes: otMins,
                 msg: msg
            };

            fetch('/api/save-shift', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;

                let display = `${shift}`;
                if (start && end) display += ` (${start}-${end})`;
                if (overtime) display += `<br><span class="ot">OT ${otHours || 0}h ${otMins || 0}m</span>`;

                activeCell.innerHTML = display;
                activeCell.classList.remove('day','night');
                if (shift === 'Day') activeCell.classList.add('day');
                if (shift === 'Night') activeCell.classList.add('night');

                bootstrap.Modal.getInstance(document.getElementById('shiftModal')).hide();
                const savedWeek = localStorage.getItem('selectedWeek');
                if (savedWeek) {
                    alert('✅ Shift Saved successfully!');
                    const [start] = savedWeek.split('|');
                    loadGrids(start);
                } else {
                     alert('✅ Shift Saved successfully!');
                    loadGrids(date);
                }
            })
            .catch(e => alert(e.message || 'Save failed'));
        });

        // --- Load legend ---
        // loadLegend();

    });

    // --- Helper: Weeks in month ---
    function getWeeksInMonth(year, month) {
        const weeks = [];
        const firstDay = new Date(year, month-1, 1);
        const lastDay = new Date(year, month, 0);
        let start = new Date(firstDay);

        while (start <= lastDay) {
            let end = new Date(start);
            end.setDate(end.getDate() + 6);
            if (end > lastDay) end = new Date(lastDay);

            weeks.push({ start: formatDate(start), end: formatDate(end) });
            start.setDate(end.getDate() + 1);
        }

        return weeks;
    }

    function formatDate(d) {
        return `${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`;
    }

    // --- Load grids ---
    function loadGrids(startDate, endDate = null) {
          
        let url = `/api/weekly-shifts?start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;

                const days = [];
                const sDate = new Date(res.start_date);
                for (let i = 0; i < 7; i++) {
                    const d = new Date(sDate);
                    d.setDate(d.getDate() + i);
                    days.push(`${d.getDate().toString().padStart(2,'0')}-${(d.getMonth()+1).toString().padStart(2,'0')}-${d.getFullYear()}`);
                }

                const columns = ['MASTER', ...days];
                const headHtml = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;

                document.getElementById('dayGridHeadKitchen').innerHTML = headHtml;
                document.getElementById('dayGridHeadCare').innerHTML = headHtml;
                document.getElementById('nightGridHeadCare').innerHTML = headHtml;

                // Kitchen (day only)
                const kitchenRoles = {
                    7: 'Kitchen Manager',
                    8: 'Cooks',
                    9: 'Cook/Asst.',
                    10: 'Cleaners',
                    11: 'Laundry',
                    6: 'Bank'
                };

                // Care (day)
                const careDayRoles = {
                    3: 'T/Leaders',
                    4: 'Seniors',
                    5: 'Carers',
                    6: 'Bank'
                };

                // Care (night)
                const careNightRoles = {
                    4: 'Seniors',
                    5: 'Carers',
                    6: 'Bank'
                };
                buildGrid(res.kitchen_housekeeping_grid, 'Kitchen', kitchenRoles, columns, true);
                buildGrid(res.care_grid, 'CareDay', careDayRoles, columns, false, 'Day');
                buildGrid(res.care_grid, 'CareNight', careNightRoles, columns, false, 'Night')
            })
            .catch(e => alert(e.message || 'Error loading shifts'));
    }

    function buildGrid(gridData, prefix, roleMap, columns, kitchenOnlyDay = false, slotType = null) {
        const rows = [];

        let roleEntries = Object.entries(roleMap);

        // keep Bank at bottom
        roleEntries = roleEntries.filter(([id]) => id != 6).concat(roleEntries.filter(([id]) => id == 6));

        // Headers by role
        roleEntries.forEach(([id, label]) => {
            rows.push(`<tr class="bg-light fw-bold text-start"><td colspan="${columns.length+1}">${label}</td></tr>`);
        });

        gridData.forEach(row => {
            const sectionLabel = roleMap[row.role_id] || '';
            const cells = columns.slice(1).map((date, idx) => {
                const slot = row.slots[idx] || null;
                let display = '';

                if (slot && (!slotType || slot.slot === slotType)) {
                    
                    display = `${slot.type}${slot.time ? ` (${slot.time})` : ''}`;
                    if (slot.overtime) {
                        display += `<br><span class="ot">OT ${slot.overtime_hours||0}h ${slot.overtime_minutes||0}m</span>`;
                    }
                    if (ROLE_ID !== 0) {
                        display += `<br><a href="javascript:void(0)" class="delete-shift" data-user="${row.user_id}" data-date="${date}">Delete</a>`;
                    } else {
                        display += `<br><a href="javascript:void(0)" class="delete-shift" style="display:none;" data-user="${row.user_id}" data-date="${date}">Delete</a>`;
                    }
                    if (slot.msg) {
                        display += `<br><span class="publish text-success">${slot.msg}</span>`;
                    }
                    if (slot.status == '1') {
                        display += `<br><span class="publish text-success">Published</span>`;
                    }

                    // Add delete button
                    // display += `<br><a href="javascript:void(0)" class="delete-shift" data-user="${row.user_id}" data-date="${date}">Delete</a>`;

                    // display += `<br><button class="btn btn-sm btn-danger delete-shift" data-user="${row.user_id}" data-date="${date}">Delete</button>`;
                }

                // let tdClass = "clickable";
                // if (slot && slot.leave_request) tdClass += " bg-danger text-white";
                
                let tdClass = "clickable";
                
                // Highlight leave types automatically
                const leaveTypes = ["A/L", "S", "CL", "UL"];
                if (slot) {
                    const shiftCode = slot.type?.toUpperCase?.().trim() || "";
                    
                    // Highlight if it's a leave request OR one of the leave codes
                    if (slot.leave_request || leaveTypes.includes(shiftCode)) {
                        tdClass += " bg-danger text-white";
                    }
                }

                return `<td data-user="${row.user_id}" data-date="${date}" class="${tdClass}">${display}</td>`;
            });

            // Insert row just after the section header
            const idx = rows.findIndex(r => r.includes(sectionLabel));
            rows.splice(idx + 1, 0, `<tr><td>${row.name}${cells.join('')}</td></tr>`);
        });

        document.getElementById(`dayGridBody${prefix}`).innerHTML = rows.join('');

        // Add event listener for delete buttons
        document.querySelectorAll(`#dayGridBody${prefix} .delete-shift`).forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // prevent triggering the modal
                const userId = this.dataset.user;
                const date = this.dataset.date;
                if(!confirm('Are you sure you want to delete this shift?')) return;

                fetch('/api/delete-shift', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ user_id: userId, date: date })
                })
                .then(r => r.json())
                .then(res => {
                    if(res.status){
                        alert('✅ Shift deleted successfully!');
                        const savedWeek = localStorage.getItem('selectedWeek');
                        if (savedWeek) {
                            const [start] = savedWeek.split('|');
                            loadGrids(start);
                        } else {
                            loadGrids(date);
                        }
                        // loadGrids(date); 
                    } else {
                        alert(res.message || '❌ Failed to delete shift');
                    }
                })
                .catch(e => alert(e.message || '❌ Error deleting shift'));
            });
        });
    }


</script>
@endsection
