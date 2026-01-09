@extends('layouts.admin')

@section('content')
<div class="container">
    
     <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Task Schedules</h4>
        
         <div class="right-actions">
            <form method="GET" action="{{ route('task_perform.index') }}" style="display: flex; gap: 8px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Title" class="filter-input">
                <button type="submit" class="btn btn-primary me-2">Search</button>
            </form>

            <a href="{{ route('task_perform.create') }}">
                <button class="add-button">
                    <i class="fas fa-plus"></i> Add New Task
                </button>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

   
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr class="shadow-sm bg-white">
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->date }}</td>
                        <td>{{ $task->start_time }}</td>
                        <td>{{ $task->end_time }}</td>
                        <td>{{ $task->duration_hours }}</td>
                        
                        <td>
                            <a href="{{ route('task_perform.edit', $task->id) }}" class="edit-btn"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ route('task_perform.destroy', $task->id) }}" style="display:inline-block;" onsubmit="return confirm('Delete this task?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
