@extends('layouts.admin')

@section('content')
    <h3>Create Schedule</h3>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('schedule.store') }}">
        @csrf

        <div class="mb-3">
            <label>User</label>
            <select name="user_id" class="form-control" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                    @if($user->role_id != 1)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $schedule->user_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endif
                @endforeach
            </select>

        </div>

        <div class="mb-3">
            <label>Title</label>
            <input name="title" class="form-control" value="{{ old('title') }}" required />
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input name="location" class="form-control" value="{{ old('location') }}" />
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required />
        </div>

        <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required />
        </div>

        <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}" required />
        </div>

        <button class="btn btn-success">Create</button>
    </form>
@endsection
