@extends('layout.admin')

@section('content')

<form method="GET" action="{{ route('cqc.tasks.overview') }}" class="mb-3 d-flex gap-2">

    <!-- SECTION -->
    <select name="section" class="form-select w-auto" onchange="this.form.submit()">
        @foreach (['Safe','Effective','Well-Led','Responsive','Caring'] as $sec)
            <option value="{{ $sec }}" {{ $section == $sec ? 'selected' : '' }}>
                {{ $sec }}
            </option>
        @endforeach
    </select>

    <!-- RANGE -->
    <select name="range" class="form-select w-auto" onchange="this.form.submit()">
        <option value="daily" {{ $range == 'daily' ? 'selected' : '' }}>Daily</option>
        <option value="weekly" {{ $range == 'weekly' ? 'selected' : '' }}>Weekly</option>
        <option value="monthly" {{ $range == 'monthly' ? 'selected' : '' }}>Monthly</option>
    </select>

    <!-- EXPORT -->
    <a href="{{ route('cqc.tasks.exportPdf', ['section'=>$section,'range'=>$range]) }}"
       class="btn btn-danger">
        Export PDF
    </a>

</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Progress</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tasks as $task)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $task->description }}</td>
            <td>{{ ucfirst($task->progress) }}</td>
            <td>{{ $task->created_at->format('d M Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No tasks found</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection
