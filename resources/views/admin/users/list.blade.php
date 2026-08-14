@extends('admin.layouts.master')
@section('content')
@section('title',$title)
<div class="row">
	<div class="col-md-12">
	<div class="card">
    
    <!-- ADD USER BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-3 p-3">
        <h5 class="mb-0">@lang('Users List')</h5>

        {{-- <a href="{{ route('admin.users.register') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Add User
        </a> --}}
    </div>

    <div class="card-body p-0">
				<div class="table-responsive">
	              <table class="table">
	                  <thead>
	                    <tr>
	                      <th scope="col">@lang('SL')</th>
	                      <th scope="col">@lang('Name')</th>
	                      <th scope="col">@lang('Username')</th>
	                      <th scope="col">@lang('Email')</th>
	                      <th scope="col">@lang('Balance')</th>
	                      <th scope="col">@lang('Action')</th>
	                    </tr>
	                  </thead>
	                  <tbody class="cur-body">
	                  	@forelse($users as $user)
	                  		<tr>
	                  			<td>{{ $loop->iteration }}</td>
	                  			<td>
	                  				{{ __($user->fullname()) }}
	                  			</td>
	                  			<td>{{'@'}}{{ $user->username }}</td>
	                  			<td>{{ $user->email }}</td>
	                  			<td>{{ $setting->currency_symbol }}{{ $user->balance + 0 }}</td>
	                  			<td>
	                  				<a href="{{ route('admin.users.details',$user->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>

						
									@if(auth()->guard('admin')->user()->role == 'super_admin')
    <!-- Delete -->
    <button 
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $user->id }}"
        data-url="{{ route('admin.users.delete', $user->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $user->id }}" 
          action="{{ route('admin.users.delete', $user->id) }}" 
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
	@endif
	                  			</td>
	                  		</tr>
	                  	@empty
	                  		<tr>
	                  			<td class="text-center" colspan="100%">@lang('Data not found')</td>
	                  		</tr>
	                  	@endforelse
	                  </tbody>
	              </table>
	          </div>
			</div>
			<div class="card-footer">
				{{$users->links()}}
			</div>
		</div>
	</div>
</div>

@push('js')
	
<script>
	
$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    let id = $(this).data('id');
    let form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: "Delete karne ke baad recover nahi hoga!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush
@endsection
