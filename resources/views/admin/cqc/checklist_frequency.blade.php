@extends('layout.admin')

@section('content')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexteck | CQC Compliance Checklist</title>
    <style>
        :root { --primary: #004a99; --secondary: #00a8e8; --light: #f4f7f6; --dark: #333; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--light); color: var(--dark); padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: var(--primary); text-align: center; margin-bottom: 10px; }
        p.subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .tabs { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
        button { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-active { background: var(--primary); color: white; }
        .btn-inactive { background: #e0e0e0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: var(--primary); color: white; text-align: left; padding: 15px; }
        td { border-bottom: 1px solid #ddd; padding: 15px; }
        tr:hover { background: #f9f9f9; }
        .freq-tag { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; }
        .daily { background: #ffebee; color: #c62828; }
        .weekly { background: #e3f2fd; color: #1565c0; }
        .monthly { background: #e8f5e9; color: #2e7d32; }
        .branding { text-align: center; margin-top: 40px; font-size: 0.9em; color: #999; }
    </style>

    <div class="container">
        <h1>Nexteck CQC Evidence Tracker</h1>
        <p class="subtitle">Ensuring your agency is "Inspection-Ready" 24/7</p>

        <div class="tabs">
            <button id="careBtn" class="btn-active" onclick="showTab('care')">Care Home (Residential)</button>
            <button id="domBtn" class="btn-inactive" onclick="showTab('dom')">Domiciliary (Home Care)</button>
        </div>

        <table id="checklistTable">
            <thead>
                <tr>
                    <th>CQC Category</th>
                    <th>Evidence Requirement</th>
                    <th>Frequency</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                </tbody>
        </table>

        <div class="branding">
            <strong>Nexteck Digital Solutions</strong> | nexteck.uk | 07879 175585
        </div>
    </div>

    <script>
        const careData = [
            { cat: 'Safe', item: 'MAR Charts & Med Audits', freq: 'Daily', cls: 'daily' },
            { cat: 'Safe', item: 'Fire Safety & Alarm Logs', freq: 'Weekly', cls: 'weekly' },
            { cat: 'Effective', item: 'Deprivation of Liberty (DoLS) Tracker', freq: 'Monthly', cls: 'monthly' },
            { cat: 'Caring', item: 'Resident Daily Choice Logs', freq: 'Daily', cls: 'daily' },
            { cat: 'Responsive', item: 'Care Plan Reviews', freq: 'Monthly', cls: 'monthly' },
            { cat: 'Well-Led', item: 'Manager Quality Audits', freq: 'Monthly', cls: 'monthly' }
        ];

        const domData = [
            { cat: 'Safe', item: 'Lone Worker Check-in Logs', freq: 'Daily', cls: 'daily' },
            { cat: 'Safe', item: 'Field Supervision (Spot Checks)', freq: 'Monthly', cls: 'monthly' },
            { cat: 'Effective', item: 'Staff Competency Sign-offs', freq: 'Quarterly', cls: 'monthly' },
            { cat: 'Responsive', item: 'Late/Missed Call Mitigation', freq: 'Daily', cls: 'daily' },
            { cat: 'Well-Led', item: 'Service User Feedback Surveys', freq: 'Monthly', cls: 'monthly' }
        ];

        function showTab(type) {
            const body = document.getElementById('tableBody');
            const data = type === 'care' ? careData : domData;

            // Update Buttons
            document.getElementById('careBtn').className = type === 'care' ? 'btn-active' : 'btn-inactive';
            document.getElementById('domBtn').className = type === 'dom' ? 'btn-active' : 'btn-inactive';

            body.innerHTML = data.map(row => `
                <tr>
                    <td><strong>${row.cat}</strong></td>
                    <td>${row.item}</td>
                    <td><span class="freq-tag ${row.cls}">${row.freq}</span></td>
                </tr>
            `).join('');
        }

        // Initial Load
        showTab('care');
    </script>
@endsection
