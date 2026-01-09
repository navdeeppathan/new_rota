@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Edit Broadcast</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some issues with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('broadcasts.update', $broadcast->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="title">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $broadcast->title) }}">
        </div>

        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $broadcast->description) }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="broadcast_date">Broadcast Date</label>
            <input type="date" name="broadcast_date" class="form-control" value="{{ old('broadcast_date', $broadcast->broadcast_date ? \Carbon\Carbon::parse($broadcast->broadcast_date)->format('Y-m-d') : '') }}">
        </div>

        <div class="form-group form-check mb-3">
            <input type="checkbox" name="is_starred" class="form-check-input" id="is_starred" value="1" {{ old('is_starred', $broadcast->is_starred) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_starred">Mark as Important</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Broadcast</button>
        <a href="{{ route('broadcasts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
