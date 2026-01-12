@extends('layout.admin')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-shield-lock-fill text-primary me-2"></i>
            CQC E-Vault
        </h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="bi bi-folder-plus me-2"></i>
            Create New Folder
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        @forelse($folders as $folder)
        <div class="col-lg-6 col-md-6">
            <div class="folder-item">
                <div class="card border hover-shadow transition-all h-100" style="transition: all 0.3s ease;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 0;">
                                <div class="folder-icon">
                                    <i class="bi bi-folder-fill text-warning" style="font-size: 3rem;"></i>
                                </div>

                                <div class="flex-grow-1" style="min-width: 0;">
                                    <h5 class="mb-1 fw-bold text-truncate"
                                        title="{{ $folder->name }}">
                                        {{ $folder->name }}
                                    </h5>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Created {{ $folder->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>

                            <!-- RIGHT : ACTIONS -->
                            <div class="d-flex flex-column gap-2 flex-shrink-0">
                                <a href="{{ url('cqc-vault/folder/'.$folder->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Open Folder">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    Open
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-folder-btn"
                                        data-folder-id="{{ $folder->id }}"
                                        data-folder-name="{{ $folder->name }}"
                                        title="Delete Folder">
                                    <i class="bi bi-trash me-1"></i>
                                    Delete
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-folder-x" style="font-size: 5rem; color: #dee2e6;"></i>
                </div>
                <h4 class="text-muted mb-3">No Folders Yet</h4>
                <p class="text-muted mb-4">
                    Create your first folder to start organizing documents.
                </p>
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                    <i class="bi bi-folder-plus me-2"></i>
                    Create Your First Folder
                </button>
            </div>
        </div>
        @endforelse

    </div>
</div>

<!-- CREATE FOLDER MODAL -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title fw-semibold" id="createFolderModalLabel">
                    <i class="bi bi-folder-plus me-2"></i>
                    Create New Folder
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ url('cqc-vault/folder/create') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-lg">
                            <i class="bi bi-tag-fill text-muted me-1"></i>
                            Folder Name
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control form-control-lg"
                               placeholder="Enter folder name"
                               required
                               autofocus>
                    </div>
                    <div class="alert alert-info border-0 bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Organize your CQC documents by creating folders for different categories.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Create Folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-labelledby="deleteFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-semibold" id="deleteFolderModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <i class="bi bi-folder-x text-danger" style="font-size: 4rem;"></i>
                </div>
                <h6 class="text-center mb-3">Are you sure you want to delete this folder?</h6>
                <div class="alert alert-warning border-0">
                    <strong>Folder: <span id="folderNameDisplay"></span></strong>
                    <p class="mb-0 mt-2 small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        All files and subfolders inside will be permanently removed.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <form method="POST" id="deleteFolderForm" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Yes, Delete Folder
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-5px);
}

.transition-all {
    transition: all 0.3s ease;
}

.folder-item .card {
    border-left: 4px solid transparent;
    border-radius: 10px;
}
/* REMOVE TOP GAP FROM MODAL */
.modal-dialog {
    margin-top: 0 !important;
}

/* Optional: Modal ko thoda upar chipkao */
.modal {
    padding-top: 0 !important;
}


.folder-item .card:hover {
    border-left-color: #667eea;
    background-color: #f8f9fa;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-5px);
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: #0d6efd;
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

@media (max-width: 768px) {
    .col-lg-6 {
        padding: 0.5rem;
    }
}
</style>

<script>
// Delete Folder Modal Handler
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-folder-btn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteFolderModal'));
    const deleteFolderForm = document.getElementById('deleteFolderForm');
    const folderNameDisplay = document.getElementById('folderNameDisplay');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const folderId = this.getAttribute('data-folder-id');
            const folderName = this.getAttribute('data-folder-name');

            // Update modal content
            folderNameDisplay.textContent = folderName;
            deleteFolderForm.action = '{{ url("cqc-vault/folder") }}/' + folderId;

            // Show modal
            deleteModal.show();
        });
    });
});

// Auto-hide success alert
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(function() {
            successAlert.classList.remove('show');
            setTimeout(function() {
                successAlert.remove();
            }, 150);
        }, 5000);
    }
});
</script>

@endsection
