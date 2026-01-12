@extends('layout.admin')

@section('content')

<h4>Create Folder / Subfolder</h4>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- CREATE FOLDER FORM -->
<form method="POST" action="{{ url('cqc-vault/folder/create') }}" class="mb-4">
    @csrf
    <div class="row mb-2">
        <div class="col-md-6">
            <select name="parent_id" class="form-select">
                <option value="">-- Select Parent Folder (for Subfolder) --</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="names[]" class="form-control" placeholder="Folder Name" required>
        </div>
    </div>

    <div id="additional-folders"></div>
    <button type="button" class="btn btn-secondary mb-2" id="addFolderInput">+ Add Another Subfolder</button>
    <button class="btn btn-success w-100">Create Folder(s)</button>
</form>

<script>
$(document).ready(function() {
    let counter = 1;
    $('#addFolderInput').click(function() {
        counter++;
        $('#additional-folders').append(`
            <div class="row mb-2" id="folderRow${counter}">
                <div class="col-md-6">
                    <select name="parent_id" class="form-select">
                        <option value="">-- Select Parent Folder (for Subfolder) --</option>
                        @foreach($folders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="names[]" class="form-control" placeholder="Folder Name" required>
                </div>
            </div>
        `);
    });
});
</script>

@endsection
