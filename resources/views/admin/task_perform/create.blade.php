@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Create Task</h3>
    <form action="{{ route('task_perform.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group mb-3">
            <label>Title *</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
        </div>

        <!--<div class="form-group mb-3">-->
        <!--    <label>Date</label>-->
        <!--    <input type="date" name="date" class="form-control" value="{{ old('date') }}">-->
        <!--</div>-->

        <div class="form-group mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
        </div>

        <div class="form-group mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
        </div>

        <div class="form-group mb-3">
            <label>Upload Image</label>
            <input type="file" name="images" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Save Task</button>
        <a href="{{ route('task_perform.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
