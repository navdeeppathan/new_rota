@extends('layout.admin')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h3>Task Management</h3>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">+ Create Task</a>
    </div>
   

    {{-- Task Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark ">
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Section</th>
                        <th>Progress</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>
                            <form method="POST" action="{{ route('tasks.update',$task->id) }}">
                            @csrf
                            <input type="text" name="description" value="{{ $task->description }}" class="form-control">
                        </td>
                        <td>
                            <select name="section" class="form-select" required>
                                <option value="">-- Select Section --</option>

                                <option value="Safe" {{ $task->section == 'Safe' ? 'selected' : '' }}>Safe</option>
                                <option value="Effective" {{ $task->section == 'Effective' ? 'selected' : '' }}>Effective</option>
                                <option value="Caring" {{ $task->section == 'Caring' ? 'selected' : '' }}>Caring</option>
                                <option value="Responsive" {{ $task->section == 'Responsive' ? 'selected' : '' }}>Responsive</option>
                                <option value="Well-Led" {{ $task->section == 'Well-Led' ? 'selected' : '' }}>Well-Led</option>
                            </select>
                        </td>

                        <td>
                            <select name="progress" class="form-select progress-select" data-id="{{ $task->id }}">
                                <option value="">-- Select --</option>
                                <option value="completed" {{ $task->progress=='completed'?'selected':'' }}>Completed</option>
                                <option value="not completed" {{ $task->progress=='not completed'?'selected':'' }}>Not Completed</option>
                                <option value="progress" {{ $task->progress=='progress'?'selected':'' }}>Progress</option>
                                <option value="note" {{ $task->progress=='note'?'selected':'' }}>Note</option>
                                <option value="location" {{ $task->progress=='location'?'selected':'' }}>Location</option>
                            </select>

                            <input type="hidden" name="progress_desc" id="desc_{{ $task->id }}" value="{{ $task->progress_desc }}">
                        </td>
                        
                        <td>
                            <button class="btn btn-success btn-sm">Update</button>
                            </form>
                            <a href="{{ route('tasks.delete',$task->id) }}" class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this task?')">Delete</a>
                        </td>
                    </tr>
                    @endforeach
                    @if($tasks->count() == 0)
                        <tr><td colspan="5" class="text-center">No tasks found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Enter Details</h5>
        </div>
        <div class="modal-body">
            <textarea id="modal_desc" class="form-control" rows="4"></textarea>
            <input type="hidden" id="modal_task_id">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="saveDesc()">Save</button>
        </div>
        </div>
    </div>
    </div>

</div>

<script>
document.querySelectorAll('.progress-select').forEach(el => {
    el.addEventListener('change', function () {
        let val = this.value;
        let id = this.dataset.id;

        if (val === 'note' || val === 'location') {
            document.getElementById('modal_task_id').value = id;
            new bootstrap.Modal(document.getElementById('noteModal')).show();
        }
    });
});

function saveDesc() {
    let id = document.getElementById('modal_task_id').value;
    let val = document.getElementById('modal_desc').value;

    document.getElementById('desc_' + id).value = val;
    bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
}
</script>

@endsection
