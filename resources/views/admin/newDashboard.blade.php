@extends('layout.admin')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #004a99; --secondary: #00a8e8; --success: #2e7d32; --warning: #ffa000; --danger: #c62828; --bg: #f0f2f5; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: #333; }
        .dashboard-container { max-width: 1200px; margin: auto; }
        
        /* Navigation Tabs */
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; background: #ddd; color: #666; transition: 0.3s; }
        .tab-btn.active { background: var(--primary); color: white; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .brand-logo { font-size: 24px; font-weight: 800; color: var(--primary); }
        .status-badge { background: #e8f5e9; color: var(--success); padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 0.9em; border: 1px solid var(--success); }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .card h3 { margin-top: 0; color: #666; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .chart-container { position: relative; height: 250px; width: 100%; display: flex; justify-content: center; }
        .big-stat { font-size: 3rem; font-weight: 800; color: var(--primary); margin: 10px 0; }
        .hidden { display: none; }
        .footer-info { text-align: center; margin-top: 30px; color: #888; font-size: 0.85em; }
    </style>


<div class="dashboard-container">
    <header>
        <div class="brand-logo">NEXTECK <span style="font-weight: 300; color: var(--secondary);">Unified Command</span></div>
        <div class="status-badge">● SYSTEM LIVE</div>
    </header>

    <div class="nav-tabs">
        <button id="btn-cqc" class="tab-btn active" onclick="switchTab('cqc')">CQC Readiness</button>
        <button id="btn-rota" class="tab-btn" onclick="switchTab('rota')">Live Rota Operations</button>
    </div>

    <div id="section-cqc">
        <div class="grid">
            <div class="card">
                <h3>Overall CQC Readiness</h3>
                <div class="chart-container"><canvas id="readinessChart"></canvas></div>
                <p style="font-weight: bold; color: var(--success);">94% Compliance Score</p>
            </div>
            <div class="card">
                <h3>5 Key Question Performance</h3>
                <div class="chart-container"><canvas id="kloChart"></canvas></div>
            </div>
            <div class="card">
                <h3>Staff Training Status</h3>
                <div class="chart-container"><canvas id="staffChart"></canvas></div>
            </div>
        </div>
        <div class="grid">
            <div class="card">
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
    </div>

    <div id="section-rota" class="hidden">
        <div class="grid">
            <div class="card">
                <h3>Staff Attendance (Live)</h3>
                <div class="chart-container"><canvas id="rotaLiveChart"></canvas></div>
                <p style="font-weight: bold; color: var(--primary);">8/10 Staff On-Site</p>
            </div>
            <div class="card">
                <h3>Visit Volume (Current Week)</h3>
                <div class="chart-container"><canvas id="rotaVolumeChart"></canvas></div>
            </div>
            <div class="card">
                <h3>Punctuality Rating</h3>
                <div class="chart-container"><canvas id="rotaPunctualChart"></canvas></div>
            </div>
        </div>
        <div class="grid">
            <div class="card">
                <h3>Total Care Hours</h3>
                <div class="big-stat">442</div>
                <p style="color: #666;">Delivered This Month</p>
            </div>
            <div class="card" style="grid-column: span 2; text-align: left;">
                <h3>Active Shift Feed</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><strong>Sarah J.</strong></td>
                        <td>Mr. Henderson (SW1)</td>
                        <td><span style="color: var(--success);">● Active</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><strong>David K.</strong></td>
                        <td>Mrs. Gills (E14)</td>
                        <td><span style="color: var(--warning);">● En-Route</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Amrita B.</strong></td>
                        <td>Mr. Peters (N1)</td>
                        <td><span style="color: var(--primary);">● Completed</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="footer-info">
        Nexteck.uk | Diamond Home Care Operations | 2026 Strategy
    </div>
</div>

<script>
    // Tab Switching Logic
    function switchTab(tab) {
        if(tab === 'cqc') {
            document.getElementById('section-cqc').classList.remove('hidden');
            document.getElementById('section-rota').classList.add('hidden');
            document.getElementById('btn-cqc').classList.add('active');
            document.getElementById('btn-rota').classList.remove('active');
        } else {
            document.getElementById('section-cqc').classList.add('hidden');
            document.getElementById('section-rota').classList.remove('hidden');
            document.getElementById('btn-rota').classList.add('active');
            document.getElementById('btn-cqc').classList.remove('active');
        }
    }

    // --- CQC CHARTS ---
    new Chart(document.getElementById('readinessChart'), {
        type: 'doughnut',
        data: { labels: ['Ready', 'Pending'], datasets: [{ data: [94, 6], backgroundColor: ['#2e7d32', '#e0e0e0'], borderWidth: 0 }] },
        options: { cutout: '80%', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('kloChart'), {
        type: 'bar',
        data: { labels: ['Safe', 'Eff.', 'Car.', 'Res.', 'Led'], datasets: [{ data: [98, 92, 100, 88, 95], backgroundColor: '#004a99' }] },
        options: { scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('staffChart'), {
        type: 'pie',
        data: { labels: ['Done', 'Soon', 'Late'], datasets: [{ data: [85, 12, 3], backgroundColor: ['#2e7d32', '#ffa000', '#c62828'] }] }
    });

    // --- ROTA CHARTS ---
    new Chart(document.getElementById('rotaLiveChart'), {
        type: 'doughnut',
        data: { labels: ['On-Site', 'Travelling', 'Off'], datasets: [{ data: [80, 15, 5], backgroundColor: ['#004a99', '#ffa000', '#eee'], borderWidth: 0 }] },
        options: { cutout: '80%', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('rotaVolumeChart'), {
        type: 'bar',
        data: { labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], datasets: [{ label: 'Visits', data: [42, 38, 45, 40, 35], backgroundColor: '#00a8e8' }] },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('rotaPunctualChart'), {
        type: 'pie',
        data: { labels: ['On Time', 'Late < 5m', 'Late > 15m'], datasets: [{ data: [92, 6, 2], backgroundColor: ['#2e7d32', '#ffa000', '#c62828'] }] }
    });
</script>

@endsection