@extends('admin.layouts.master')

@section('title', 'Seasonal Section List')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Seasonal Section List</h5>

                <a href="{{ route('admin.seasonal.sections.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Subtitle</th>
                          
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($sections as $key => $item)

                            {{-- A Category::whereIn() ran here once per row and
                                 nothing in this table ever read the result. --}}

                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>{{ $item->title }}</td>

                              

                                <td>{{ $item->subtitle }}</td>

                               

                                <td>
                                    @if($item->status)
                                        <span class="badge badge-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.seasonal.sections.edit', $item->id) }}"
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.seasonal.sections.delete', $item->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>
@endsection