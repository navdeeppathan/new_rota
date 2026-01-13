@extends('layout.admin')
@section('title','Organisation Dashboard')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; background:#f8fafc; }

    /* ---------- HEADER ---------- */
    .dashboard-header h2 { font-weight:700; }
    .dashboard-header p { color:#6b7280; }

    /* ---------- SITES BAR ---------- */
    .sites-bar {
        background:#e6fbf5;
        padding:8px 12px;
        border-radius:8px 8px 0 0;
        display:flex;
        align-items:center;
        justify-content:space-between;
        font-weight:600;
        color:#0f766e;
    }

    .sites-tabs {
        display:flex;
        background:#d1fae5;
        border-radius:6px;
        overflow:hidden;
    }
    .sites-tabs button {
        padding:6px 18px;
        border:none;
        background:transparent;
        font-weight:600;
        color:#0f766e;
    }
    .sites-tabs button.active {
        background:#0f766e;
        color:white;
    }

    /* ---------- SEARCH ---------- */
    .sites-search {
        width:100%;
        padding:8px 12px;
        border-radius:6px;
        border:1px solid #d1d5db;
        font-size:14px;
    }

    /* ---------- TABLE ---------- */
    .sites-card {
        border:1px solid #d1fae5;
        border-top:none;
        border-radius:0 0 8px 8px;
        overflow:hidden;
        background:white;
    }
    .sites-table {
        width:100%;
        border-collapse:collapse;
    }
    .sites-table thead th {
        background:#d1fae5;
        color:#0f766e;
        padding:10px;
        font-size:13px;
        text-align:left;
    }
    .sites-table tbody td {
        padding:10px;
        font-size:13px;
        border-top:1px solid #e5e7eb;
    }
    .sites-table tbody tr {
        background:#ecfeff;
    }

    /* ---------- FOOTER ---------- */
    .sites-footer {
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:10px 12px;
        border-top:1px solid #e5e7eb;
        font-size:13px;
        background:white;
    }
    .sites-footer .pager button {
        border:1px solid #0f766e;
        background:#0f766e;
        color:white;
        padding:4px 10px;
        border-radius:4px;
    }
    .sites-footer .rows select {
        border:1px solid #d1d5db;
        padding:4px 8px;
        border-radius:4px;
    }




</style>
<style>
    .icon-tabs {
        background:white;
        border-radius:10px;
        padding:18px 10px;
        display:flex;
        justify-content:space-between;
        box-shadow:0 1px 3px rgba(0,0,0,0.06);
    }

    .icon-tab {
        flex:1;
        text-align:center;
        cursor:pointer;
        color:#6b7280;
        transition:0.2s;
    }

    .icon-tab:hover {
        color:#0f766e;
    }

    .icon-tab i {
        font-size:26px;
        color:#14b8a6;
        margin-bottom:6px;
    }

    .icon-tab span {
        display:block;
        font-size:13px;
        font-weight:600;
    }
</style>
<style>
    .score-bar {
        background:white;
        border-radius:10px;
        padding:14px 16px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        box-shadow:0 1px 2px rgba(0,0,0,0.05);
    }

    .score-filter {
        display:flex;
        align-items:center;
        gap:10px;
    }

    .score-filter select {
        border:1px solid #d1d5db;
        border-radius:6px;
        padding:6px 10px;
    }

    .score-badge {
        background:#16a34a;
        color:white;
        padding:4px 10px;
        border-radius:6px;
        font-weight:700;
        font-size:13px;
    }

    .score-options {
        font-size:13px;
        color:#6b7280;
    }
    .score-options input {
        margin-right:6px;
    }
</style>
<style>
    .metric-box {
        background:white;
        border-radius:10px;
        padding:16px;
        box-shadow:0 1px 3px rgba(0,0,0,0.05);
    }

    .metric-header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:10px;
    }

    .metric-title {
        font-weight:700;
    }

    .dot-red { width:12px;height:12px;border-radius:50%;background:#ef4444; }
    .dot-yellow { width:12px;height:12px;border-radius:50%;background:#f59e0b; }
    .dot-green { width:12px;height:12px;border-radius:50%;background:#10b981; }
    .dot-blue { width:12px;height:12px;border-radius:50%;background:#3b82f6; }
    .dot-purple { width:12px;height:12px;border-radius:50%;background:#8b5cf6; }
    .dot-orange { width:12px;height:12px;border-radius:50%;background:#f97316; }
    .metric-badge {
        display:inline-block;
        font-size:12px;
        padding:4px 10px;
        border-radius:6px;
        font-weight:700;
        margin-bottom:10px;
    }
    .bad-red { background:#fee2e2;color:#dc2626; }
    .bad-yellow { background:#fef3c7;color:#d97706; }

    .metric-grid {
        display:grid;
        grid-template-columns:1fr auto auto auto;
        font-size:13px;
        gap:6px;
        margin-top:8px;
    }
    .metric-grid div {
        padding:4px 0;
        border-bottom:1px solid #e5e7eb;
    }
    .metric-grid div:last-child { border:none; }
    .metric-label { font-weight:600;color:#6b7280; }
</style>

<!-- ================= HEADER ================= -->
    <div class="d-flex justify-content-between align-items-center">
        <div class="dashboard-header mb-4">
            <h2>Organisation Dashboard</h2>
            <p>The Organisation Dashboard gives you a centralised view of all audit activity, actions, and survey engagement across your entire service.</p>
        </div>
        <a href="{{route('tasks.index2')}}" class="btn btn-primary" >
            <i class="fa fa-download"></i>
            <span>All Tasks</span>
        </a>
    </div>

    <!-- ================= SITES ================= -->
    <div class="sites-bar">
        <span>Sites</span>
        
    </div>



    <div class="sites-card mb-4">
        <table class="sites-table">
            <thead>
                <tr>
                
                    <th>Safe</th>
                    <th>Effective</th>
                    <th>Well Led</th>
                    <th>Responsive</th>
                    <th>Caring</th>
                    <th>Task Remaining</th>
                    
                </tr>
            </thead>
            <tbody>
                
                <tr>
                    
                <td>{{ $safe ?? '-' }}</td>
                <td>{{ $effective ?? '-' }}</td>
                <td>{{ $wellled ?? '-' }}</td>
                <td>{{ $responsive ?? '-' }}</td>
                <td>{{ $caring ?? '-' }}</td>
                <td>{{ $remaining ?? '-' }}</td>
            

                
                </tr>
            
            </tbody>


        </table>

    

    

    </div>



<!-- ================= ICON MENU ================= -->


<div class="icon-tabs mb-4">

    <div class="icon-tab">
        <i class="bi bi-file-earmark-text"></i>
        <span>Status</span>
    </div>

    <div class="icon-tab">
        <i class="bi bi-calendar-event"></i>
        <span>Calendar</span>
    </div>

   

    <div class="icon-tab">
        <i class="bi bi-clipboard-check"></i>
        <span>Actions</span>
    </div>

    

</div>








<div class="row g-3">

    <div class="col-md-4">
        <div class="metric-box">
            <div class="metric-header">
                <div class="metric-title">Safe</div>
                <div class="dot-green"></div>
            </div>

            <div class="metric-grid">
                <div></div><div>Yes</div><div>No</div><div>NA</div>

                <div class="metric-label">Original :</div>
                <div>{{ $safeOriginal['yes'] }}</div>
                <div>{{ $safeOriginal['no'] }}</div>
                <div>{{ $safeOriginal['na'] }}</div>

                <div class="metric-label">Currently :</div>
                <div>{{ $safeOriginal['yes'] }}</div>
                <div>{{ $safeOriginal['no'] }}</div>
                <div>{{ $safeOriginal['na'] }}</div>
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class="metric-box">
            <div class="metric-header">
                <div class="metric-title">Effective</div>
                <div class="dot-yellow"></div>
            </div>

            <div class="metric-grid">
                <div></div><div>Yes</div><div>No</div><div>NA</div>

                <div class="metric-label">Original :</div>
                <div>{{ $effectiveOriginal['yes'] }}</div>
                <div>{{ $effectiveOriginal['no'] }}</div>
                <div>{{ $effectiveOriginal['na'] }}</div>

                <div class="metric-label">Currently :</div>
                <div>{{ $effectiveOriginal['yes'] }}</div>
                <div>{{ $effectiveOriginal['no'] }}</div>
                <div>{{ $effectiveOriginal['na'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="metric-box">
            <div class="metric-header">
                <div class="metric-title">Well Led</div>
                <div class="dot-blue"></div>
            </div>

            <div class="metric-grid">
                <div></div><div>Yes</div><div>No</div><div>NA</div>

                <div class="metric-label">Original :</div>
                <div>{{ $wellledOriginal['yes'] }}</div>
                <div>{{ $wellledOriginal['no'] }}</div>
                <div>{{ $wellledOriginal['na'] }}</div>

                <div class="metric-label">Currently :</div>
                <div>{{ $wellledOriginal['yes'] }}</div>
                <div>{{ $wellledOriginal['no'] }}</div>
                <div>{{ $wellledOriginal['na'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="metric-box">
            <div class="metric-header">
                <div class="metric-title">Responsive</div>
                <div class="dot-purple"></div>
            </div>

            <div class="metric-grid">
                <div></div><div>Yes</div><div>No</div><div>NA</div>

                <div class="metric-label">Original :</div>
                <div>{{ $responsiveOriginal['yes'] }}</div>
                <div>{{ $responsiveOriginal['no'] }}</div>
                <div>{{ $responsiveOriginal['na'] }}</div>

                <div class="metric-label">Currently :</div>
                <div>{{ $responsiveOriginal['yes'] }}</div>
                <div>{{ $responsiveOriginal['no'] }}</div>
                <div>{{ $responsiveOriginal['na'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="metric-box">
            <div class="metric-header">
                <div class="metric-title">Caring</div>
                <div class="dot-orange"></div>
            </div>

            <div class="metric-grid">
                <div></div><div>Yes</div><div>No</div><div>NA</div>

                <div class="metric-label">Original :</div>
                <div>{{ $caringOriginal['yes'] }}</div>
                <div>{{ $caringOriginal['no'] }}</div>
                <div>{{ $caringOriginal['na'] }}</div>

                <div class="metric-label">Currently :</div>
                <div>{{ $caringOriginal['yes'] }}</div>
                <div>{{ $caringOriginal['no'] }}</div>
                <div>{{ $caringOriginal['na'] }}</div>
            </div>
        </div>
    </div>

</div>




@endsection
