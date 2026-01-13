<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">{{ $title }} Tasks</h5>
    </div>

    <div class="card-body">

        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Progress</th>
                    <th>Progress Description</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody>
               @foreach($tasks as $index => $task)
                <tr>
                    <td>{{ $tasks->firstItem() + $index }}</td>

                    <td>
                        {{ $task->description }}
                    </td>

                    <td>
                        {{ $task->progress }}
                    </td>

                    <td>
                        {{ $task->progress_desc }}
                    </td>

                    <td class="text-center">

                       {{-- Note --}}
                        <button class="btn btn-sm btn-outline-warning me-1"
                                onclick="openProgressModal({{ $task->id }}, 'note', '{{ $task->progress_desc }}')">
                            <i class="bi bi-journal-text"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-primary me-1"
                                onclick="openProgressModal({{ $task->id }}, 'location', '{{ $task->progress_desc }}')">
                            <i class="bi bi-geo-alt"></i>
                        </button>

                        {{-- Edit --}}
                        <a href="{{ route('tasks.edit',$task->id) }}"
                        class="btn btn-sm btn-outline-success"
                        title="Edit Task">
                            <i class="bi bi-pencil-square"></i>
                        </a>


                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-2">
           {{ $tasks->links() }}

        </div>

    </div>
</div>


<div class="modal fade" id="progressModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <textarea id="modal_desc" class="form-control" rows="4"
                  placeholder="Enter details..."></textarea>
        <input type="hidden" id="modal_task_id">
        <input type="hidden" id="modal_progress">
    </div>

    <div class="modal-footer">
        <button class="btn btn-primary" onclick="saveProgress()">Save</button>
    </div>

</div>
</div>
</div>

<script>
function openProgressModal(id, type, desc = '') {
    document.getElementById('modal_task_id').value = id;
    document.getElementById('modal_progress').value = type;
    document.getElementById('modal_desc').value = desc;

    document.getElementById('modalTitle').innerText =
        type === 'note' ? 'Add Note' : 'Add Location';

    new bootstrap.Modal(document.getElementById('progressModal')).show();
}

function saveProgress() {
    let id = document.getElementById('modal_task_id').value;
    let progress = document.getElementById('modal_progress').value;
    let desc = document.getElementById('modal_desc').value;

    fetch(`/cqc-vault/tasks/${id}/progress`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            progress: progress,
            progress_desc: desc
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            location.reload();
        }
    });
}
</script>
