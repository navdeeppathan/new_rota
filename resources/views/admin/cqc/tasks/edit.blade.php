@extends('layout.admin')

@section('content')
<div class="container">

    <h3 class="mb-4">Edit Task</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Task Description</label>
                    <textarea name="description" class="form-control" rows="3" required>{{ $task->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-select" required>
                        <option value="Safe" {{ $task->section=='Safe'?'selected':'' }}>Safe</option>
                        <option value="Effective" {{ $task->section=='Effective'?'selected':'' }}>Effective</option>
                        <option value="Caring" {{ $task->section=='Caring'?'selected':'' }}>Caring</option>
                        <option value="Responsive" {{ $task->section=='Responsive'?'selected':'' }}>Responsive</option>
                        <option value="Well-Led" {{ $task->section=='Well-Led'?'selected':'' }}>Well-Led</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Progress</label>
                    <select name="progress" class="form-select">
                        <option value="">-- Select --</option>
                        <option value="completed" {{ $task->progress=='completed'?'selected':'' }}>Completed</option>
                        <option value="not completed" {{ $task->progress=='not completed'?'selected':'' }}>Not Completed</option>
                        <option value="progress" {{ $task->progress=='progress'?'selected':'' }}>In Progress</option>
                        
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Progress Description</label>
                    <textarea name="progress_desc" class="form-control" rows="3">{{ $task->progress_desc }}</textarea>
                </div>

                <button class="btn btn-primary">Update Task</button>
                <a href="{{ route('tasks.index2') }}" class="btn btn-secondary">Back</a>

            </form>

        </div>
    </div>

</div>
@endsection
