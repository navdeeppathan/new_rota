@extends('layout.admin')

@section('content')

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexteck | CQC Readiness Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #004a99; --secondary: #00a8e8; --success: #2e7d32; --warning: #ffa000; --bg: #f0f2f5; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 20px; color: #333; }
        .dashboard-container { max-width: 1200px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .brand-logo { font-size: 24px; font-weight: 800; color: var(--primary); }
        .status-badge { background: #e8f5e9; color: var(--success); padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 0.9em; border: 1px solid var(--success); }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .card h3 { margin-top: 0; color: #666; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; }

        .chart-container { position: relative; height: 250px; width: 100%; display: flex; justify-content: center; }
        .big-stat { font-size: 3rem; font-weight: 800; color: var(--primary); margin: 10px 0; }
        .footer-info { text-align: center; margin-top: 30px; color: #888; font-size: 0.85em; }
    </style>


<div class="dashboard-container">
    <header>
        <div class="brand-logo">NEXTECK <span style="font-weight: 300; color: var(--secondary);">Digital Care</span></div>
        <div class="status-badge">● CQC AUDIT READY</div>
    </header>

    <div class="grid">
        <div class="card">
            <h3>Overall CQC Readiness</h3>
            <div class="chart-container">
                <canvas id="readinessChart"></canvas>
            </div>
            <p style="font-weight: bold; color: var(--success);">94% Compliance Score</p>
        </div>

        <div class="card">
            <h3>5 Key Question Performance</h3>
            <div class="chart-container">
                <canvas id="kloChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Staff Training Status</h3>
            <div class="chart-container">
                <canvas id="staffChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card" style="grid-column: span 1;">
            <h3>Days Since Last Incident</h3>
            <div class="big-stat">128</div>
            <p style="color: #666;">Target: > 90 Days</p>
        </div>
        <div class="card" style="grid-column: span 2; text-align: left;">
            <h3>Live Compliance Alerts</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px 0; border-bottom: 1px solid #eee;">⚠️ Fire Safety Audit due in <strong>4 days</strong></li>
                <li style="padding: 10px 0; border-bottom: 1px solid #eee;">✅ 42/42 Care Plans Reviewed this month</li>
                <li style="padding: 10px 0;">✅ 100% MAR Chart Completion (Last 24hrs)</li>
            </ul>
        </div>
    </div>

    <div class="footer-info">
        Nexteck.uk | Management System for Marian House | Private & Confidential
    </div>
</div>

<script>
    // Readiness Gauge (Pie/Doughnut)
    new Chart(document.getElementById('readinessChart'), {
        type: 'doughnut',
        data: {
            labels: ['Ready', 'Pending'],
            datasets: [{
                data: [94, 6],
                backgroundColor: ['#2e7d32', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: { cutout: '80%', plugins: { legend: { display: false } } }
    });

    // KLOE Performance (Bar Chart)
    new Chart(document.getElementById('kloChart'), {
        type: 'bar',
        data: {
            labels: ['Safe', 'Effective', 'Caring', 'Resp.', 'Well-Led'],
            datasets: [{
                label: 'Score %',
                data: [98, 92, 100, 88, 95],
                backgroundColor: '#004a99'
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, max: 100 } },
            plugins: { legend: { display: false } }
        }
    });

    // Staff Training (Pie Chart)
    new Chart(document.getElementById('staffChart'), {
        type: 'pie',
        data: {
            labels: ['Up to Date', 'Due Soon', 'Overdue'],
            datasets: [{
                data: [85, 12, 3],
                backgroundColor: ['#2e7d32', '#ffa000', '#c62828']
            }]
        }
    });
</script>
@endsection
