@extends('layouts.admin')

@section('content')
    <h3>Edit Schedule</h3>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('schedule.update', $schedule->id) }}">
        @csrf

        <div class="mb-3">
            <label for="user_id">User</label>
            <select name="user_id" class="form-control" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                    @if($user->role_id != 1)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $schedule->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endif
                @endforeach
            </select>

        </div>

        <div class="mb-3">
            <label for="title">Title</label>
            <input name="title" class="form-control" value="{{ old('title', $schedule->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="description">Description</label>
            <textarea name="description" class="form-control">{{ old('description', $schedule->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="location">Location</label>
            <input name="location" class="form-control" value="{{ old('location', $schedule->location) }}">
        </div>

        <div class="mb-3">
            <label for="date">Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date', $schedule->date) }}" required>
        </div>

        <div class="mb-3">
            <label for="start_time">Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $schedule->start_time) }}" required>
        </div>

        <div class="mb-3">
            <label for="end_time">End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $schedule->end_time) }}" required>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Back</a>
    </form>
@endsection
