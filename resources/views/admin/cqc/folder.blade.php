@extends('layout.admin')

@section('content')

    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ url('cqc-vault') }}" class="btn btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Vault
            </a>
            <h4 class="fw-bold mb-0 mt-2">
                <i class="bi bi-folder-open text-warning me-2"></i>
                {{ $folder->name }}
            </h4>
        </div>
    </div>

    <div class="row g-4">

        <!-- LEFT SIDEBAR: Subfolders -->
            <div class="col-lg-5 col-md-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-folder2 me-2"></i>
                            Subfolders
                        </h6>
                    </div>

                    <div class="card-body p-0">
                        <!-- Existing Subfolders -->
                        @if($folder->children->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($folder->children as $child)
                            @if($child->status == 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center hover-item">
                                <a href="{{ url('cqc-vault/folder/'.$child->id) }}" class="text-decoration-none text-dark flex-grow-1">
                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                    <span class="fw-semibold">{{ $child->name }}</span>
                                </a>
                                <form method="POST" action="{{ url('cqc-vault/folder/'.$child->id) }}"
                                    onsubmit="return confirm('Delete this subfolder?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                        @endif

                        <!-- Add Multiple Subfolders -->

                        <div class="p-3 border-top bg-light">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-plus-circle text-primary me-1"></i>
                                Add Subfolder
                            </h6>

                            <form method="POST" action="{{ url('cqc-vault/folder/'.$folder->id.'/subfolders') }}">
                                @csrf
                                <div id="subfolderInputs">
                                    <div class="mb-2 subfolder-input">
                                        <input type="text" name="names[]" class="form-control"
                                            placeholder="Subfolder name" required>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2" id="addSubfolderInput">
                                    + Add Another
                                </button>

                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    Create Subfolder(s)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <!-- RIGHT CONTENT: Documents -->
        <div class="col-lg-7 col-md-8'}}">


            <!-- Documents Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        Documents
                        <span class="badge bg-secondary ms-2">{{ $folder->documents->count() }}</span>
                    </h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">
                                        <i class="bi bi-file-text me-1"></i>Document Title
                                    </th>
                                    <th class="text-center px-3 py-3" width="120">
                                        <i class="bi bi-eye me-1"></i>View
                                    </th>
                                    <th class="text-center px-3 py-3" width="120">
                                        <i class="bi bi-clock-history me-1"></i>History
                                    </th>
                                    <th class="text-center px-3 py-3" width="120">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($folder->documents as $doc)
                                <tr class="document-row">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-pdf text-danger me-2" style="font-size: 1.5rem;"></i>
                                            <span class="fw-semibold">{{ $doc->title }}</span>
                                        </div>
                                    </td>

                                    <td class="text-center px-3">
                                        <a href="{{ asset($doc->file_path) }}" target="_blank"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Document">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>

                                    <td class="text-center px-3">
                                        <button class="btn btn-sm btn-outline-info history-btn"
                                                data-id="{{ $doc->id }}"
                                                title="View History">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                    </td>

                                    <td class="text-center px-3">
                                        <form method="POST" action="{{ url('cqc-vault/document/'.$doc->id) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this document?')"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete Document">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-2">
                                        <div>
                                            <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #dee2e6;"></i>
                                            <h6 class="text-muted mt-3 mb-2">No Documents Yet</h6>
                                            <p class="text-muted small mb-0">Upload your first document below.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Upload Document Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-cloud-upload me-2"></i>
                        Upload New Document
                    </h6>
                </div>

                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" action="{{ url('cqc-vault/upload') }}">
                        @csrf
                        <input type="hidden" name="folder_id" value="{{ $folder->id }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag-fill text-muted me-1"></i>
                                    Document Title
                                </label>
                                <input type="text" class="form-control form-control"
                                       name="title"
                                       placeholder="Enter document title"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-paperclip text-muted me-1"></i>
                                    Choose File
                                </label>
                                <input type="file" class="form-control form-control"
                                       name="file"
                                       required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-upload me-2"></i>
                                    Upload Document
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>



<!-- HISTORY MODAL -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-clock-history me-2"></i>
                    Document History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul id="historyList" class="list-group list-group-flush"></ul>
            </div>
        </div>
    </div>
</div>

<style>
.hover-item {
    transition: all 0.3s ease;
}

.hover-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.document-row {
    transition: all 0.3s ease;
}

.document-row:hover {
    background-color: #f8f9fa;
    box-shadow: inset 4px 0 0 #667eea;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #0e8074 0%, #2dd46b 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
}

.subfolder-input {
    animation: slideIn 0.3s ease;
}
/* REMOVE TOP GAP FROM MODAL */
.modal-dialog {
    margin-top: 0 !important;
}

/* Optional: Modal ko thoda upar chipkao */
.modal {
    padding-top: 0 !important;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sticky-top {
    position: sticky;
    z-index: 1020;
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative;
    }
}
</style>

<script>
// Document History Modal
$('.history-btn').click(function(){
    let id = $(this).data('id');
    $.get('{{ url("cqc-vault/history") }}/'+id, function(res){
        let html='';
        if(res.length > 0) {
            res.forEach(r=>{
                html+=`
                    <li class="list-group-item">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-dot text-primary" style="font-size: 2rem; margin-top: -10px;"></i>
                            <div>
                                <div class="fw-semibold">${r.action}</div>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>${r.created_at}
                                </small>
                            </div>
                        </div>
                    </li>
                `;
            });
        } else {
            html = `
                <li class="list-group-item text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mb-0 mt-2">No history available</p>
                </li>
            `;
        }
        $('#historyList').html(html);
        $('#historyModal').modal('show');
    });
});

// Add Subfolder Input
$('#addSubfolderInput').click(function() {
    $('#subfolderInputs').append(`
        <div class="mb-2 subfolder-input">
            <input type="text" name="names[]" class="form-control form-control-sm"
                   placeholder="Subfolder name" required>
        </div>
    `);
});
</script>

@endsection
