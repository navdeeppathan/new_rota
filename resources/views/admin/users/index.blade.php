@extends('layouts.admin')

@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0;
  right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #305ED9;
}

input:checked + .slider:before {
  transform: translateX(20px);
}
</style>

@php
$user = session('user'); 
$role_id = $user['role'];
@endphp

<div class="user-list-container">
    <div class="top-bar">
        <div class="title">Users List</div>
        <div class="right-actions">
            <form method="GET" action="{{ route('users') }}" style="display: flex; gap: 8px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter" class="filter-input">
                <button type="submit" class="search-btn">Search</button>
            </form>

             @if($role_id == 1)
                <a href="{{ route('users.create') }}">
                    <button class="add-button">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </a>
            @endif
        </div>
    </div>
    
    <style>
        .search-btn {
            padding: 8px 16px;
            background-color: #305ED9;
            color: #fff;
            border: none;
            border-radius: 999px;
            font-weight: 600;
            margin-left: 10px;
            cursor: pointer;
        }
    
        .search-btn:hover {
            background-color: #254bb5;
        }
    </style>


    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Profile</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{($user->id) }}</td>
                        <td>
                            <img src="{{ $user->profile_pic ? asset($user->profile_pic) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=305ed9&color=fff' }}" alt="Profile">
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                         <td>@if($user->category == 1 ) Kitchen and Houskeeping @else Care @endif</td>
                      <td>
                            @if($role_id == 0)
                                {{-- Only show status without toggle --}}
                                {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                            @else
                                <label class="switch">
                                    <input type="checkbox" class="status-toggle" data-user-id="{{ $user->id }}" {{ $user->status == 1 ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            @endif
                        </td>
                        
                        <td>
                            @if($role_id == 0)
                                {{-- No edit/delete for role_id 0 --}}
                                <span style="color: #888;">View Only</span>
                            @else
                                <a href="{{ route('users.edit', $user->id) }}" class="edit-btn"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('users.delete', $user->id) }}" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const userId = this.dataset.userId;
            const newStatus = this.checked ? 1 : 0;

            fetch(`/api/user/status?user_id=${userId}&status=${newStatus}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: data.message || 'Something went wrong.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Could not update status.'
                });
                console.error(err);
            });
        });
    });
</script>

@endsection


