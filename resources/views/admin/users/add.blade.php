@extends('admin.layouts.master')

@section('title','Add User')

@section('content')

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">

            <div class="card-header d-flex justify-content-between">
                <h5>Add New User</h5>
                <a href="{{ route('admin.users.all') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.users.register') }}" method="POST">
                @csrf

                <div class="card-body">

                   <div class="form-row">
    
    <!-- First Name -->
    <div class="form-group col-md-4">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" required>
    </div>

    <!-- Last Name -->
    <div class="form-group col-md-4">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" required>
    </div>
   <div class="form-group col-md-4">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

</div>

                    <!-- Username -->
                 
                   <div class="form-row">

    <!-- Mobile -->
    <div class="form-group col-md-6">
        <label>Mobile</label>
        <input type="text" name="mobile" class="form-control" required>
    </div>

    <!-- Email -->
    <div class="form-group col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

</div>

                 <div class="form-row">
    <!-- Password -->
    <div class="form-group col-md-6">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <!-- Confirm Password -->
    <div class="form-group col-md-6">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>
</div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection