@extends('layout.admin')

@section('content')
<div class="container">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h3 class="mb-4">Create New Task</h3>

    {{-- Validation Errors --}}
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
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Task Description</label>
                    <textarea name="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-select" required>
                        <option value="">-- Select Section --</option>
                        <option value="Safe" {{ old('section')=='Safe'?'selected':'' }}>Safe</option>
                        <option value="Effective" {{ old('section')=='Effective'?'selected':'' }}>Effective</option>
                        <option value="Caring" {{ old('section')=='Caring'?'selected':'' }}>Caring</option>
                        <option value="Responsive" {{ old('section')=='Responsive'?'selected':'' }}>Responsive</option>
                        <option value="Well-Led" {{ old('section')=='Well-Led'?'selected':'' }}>Well-Led</option>
                    </select>
                </div>


                <button class="btn btn-primary">Save Task</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back</a>

            </form>
        </div>
    </div>

</div>
@endsection
