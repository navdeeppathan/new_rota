@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Schedules List</h4>
        
         <div class="right-actions">
            <form method="GET" action="{{ route('schedule.index') }}" style="display: flex; gap: 8px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Title" class="filter-input">
                <button type="submit" class="btn btn-primary me-2">Search</button>
            </form>

            <a href="{{ route('schedule.create') }}">
                <button class="add-button">
                    <i class="fas fa-plus"></i> Add New Schedule
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
                    <th>#ID</th>
                    <th>User ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Duration (hrs)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedule as $item)
                    <tr class="shadow-sm bg-white">
                        <td>{{($item->id) }}</td>
                        <td>{{($item->user_id)}}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->duration_hours }}</td>
                       
                        <td>
                            <a href="{{ route('schedule.edit', $item->id) }}" class="edit-btn"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{  route('schedule.destroy', $item->id)  }}" style="display:inline-block;" onsubmit="return confirm(''Delete this schedule?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No schedules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
