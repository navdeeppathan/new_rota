<!DOCTYPE html>
<html>
<head>
<title>CQC E-Vault</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body{ background:#f4f6f9; font-family:'Poppins', sans-serif; }
        .sidebar{
            width:240px;
            height:100vh;
            position:fixed;
            background:#0d6efd;
            color:white;
        }
        .sidebar a{
            color:white;
            text-decoration:none;
            padding:12px;
            display:block;
        }
        .sidebar a:hover{ background:#374151; }
        .content{
            margin-left:240px;
            padding:20px;
        }
 
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">

    <h5 class="p-3 border-bottom">Admin Panel</h5>

    <a href="{{ url('admin/dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    <a href="{{ url('cqc-vault') }}">
        <i class="bi bi-folder2-open me-2"></i>CQC E-Vault
    </a>
    <a href="{{ url('admin/checklist-frequency') }}">
        <i class="bi bi-calendar-check me-2"></i> Checklist Frequency
    </a>
    <a href="{{ url('admin/compliance') }}">
        <i class="bi bi-list-check me-2"></i> Checklist CQC
    </a>
     <a href="{{ url('cqc-vault/tasks') }}">
        <i class="bi bi-list me-2"></i>Tasks
    </a>
    <a href="{{ url('cqc-vault/audit-logs') }}">
        <i class="bi bi-journal-text me-2"></i>Audit CQC E-Vault
    </a>

    <!-- Push user info to bottom -->
    <div class="mt-auto border-top p-3">

        <!-- User -->
        {{-- <div class="d-flex align-items-center ">
            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2"
                 style="width:40px;height:40px;">
                <i class="bi bi-person text-white fs-5"></i>
            </div>
            <div>
                <strong>{{ Auth::user()->name }}</strong>
                <div class="text-white" style="font-size:12px;">Administrator</div>
            </div>
        </div> --}}

        <!-- Logout -->
         <!-- Logout -->
     <form method="POST" action="{{ route('logout') }}" class="mt-3 btn btn-danger btn-sm text-white">
        @csrf
        <button class="btn btn-danger w-100" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
    </form>

    </div>

</div>


<!-- CONTENT -->
<div class="content">
@yield('content')
</div>

</body>
</html>
