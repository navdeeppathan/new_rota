@extends('layout.admin')

@section('content')

<h4>Audit Logs</h4>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Time</th>
                <th>Action</th>
                <th>Summary</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>
                    <span class="badge bg-info">{{ $log->action }}</span>
                </td>
                <td>{{ $log->summary }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links() }}
@endsection
