@extends('layouts.admin')

@section('content')
@php
    $user = session('user'); 
    $role_id = $user['role'];
@endphp
    <h1>Manage Privacy Policy</h1>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.privacy.manage') }}" method="POST">
        @csrf
       <textarea name="content" id="editor" style=" min-height: 100px;">{{ old('content', $policy->content ?? '') }}</textarea>


        <br>
        @if($role_id == 1)
            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
                Save Policy
            </button>
        @endif

    </form>

    {{-- CKEditor --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'blockQuote', 'undo', 'redo'
                ]
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@endsection
