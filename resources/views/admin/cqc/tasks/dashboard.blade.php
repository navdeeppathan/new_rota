@extends('layout.admin')
@section('title','Organisation Dashboard')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    font-family:'Inter',sans-serif;
    background:linear-gradient(135deg,#eff6ff,#f9fbfc);
}

/* ================= HEADER ================= */
.dashboard-header{
    animation: slideDown .8s ease;
}
@keyframes slideDown{
    from{opacity:0;transform:translateY(-30px)}
    to{opacity:1;transform:translateY(0)}
}

/* ================= SITES ================= */
.sites-bar{
    background:#4596ff;
    padding:10px 14px;
    border-radius:10px 10px 0 0;
    display:flex;
    justify-content:space-between;
    font-weight:700;
    color:#fff;
}

.sites-card{
    background:white;
    border-radius:0 0 12px 12px;
    box-shadow:0 20px 40px rgba(0,0,0,.05);
    overflow:hidden;
}

.sites-table{
    width:100%;
}
.sites-table thead{
    background:#dbeafe;
    color:#1e40af;
}
.sites-table th, .sites-table td{
    padding:14px;
    font-size:14px;
}
.sites-table tbody tr{
    background:#eff6ff;
    transition:background .3s;
}
.sites-table tbody tr:hover{
    background:#dbeafe;
}

/* ================= ICON TABS ================= */
.icon-tabs{
    background:white;
    padding:18px;
    border-radius:16px;
    display:flex;
    justify-content:space-between;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}
.icon-tab{
    flex:1;
    text-align:center;
    cursor:pointer;
    padding:16px;
    border-radius:14px;
    transition:.3s;
}
.icon-tab:hover{
    background:#eff6ff;
    transform:translateY(-6px) scale(1.05);
}
.icon-tab i{
    font-size:30px;
    color:#2563eb;
}
.icon-tab span{
    display:block;
    margin-top:6px;
    font-weight:700;
    color:#1e40af;
}

/* ================= METRIC BOX ================= */
.metric-box{
    background:white;
    padding:18px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
    position:relative;
    overflow:hidden;
    transition:.4s;
}
.metric-box:hover{
    transform:translateY(-8px) scale(1.02);
    box-shadow:0 20px 40px rgba(0,0,0,.1);
}

/* metric header */
.metric-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}
.metric-title{
    font-weight:800;
    font-size:18px;
    color:#1e40af;
}

/* dots (no animation) */
.dot-green{background:#22c55e}
.dot-yellow{background:#eab308}
.dot-blue{background:#2563eb}
.dot-purple{background:#6366f1}
.dot-orange{background:#f97316}

.dot-green,.dot-yellow,.dot-blue,.dot-purple,.dot-orange{
    width:14px;
    height:14px;
    border-radius:50%;
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}

/* metric grid */
.metric-grid{
    display:grid;
    grid-template-columns:1fr auto auto auto;
    gap:8px;
    font-size:14px;
}
.metric-grid div{
    padding:6px 0;
    border-bottom:1px solid #e5e7eb;
    font-weight:700;
    color:#1e40af;
}
.metric-label{
    font-weight:600;
    color:#64748b;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="dashboard-header">
        <h2>Organisation Dashboard</h2>
        <p>Centralised view of all audits, actions and survey engagement</p>
    </div>
    <a href="{{ route('tasks.index2') }}" class="btn btn-primary px-4 py-2 shadow">
        <i class="bi bi-list-task"></i> All Tasks
    </a>
</div>

<div class="sites-bar">Sites Overview</div>
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

<div class="icon-tabs mb-4">
    <div class="icon-tab"><i class="bi bi-file-earmark-text"></i><span>Status</span></div>
    <div class="icon-tab"><i class="bi bi-calendar-event"></i><span>Calendar</span></div>
    <div class="icon-tab"><i class="bi bi-clipboard-check"></i><span>Actions</span></div>
</div>

<div class="row g-4">
@php
$metrics = [
    ['Safe',$safeOriginal,'dot-green'],
    ['Effective',$effectiveOriginal,'dot-yellow'],
    ['Well Led',$wellledOriginal,'dot-blue'],
    ['Responsive',$responsiveOriginal,'dot-purple'],
    ['Caring',$caringOriginal,'dot-orange'],
];
@endphp

@foreach($metrics as $m)
<div class="col-md-4">
    <div class="metric-box">
        <div class="metric-header">
            <div class="metric-title">{{ $m[0] }}</div>
            <div class="{{ $m[2] }}"></div>
        </div>
        <div class="metric-grid">
            <div></div><div>Yes</div><div>No</div><div>NA</div>
            <div class="metric-label">Original</div>
            <div>{{ $m[1]['yes'] }}</div>
            <div>{{ $m[1]['no'] }}</div>
            <div>{{ $m[1]['na'] }}</div>
            <div class="metric-label">Currently</div>
            <div>{{ $m[1]['yes'] }}</div>
            <div>{{ $m[1]['no'] }}</div>
            <div>{{ $m[1]['na'] }}</div>
        </div>
    </div>
</div>
@endforeach
</div>

@endsection
