@extends('layout.admin')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #004a99; --secondary: #00a8e8; --success: #2e7d32; --bg: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: #333; }
        .container { max-width: 1100px; margin: auto; display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }

        .sidebarr { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); height: fit-content; position: sticky; top: 20px; }
        .main-content { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        h2 { color: var(--primary); margin-top: 0; border-bottom: 2px solid var(--bg); padding-bottom: 10px; }

        /* Checklist Styling */
        .check-item { display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #eee; transition: 0.2s; }
        .check-item:hover { background: #f1f5f9; }
        .check-item input[type="checkbox"] { transform: scale(1.5); cursor: pointer; accent-color: var(--success); }
        .label-text { flex-grow: 1; margin-left: 15px; font-weight: 500; }
        .freq-badge { font-size: 0.75em; padding: 3px 8px; border-radius: 4px; background: #e2e8f0; color: #475569; }

        /* Chart Area */
        .chart-box { text-align: center; margin-bottom: 20px; }
        .score-text { font-size: 2.5rem; font-weight: 800; color: var(--primary); margin: 0; }

        .header-bar { grid-column: span 2; background: var(--primary); color: white; padding: 15px 25px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    </style>


        <div class="container">
            <div class="header-bar">
                <div style="font-size: 1.5rem; font-weight: bold;">NEXTECK <span style="font-weight: 200;">CQC COMMAND</span></div>
                <div id="date-display">Marian House | Live Audit Mode</div>
            </div>

            <div class="sidebarr">
                <div class="chart-box">
                    <h3>Live Readiness Score</h3>
                    <canvas id="gaugeChart" width="200" height="200"></canvas>
                    <p class="score-text" id="scoreVal">0%</p>
                    <p id="statusMsg" style="color: #666; font-weight: bold;">Action Required</p>
                </div>
                <hr>
                <div style="font-size: 0.9em; color: #555;">
                    <p><strong>Manager:</strong> Linda</p>
                    <p><strong>Last Sync:</strong> Just now</p>
                </div>
            </div>

            <div class="main-content">
                <h2>Weekly Compliance Checklist</h2>
                <div id="checklist">
                    </div>
                <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px; border-left: 5px solid var(--success);">
                    <strong>CEO Note:</strong> Every checkmark updates your Evidence Vault with a timestamp and digital signature for CQC inspectors.
                </div>
            </div>
        </div>

        <script>
            const tasks = [
                { name: "MAR Charts Audited", freq: "Daily" },
                { name: "Fire Safety Walkthrough", freq: "Weekly" },
                { name: "Staff Supervision Logs Updated", freq: "Weekly" },
                { name: "Infection Control Spot Check", freq: "Daily" },
                { name: "Resident Care Plan Review", freq: "Monthly" },
                { name: "Agency Staff Induction Completed", freq: "Daily" },
                { name: "Food Hygiene & Temp Logs", freq: "Daily" },
                { name: "Emergency On-Call Rota Verified", freq: "Weekly" }
            ];

            let completedCount = 0;
            let gaugeChart;

            function init() {
                // Render Checklist
                const listDiv = document.getElementById('checklist');
                listDiv.innerHTML = tasks.map((task, index) => `
                    <div class="check-item">
                        <input type="checkbox" onchange="updateScore(this)">
                        <span class="label-text">${task.name}</span>
                        <span class="freq-badge">${task.freq}</span>
                    </div>
                `).join('');

                // Initialize Gauge
                const ctx = document.getElementById('gaugeChart').getContext('2d');
                gaugeChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [0, 100],
                            backgroundColor: ['#2e7d32', '#f1f5f9'],
                            borderWidth: 0
                        }]
                    },
                    options: { cutout: '85%', plugins: { legend: { display: false } } }
                });
            }

            function updateScore(checkbox) {
                if (checkbox.checked) completedCount++;
                else completedCount--;

                const percentage = Math.round((completedCount / tasks.length) * 100);

                // Update Chart
                gaugeChart.data.datasets[0].data = [percentage, 100 - percentage];
                gaugeChart.update();

                // Update Text
                document.getElementById('scoreVal').innerText = percentage + "%";

                const msg = document.getElementById('statusMsg');
                if(percentage < 50) { msg.innerText = "Action Required"; msg.style.color = "#c62828"; }
                else if(percentage < 90) { msg.innerText = "Improving"; msg.style.color = "#ffa000"; }
                else { msg.innerText = "Audit Ready"; msg.style.color = "#2e7d32"; }
            }

            window.onload = init;
        </script>
@endsection
