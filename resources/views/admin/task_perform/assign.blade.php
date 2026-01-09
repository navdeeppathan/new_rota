@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Assign Schedules to Task</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
            
        </div>
    @endif

    <form method="POST" action="{{ route('task_perform.assign_multiple') }}">
        @csrf

        {{-- 1. Select User --}}
        <div class="mb-3">
            <label for="user_id" class="form-label">Select User</label>
            <select name="user_id" id="user_id" class="form-select select2" required>
                <option value="">Choose a user...</option>
                @foreach($users as $user)
                    @if($user->role_id != 1)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endif
                @endforeach
            </select>

        </div>

        {{-- 2. Select Task (based on user) --}}
        <div class="mb-3">
            <label for="task_id" class="form-label">Select Task</label>
            <select name="task_id" id="task_id" class="form-select select2" required>
                <option value="">Select schedules...</option>
                {{-- options will be loaded via JS --}}
            </select>
        </div>

        {{-- 3. Select Schedules --}}
      
        
        <div class="mb-3">
            <label for="schedule_ids" class="form-label">Select Schedule(s)</label>
            <select name="schedule_ids[]" id="schedule_ids" class="form-select select2" multiple required data-placeholder="Choose schedules...">
                <option value="" disabled>Select tasks...</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}">
                       {{ $schedule->title }} ({{ $schedule->duration_hours }} hrs)
        
                    </option>
                @endforeach
            </select>
        </div>


        <button type="submit" class="btn btn-success">Assign & Schedule</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#user_id').on('change', function () {
            const userId = $(this).val();
            const taskSelect = $('#task_id');

            taskSelect.html('<option value="">Loading...</option>');

            if (!userId) {
                taskSelect.html('<option value="">Select a task...</option>');
                return;
            }

            fetch(`/task-schedule/tasks_user/by-user/${userId}`)
                .then(res => res.json())
                .then(data => {
                    console.log("Fetched tasks:", data);
                    let options = '<option value="">Select a task...</option>';
                    data.forEach(task => {
                        options += `<option value="${task.id}">${task.title} (${task.date})</option>`;
                    });
                    taskSelect.html(options).trigger('change');
                })
                .catch(err => {
                    console.error('Error fetching tasks:', err);
                    taskSelect.html('<option value="">Error loading tasks</option>');
                });
        });
    });
</script>
@endsection


