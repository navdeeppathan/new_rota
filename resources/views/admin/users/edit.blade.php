@extends('layouts.admin')

@section('content')
<style>
    .main-content { padding: 10px !important; }
</style>
    <h3>Edit User</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('POST') {{-- or PUT if your route is PUT --}}

        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" value="{{ old('name', $user->name) }}" />
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required />
        </div>

        <div class="mb-3">
            <label>Password (leave blank to keep current)</label>
            <input name="password" type="password" class="form-control" />
        </div>

        <div class="mb-3">
            <label>Role</label>
            <select name="role_id" class="form-control" required>
                <option value="">-- Select Role --</option>
                 <option value="7" {{ old('role_id', $user->role_id) == 7 ? 'selected' : '' }}>Kitchen Manager</option>
                <option value="8" {{ old('role_id' , $user->role_id) == 8 ? 'selected' : '' }}>Cook</option>
                 <option value="9" {{ old('role_id' , $user->role_id) == 9 ? 'selected' : '' }}>Asst. Cooks</option>
                <option value="10" {{ old('role_id', $user->role_id) == 10 ? 'selected' : '' }}>Cleaners</option>
                <option value="11" {{ old('role_id', $user->role_id) == 11 ? 'selected' : '' }}>Laundry</option>
                <option value="1" {{ old('role_id', $user->role_id) == 1 ? 'selected' : '' }}>Admin</option>
                <option value="3" {{ old('role_id', $user->role_id) == 3 ? 'selected' : '' }}>T/Leaders</option>
                <option value="4" {{ old('role_id', $user->role_id) == 4 ? 'selected' : '' }}>Seniors</option>
                <option value="5" {{ old('role_id', $user->role_id) == 5 ? 'selected' : '' }}>Carers</option>
                <option value="6" {{ old('role_id', $user->role_id) == 6 ? 'selected' : '' }}>Bank</option>
        </select>
            </select>
        </div>
        
         <div class="mb-3">
            <label>Category</label>
            <select name="category" class="form-control" required>
                <option value="">Select Category</option>
                <option value="1" {{ old('category', $user->category) == 1 ? 'selected' : '' }}>Kitchen and Houskeeping</option>
                <option value="2" {{ old('category', $user->category) == 2 ? 'selected' : '' }}>Care</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Date of Birth</label>
            <input name="date_of_birth" type="date" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth) }}" />
        </div>

        <div class="mb-3">
            <label>Phone Number</label>
            <input name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}" />
        </div>

        <div class="mb-3">
            <label>Job Title</label>
            <input name="job_title" class="form-control" value="{{ old('job_title', $user->job_title) }}" />
        </div>

        
        <div class="mb-3">
            <label>Rate Per Hour(£)</label>
            <input name="rate" class="form-control" value="{{ old('rate', $user->rate) }}" />
        </div>
        <div class="mb-3">
            <label>Overtime Rate(£)</label>
            <input name="overtime_rate" class="form-control" value="{{ old('overtime_rate', $user->overtime_rate) }}" />
        </div>

        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ old('address', $user->address) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="d-block">Gender</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="Male" {{ old('gender', $user->gender) == 'Male' ? 'checked' : '' }}>
                <label class="form-check-label">Male</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="Female" {{ old('gender', $user->gender) == 'Female' ? 'checked' : '' }}>
                <label class="form-check-label">Female</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" value="Other" {{ old('gender', $user->gender) == 'Other' ? 'checked' : '' }}>
                <label class="form-check-label">Other</label>
            </div>
        </div>

        <div class="mb-3">
            <label>Profile Picture</label>
            <input type="file" name="profile_pic" class="form-control" accept="image/*" />
            @if($user->profile_pic)
                <div class="mt-2">
                   <img src="{{ $user->profile_pic ? url($user->profile_pic) : url('images/default-user.png') }}" alt="Profile" width="80">

                </div>
            @endif
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
@endsection
