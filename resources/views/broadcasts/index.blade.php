@extends('layouts.admin')

@section('content')
@php
$user = session('user'); 
$role_id = $user['role'];
@endphp
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Broadcast List</h2>
        @if($role_id == 1)
            <a href="{{ route('broadcasts.create') }}" class="btn btn-primary">+ Add Broadcast</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Broadcast Date</th>
                    <th>Mark As Important</th>
                      @if($role_id == 1)
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($broadcasts as $key => $broadcast)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $broadcast->title }}</td>
                        <td>{{ Str::limit($broadcast->description, 80) }}</td>
                        <td>{{ $broadcast->broadcast_date }}</td>
                         <td>
                             @if($broadcast->is_starred == "1")
                                IMPORTANT
                             @else
                                NOT IMPORTANT
                             @endif
                             
                         
                         </td>
                         @if($role_id == 1)
                            <td>
                                <a href="{{ route('broadcasts.edit', $broadcast->id) }}" class="btn btn-sm btn-warning me-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('broadcasts.destroy', $broadcast->id) }}" method="POST" style="display:inline-block;" 
                                      onsubmit="return confirm('Are you sure you want to delete this broadcast?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No broadcasts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (if using paginate in controller) --}}
    <div class="d-flex justify-content-end mt-3">
        {{ $broadcasts->links() }}
    </div>
</div>
@endsection
