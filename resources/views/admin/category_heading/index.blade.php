@extends('admin.layouts.master')

@section('title', 'Category Heading List')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Category Heading List</h5>
                <a href="{{ route('admin.categoryHeading.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Heading</th>
                           
                            <th>Heading Link</th>
                            <th>Order By</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->heading }}</td>
                                
                                <td>{{ $item->heading_link }}</td>
                                <td>{{ $item->order_by }}</td>
                                <td>
                                    @if($item->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.categoryHeading.edit', $item->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('admin.categoryHeading.status', $item->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-sync"></i>
                                    </a>

                                    <a href="{{ route('admin.categoryHeading.delete', $item->id) }}"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection