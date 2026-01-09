@extends('layouts.admin')

@section('content')
<style>
    
    .main-content {
        padding: 0px !important;
    }
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
              <option value="Day">Day</option>
              <option value="Night">Night</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Start Time</label>
            <input type="time" id="startTime" class="form-control">
          </div>

          <div class="mb-2">
            <label class="form-label">End Time</label>
            <input type="time" id="endTime" class="form-control">
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
        const tabs = document.querySelectorAll("#shiftTab .nav-link");
        const panes = document.querySelectorAll(".tab-pane");
    
        tabs.forEach(tab => {
            tab.addEventListener("click", function () {
                tabs.forEach(t => t.classList.remove("active"));
                panes.forEach(p => p.classList.remove("show", "active"));
                this.classList.add("active");
                document.getElementById(this.dataset.tab).classList.add("show", "active");
            });
        });
    });
    
    document.getElementById('filterForm').addEventListener('submit', e => {
        e.preventDefault();
        const date = new FormData(e.target).get('start_date');
        loadGrids(date);
    });

    function loadGrids(startDate) {
        fetch(`/api/weekly-shifts?start_date=${startDate}`)
            .then(r => r.json())
            .then(res => {
                if (!res.status) throw res;
    
                const days = [];
                for (let i = 0; i < 30; i++) {
                    const d = new Date(res.start_date);
                    d.setDate(d.getDate() + i);
                    days.push(`${d.getDate()} ${d.toLocaleString('en-US', { month: 'short' })}`);
                }
    
                // ✅ Only MASTER column
                const columns = ['MASTER', ...days];
                const headHtml = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
    
                // ✅ Kitchen only Shifts (Day)
                document.getElementById('dayGridHeadKitchen').innerHTML = headHtml;
    
                // ✅ Care has both
                document.getElementById('dayGridHeadCare').innerHTML = headHtml;
                document.getElementById('nightGridHeadCare').innerHTML = headHtml;
    
                const roleMap = {
                    3: 'T/Leaders',
                    4: 'Seniors',
                    5: 'Carers',
                    6: 'Bank',
                };
    
                const isNightShift = type => type && (type.toUpperCase().startsWith('N') || type.includes('*'));
                const isLD = type => type && type.toUpperCase().startsWith('LD');
    
                // ✅ Build Kitchen only day grid
                buildGrid(res.kitchen_housekeeping_grid, 'Kitchen', roleMap, isNightShift, isLD, columns, true);
    
                // ✅ Build Care with both day + night
                buildGrid(res.care_grid, 'Care', roleMap, isNightShift, isLD, columns, false);
            })
            .catch(e => alert(e.message || 'Error loading shifts'));
    }

    function buildGrid(gridData, prefix, roleMap, isNightShift, isLD, columns, kitchenOnlyDay = false) {
    const dayRows = [], nightRows = [];
    const dayTotals = Array(columns.length - 1).fill(0);
    const nightTotals = Array(columns.length - 1).fill(0);

    // Group headers for each role
    Object.values(roleMap).forEach(label => {
        dayRows.push(`<tr class="bg-light fw-bold text-start"><td colspan="${columns.length}">${label}</td></tr>`);
        if (!kitchenOnlyDay) {
            nightRows.push(`<tr class="bg-light fw-bold text-start"><td colspan="${columns.length}">${label}</td></tr>`);
        }
    });

    gridData.forEach(row => {
        let hasDay = false, hasNight = false;

        row.slots.forEach((s, i) => {
            if (s) {
                if (isNightShift(s.type)) {
                    hasNight = true;
                    nightTotals[i]++;
                } else {
                    hasDay = true;
                    dayTotals[i]++;
                }
            }
        });

        const sectionLabel = roleMap[row.role_id] || '';

        // ✅ Day Shifts
        if (hasDay) {
            const dayCells = row.slots.map(s => {
                if (!s || isNightShift(s.type)) return `<td></td>`;

                let isHighlight = false;
                const timeText = (() => {
                    if (s.type) {
                        const type = s.type.toLowerCase();
                        if ((type === 'overtime' || type === 'other') && s.time) {
                            if (type === 'overtime') isHighlight = true; // mark for yellow
                            return s.time;
                        }
                    }
                    return row.start_time && row.end_time
                        ? `${row.start_time} - ${row.end_time}`
                        : s.type;
                })();

                return `<td${isHighlight ? ' style="background-color: yellow;"' : ''}>${timeText}</td>`;
            });
            const idx = dayRows.findIndex(r => r.includes(sectionLabel));
            dayRows.splice(idx + 1, 0, `<tr><td>${row.name}</td>${dayCells.join('')}</tr>`);
        }

        // ✅ Night Shifts
        if (!kitchenOnlyDay && hasNight) {
            const nightCells = row.slots.map(s => {
                if (s && isNightShift(s.type)) {
                    let isHighlight = false;
                    const timeText = (() => {
                        if (s.type) {
                            const type = s.type.toLowerCase();
                            if ((type === 'overtime' || type === 'other') && s.time) {
                                if (type === 'overtime') isHighlight = true; // mark for yellow
                                return s.time;
                            }
                        }
                        return row.start_time && row.end_time
                            ? `${row.start_time} - ${row.end_time}`
                            : s.type;
                    })();
                    return `<td${isHighlight ? ' style="background-color: yellow;"' : ''}>${timeText}</td>`;
                }
                return `<td></td>`;
            });
            const idx = nightRows.findIndex(r => r.includes(sectionLabel));
            nightRows.splice(idx + 1, 0, `<tr><td>${row.name}</td>${nightCells.join('')}</tr>`);
        }
    });

    // ✅ Totals
    const dayTotalRow = `<tr class="fw-bold"><td>No - IN</td>${dayTotals.map(n => `<td>${n}</td>`).join('')}</tr>`;
    dayRows.push(dayTotalRow);

    document.getElementById(`dayGridBody${prefix}`).innerHTML = dayRows.join('');

    if (!kitchenOnlyDay) {
        const nightTotalRow = `<tr class="fw-bold"><td>No - N</td>${nightTotals.map(n => `<td>${n}</td>`).join('')}</tr>`;
        nightRows.push(nightTotalRow);
        document.getElementById(`nightGridBody${prefix}`).innerHTML = nightRows.join('');
    }
}

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
    
    loadGrids(new Date().toISOString().slice(0, 10));
    loadLegend();
</script>

@endsection
