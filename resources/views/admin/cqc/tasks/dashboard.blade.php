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
    <div class="sites-tabs">
        <button class="active">Enterprise</button>
        <button>Core</button>
    </div>
</div>

<div class="p-3 bg-white border-start border-end border-bottom">
    <input class="sites-search" placeholder="Enter text to search...">
</div>

<div class="sites-card mb-4">
    <table class="sites-table">
        <thead>
            <tr>
                <th>Task</th>
                <th>Section</th>
                <th>Progress</th>
                <th>Progress Description</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>{{ $task->description }}</td>
                <td>{{ $task->section }}</td>
                <td>{{ $task->progress }}</td>
                <td>{{ $task->progress_desc }}</td>
               
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No tasks found</td>
            </tr>
            @endforelse
        </tbody>


    </table>

   <div class="sites-footer">

    <div>
        Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }}
        ({{ $tasks->total() }} items)
    </div>

    <div class="pager">
        @if($tasks->onFirstPage())
            <button disabled>Prev</button>
        @else
            <a href="{{ $tasks->previousPageUrl() }}">
                <button>Prev</button>
            </a>
        @endif

        <button>{{ $tasks->currentPage() }}</button>

        @if($tasks->hasMorePages())
            <a href="{{ $tasks->nextPageUrl() }}">
                <button>Next</button>
            </a>
        @else
            <button disabled>Next</button>
        @endif
    </div>

    <div class="rows">
        Rows per page:
        <select onchange="location = this.value">
            @foreach([5,10,25] as $size)
                <option value="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}"
                    {{ request('per_page',5)==$size ? 'selected' : '' }}>
                    {{ $size }}
                </option>
            @endforeach
        </select>
    </div>

</div>

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

    {{-- <div class="icon-tab">
        <i class="bi bi-graph-up"></i>
        <span>Chart</span>
    </div> --}}

    {{-- <div class="icon-tab">
        <i class="bi bi-list-check"></i>
        <span>Audits Overview</span>
    </div> --}}

    {{-- <div class="icon-tab">
        <i class="bi bi-bar-chart"></i>
        <span>Statistics</span>
    </div> --}}

    <div class="icon-tab">
        <i class="bi bi-clipboard-check"></i>
        <span>Actions</span>
    </div>

    {{-- <div class="icon-tab">
        <i class="bi bi-grid"></i>
        <span>Categories</span>
    </div> --}}

</div>



<div class="score-bar mb-4">

    <div class="score-filter">
        <select>
            <option>All</option>
        </select>
    </div>

    <div>
        Overall Score:
        {{-- <span class="score-badge">80%</span> --}}
    </div>

    <div class="score-options">
        <label><input type="radio" checked> Show all results</label>
        <label class="ms-3"><input type="radio"> Show recent survey only</label>
    </div>

</div>



<div class="row g-3">

<!-- SAFE -->
@forelse($tasks as $task)
    <div class="col-md-4">
    <div class="metric-box">
        <div class="metric-header">
            <div class="metric-title">{{ $task->section }}</div>
            <div class="dot-red"></div>
        </div>
        <span class="metric-badge bad-red">{{ $task->progress }}</span>

        <div class="metric-grid">
            <div></div><div>Yes</div><div>No</div><div>NA</div>
            <div class="metric-label">Original :</div><div>28</div><div>10</div><div>4</div>
            <div class="metric-label">Currently :</div><div>29</div><div>9</div><div>4</div>
        </div>
    </div>
</div>
@endforeach


<!-- EFFECTIVE -->
{{-- <div class="col-md-4">
    <div class="metric-box">
        <div class="metric-header">
            <div class="metric-title">Effective</div>
            <div class="dot-red"></div>
        </div>
        <span class="metric-badge bad-red">Inadequate</span>

        <div class="metric-grid">
            <div></div><div>Yes</div><div>No</div><div>NA</div>
            <div class="metric-label">Original :</div><div>7</div><div>3</div><div>1</div>
            <div class="metric-label">Currently :</div><div>8</div><div>2</div><div>1</div>
        </div>
    </div>
</div> --}}

<!-- WELL LED -->
{{-- <div class="col-md-4">
    <div class="metric-box">
        <div class="metric-header">
            <div class="metric-title">Well Led</div>
            <div class="dot-yellow"></div>
        </div>
        <span class="metric-badge bad-yellow">Requires Improvement</span>

        <div class="metric-grid">
            <div></div><div>Yes</div><div>No</div><div>NA</div>
            <div class="metric-label">Original :</div><div>17</div><div>4</div><div>1</div>
            <div class="metric-label">Currently :</div><div>17</div><div>4</div><div>1</div>
        </div>
    </div>
</div> --}}

</div>




@endsection
