@extends('admin.layouts.master')
@section('title', 'Sales Users')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Team Users</h5>
                    <a href="{{ route('admin.sales_users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Emp Code</th>
                                <th>Designation</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('admin.sales_team.show', $u->id) }}" title="Open details: location, map, dispositions, month figures">
                                        <strong>{{ $u->name }}</strong>
                                    </a>
                                </td>
                                <td><code>{{ $u->empcode }}</code></td>
                                <td>{{ $u->designation ?? '–' }}</td>
                                <td>{{ $u->city }}</td>
                                <td>{{ $u->phone ?? '–' }}</td>
                                <td>
                                    <form action="{{ route('admin.sales_users.toggle', $u->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="badge badge-{{ $u->status ? 'success' : 'danger' }}" style="border:none;cursor:pointer;">
                                            {{ $u->status ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sales_team.show', $u->id) }}" class="btn btn-sm btn-info" title="Details">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                    <a href="{{ route('admin.sales_users.edit', $u->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.sales_users.destroy', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No sales users yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $users->links() }}</div>
        </div>
    </div>
</div>

@endsection
