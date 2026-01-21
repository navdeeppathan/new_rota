@extends('layout.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root { --primary:#004a99; --success:#2e7d32; --bg:#f8fafc; }
    body { font-family:'Inter',sans-serif; background:var(--bg); }

    .container {
        max-width:1100px; margin:auto;
        display:grid; grid-template-columns:1fr 2fr; gap:20px;
    }

    .sidebarr, .main-content {
        background:#fff; padding:20px; border-radius:15px;
        box-shadow:0 4px 12px rgba(0,0,0,0.1);
    }

    .sidebarr { position:sticky; top:20px; height:fit-content; }

    .header-bar {
        grid-column:span 2;
        background:var(--primary); color:#fff;
        padding:15px 25px; border-radius:12px;
        display:flex; justify-content:space-between;
    }

    .check-item {
        display:flex; align-items:center; gap:15px;
        padding:12px; border-bottom:1px solid #eee;
    }

    .check-item input { transform:scale(1.4); }

    .extra-box {
        display:none;
        padding:10px 40px 15px;
        background:#f9fafb;
    }

    .extra-box input, .extra-box select {
        width:100%; padding:6px; margin-bottom:6px;
    }

    .chart-box { text-align:center; }
    .score-text { font-size:2.5rem; font-weight:800; color:var(--primary); }
</style>

<style>
   
    table {
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    }

    table th, table td {
        padding:10px;
        border:1px solid #ddd;
        text-align:left;
    }

    table th {
        background:#f1f5f9;
    }

    .badge {
        padding:4px 10px;
        border-radius:12px;
        font-size:12px;
        color:#fff;
    }

    .daily { background:#0284c7; }
    .weekly { background:#7c3aed; }
    .monthly { background:#16a34a; }
</style>

<div class="container">

    <!-- HEADER -->
    <div class="header-bar">
        <div><b>NEXTECK</b> <span style="font-weight:200;">CQC COMMAND</span></div>
        <div>Marian House | Live Audit Mode</div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebarr">
        <div class="chart-box">
            <h3>Live Readiness Score</h3>
            <canvas id="gaugeChart" width="200" height="200"></canvas>
            <p class="score-text" id="scoreVal">0%</p>
            <p id="statusMsg">Action Required</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Compliance Checklist</h2>
        <div id="checklist"></div>
    </div>

</div>

<h2 style="margin-top:40px;">Daily Compliance Report</h2>

@if($dailyReport->count())
<table>
    <tr>
        <th>Task</th>
        <th>%</th>
        <th>Frequency</th>
        <th>Checked By</th>
        <th>Time</th>
    </tr>

    @foreach($dailyReport as $row)
    <tr>
        <td>{{ $row->task->name }}</td>
        <td>{{ $row->percent }}%</td>
        <td>{{ $row->frequency }}</td>
        <td>{{ $row->checked_by }}</td>
        <td>{{ \Carbon\Carbon::parse($row->checked_at)->format('h:i A') }}</td>
    </tr>
    @endforeach
</table>

<p><b>Daily Score:</b> {{ min($dailyReport->sum('percent'),100) }}%</p>
@else
<p>No tasks checked today.</p>
@endif


{{-- 🔥 PASS DB TASKS TO JS --}}
<script>
    const tasks = @json($tasks);
</script>

<script>
let gaugeChart;

/* ======================
   INIT
====================== */
function init() {
    const div = document.getElementById('checklist');

    div.innerHTML = tasks.map(t => `
        <div class="check-item">
            <input type="checkbox"
                ${t.check && t.check.is_checked ? 'checked' : ''}
                onchange="toggleTask(${t.id}, this)">
            <span>${t.name}</span>
        </div>

        <div class="extra-box"
            id="extra-${t.id}"
            style="display:${t.check && t.check.is_checked ? 'block' : 'none'}">

            <input type="number"
                id="percent-${t.id}"
                value="${t.check?.percent ?? ''}"
                placeholder="Enter percentage (0-100)">

            <select id="freq-${t.id}">
                <option value="">Select Frequency</option>
                <option ${t.check?.frequency === 'Daily' ? 'selected' : ''}>Daily</option>
                <option ${t.check?.frequency === 'Weekly' ? 'selected' : ''}>Weekly</option>
                <option ${t.check?.frequency === 'Monthly' ? 'selected' : ''}>Monthly</option>
            </select>

            <button onclick="saveTask(${t.id})"
                style="background:#2e7d32;color:#fff;border:none;padding:6px 12px;border-radius:6px;">
                Save
            </button>
        </div>
    `).join('');

    initChart();
    updateChart(calculateInitialScore());
}

/* ======================
   TOGGLE BOX
====================== */
function toggleTask(id, checkbox) {
    const box = document.getElementById(`extra-${id}`);
    if (checkbox.checked) {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
        sendUpdate(id, false, null, null);
    }
}

/* ======================
   SAVE TASK
====================== */
function saveTask(id) {
    const percent = document.getElementById(`percent-${id}`).value;
    const freq = document.getElementById(`freq-${id}`).value;

    if (!percent || !freq) {
        alert('Please enter percentage and frequency');
        return;
    }

    sendUpdate(id, true, percent, freq);
}

/* ======================
   AJAX
====================== */
function sendUpdate(taskId, isChecked, percent, frequency) {
    fetch("{{ url('admin/compliance/update-check') }}", {
        method: "POST",
        headers: {
            "Content-Type":"application/json",
            "X-CSRF-TOKEN":"{{ csrf_token() }}"
        },
        body: JSON.stringify({
            c_tasks_id: taskId,
            is_checked: isChecked,
            percent: percent,
            frequency: frequency
        })
    })
    .then(r => r.json())
    .then(r => updateChart(r.score));
}

/* ======================
   CHART
====================== */
function initChart() {
    const ctx = document.getElementById('gaugeChart').getContext('2d');
    gaugeChart = new Chart(ctx, {
        type:'doughnut',
        data:{
            datasets:[{
                data:[0,100],
                backgroundColor:['#2e7d32','#eee']
            }]
        },
        options:{
            cutout:'85%',
            plugins:{ legend:{display:false} }
        }
    });
}

function updateChart(score) {
    gaugeChart.data.datasets[0].data = [score, 100-score];
    gaugeChart.update();

    document.getElementById('scoreVal').innerText = score + "%";
    document.getElementById('statusMsg').innerText =
        score < 50 ? "Action Required" :
        score < 90 ? "Improving" : "Audit Ready";
}

/* ======================
   INITIAL SCORE
====================== */
function calculateInitialScore() {
    let total = 0;
    tasks.forEach(t => {
        if (t.check && t.check.percent) {
            total += parseInt(t.check.percent);
        }
    });
    return Math.min(total, 100);
}

window.onload = init;
</script>
@endsection
