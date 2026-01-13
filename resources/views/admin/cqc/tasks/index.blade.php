@extends('layout.admin')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h3>Task Management</h3>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">+ Create Task</a>
    </div>
   

    {{-- Task Table --}}
    @include('admin.cqc.tasks.partials.section-table', [
        'title' => 'Safe',
        'tasks' => $safe
    ])

    @include('admin.cqc.tasks.partials.section-table', [
        'title' => 'Effective',
        'tasks' => $effective
    ])

    @include('admin.cqc.tasks.partials.section-table', [
        'title' => 'Caring',
        'tasks' => $caring
    ])

    @include('admin.cqc.tasks.partials.section-table', [
        'title' => 'Responsive',
        'tasks' => $responsive
    ])

    @include('admin.cqc.tasks.partials.section-table', [
        'title' => 'Well-Led',
        'tasks' => $wellLed
    ])

    

</div>



@endsection
