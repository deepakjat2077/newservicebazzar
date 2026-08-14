@extends('admin.layouts.master')
@section('title', $title)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }}</h4>
                <a href="{{ route('admin.agents.index') }}" class="btn btn-dark btn-sm">Back</a>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.agents.update', $agent->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $agent->first_name) }}" required>
                            @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $agent->last_name) }}" required>
                            @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $agent->username) }}" required>
                            @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email) }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $agent->mobile) }}">
                            @error('mobile') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Role</label>
                            <select name="role_id" class="form-control" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $agent->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('role_id') <small class="text-danger">{{ $message }}</small> @enderror

                            <small class="text-muted d-block mt-1">
                                Permissions <a href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a> me set hoti hain.
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ old('status', $agent->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $agent->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            <small class="text-muted">Password change nahi karna ho to blank chhod do.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Profile Image</label>
                            <input type="file" name="image" class="form-control">
                            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Current Image</label><br>
                            @if($agent->image)
                                <img src="{{ asset('assets/central/images/'.$agent->image) }}" width="70" height="70" style="border-radius:50%;object-fit:cover;">
                            @else
                                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" width="70" height="70" style="border-radius:50%;object-fit:cover;">
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Agent</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection