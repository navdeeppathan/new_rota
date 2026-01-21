@extends('superadmin.dashboard')

@section('content')
<div class="container mt-4">

    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Create User</h5>
        </div>

        <div class="card-body">

            {{-- Success --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.users.store') }}">
                @csrf

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Manager Name</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter Manager Name"
                            value="{{ old('name') }}"
                            required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Registeration Number</label>
                        <input type="text"
                            name="registeration_no"
                            class="form-control"
                            placeholder="Enter Registeration Number"
                            value="{{ old('registeration_no') }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text"
                            name="phone_number"
                            class="form-control"
                            placeholder="Enter Phone Number"
                            value="{{ old('phone_number') }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text"
                            name="address"
                            class="form-control"
                            placeholder="Enter Address"
                            value="{{ old('address') }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Website Url</label>
                        <input type="text"
                            name="website_url"
                            class="form-control"
                            placeholder="Enter Website Url"
                            value="{{ old('website_url') }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter Email"
                            value="{{ old('email') }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password"
                            name="password"
                            placeholder="Enter Password"
                            class="form-control"
                            required>
                    </div>
                </div>



                <div class="d-flex justify-content-end">
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Save User
                    </button>

                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-danger ms-3">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
