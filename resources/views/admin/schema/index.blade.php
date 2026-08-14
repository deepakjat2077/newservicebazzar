@extends('admin.layouts.master')

@section('title', 'Schema Settings')

@section('content')

<style>
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 150px;
        height: 32px;
        margin-bottom: 0;
    }

    .switch-toggle input {
        display: none;
    }

    .slider-toggle {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #dc3545;
        border-radius: 4px;
        transition: .3s;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        line-height: 32px;
        overflow: hidden;
    }

    .slider-toggle:before {
        content: "";
        position: absolute;
        height: 26px;
        width: 38px;
        left: 3px;
        top: 3px;
        background-color: #fff;
        border-radius: 3px;
        transition: .3s;
        z-index: 2;
    }

    .switch-toggle input:checked + .slider-toggle {
        background-color: #28a745;
    }

    .switch-toggle input:checked + .slider-toggle:before {
        transform: translateX(106px);
    }

    .on-text,
    .off-text {
        position: absolute;
        width: 100%;
        left: 0;
        top: 0;
        z-index: 1;
    }

    .on-text {
        display: none;
    }

    .off-text {
        display: block;
    }

    .switch-toggle input:checked + .slider-toggle .on-text {
        display: block;
    }

    .switch-toggle input:checked + .slider-toggle .off-text {
        display: none;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Schema Settings</h5>

                <button type="button"
                        class="btn btn-primary btn-sm"
                        data-toggle="modal"
                        data-target="#addSchemaModal">
                    <i class="fas fa-plus"></i> Add Schema
                </button>
            </div>

            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Page</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($schemas as $key => $schema)

                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>{{ $schema->page }}</td>

                                <td>
                                    @if($schema->status == 1)
                                        <span class="badge badge-success">
                                            Enable
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Disable
                                        </span>
                                    @endif
                                </td>

                                <td>

                                    <button type="button"
                                            class="btn btn-info btn-sm"
                                            data-toggle="modal"
                                            data-target="#editSchemaModal{{ $schema->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('admin.schema.delete', $schema->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>

                                </td>
                            </tr>



                            {{-- Edit Modal --}}
                            <div class="modal fade"
                                 id="editSchemaModal{{ $schema->id }}"
                                 tabindex="-1">

                                <div class="modal-dialog modal-lg">

                                    <div class="modal-content">

                                        <form action="{{ route('admin.schema.update', $schema->id) }}"
                                              method="POST">

                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">

                                                <h5 class="modal-title">
                                                    Edit Schema Setting
                                                </h5>

                                                <button type="button"
                                                        class="close"
                                                        data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>

                                            </div>

                                            <div class="modal-body">

                                                <div class="form-group">
                                                    <label>Page</label>

                                                    <input type="text"
                                                           name="page"
                                                           class="form-control"
                                                           value="{{ $schema->page }}"
                                                           required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Schema JSON</label>

                                                    <textarea name="schema_json"
                                                              rows="12"
                                                              class="form-control"
                                                              required>{{ $schema->schema_json }}</textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label>Status</label><br>

                                                    <input type="hidden"
                                                           name="status"
                                                           value="0">

                                                    <label class="switch-toggle">

                                                        <input type="checkbox"
                                                               name="status"
                                                               value="1"
                                                               {{ $schema->status == 1 ? 'checked' : '' }}>

                                                        <span class="slider-toggle">
                                                            <span class="on-text">Enable</span>
                                                            <span class="off-text">Disable</span>
                                                        </span>

                                                    </label>
                                                </div>

                                            </div>

                                            <div class="modal-footer">

                                                <button type="button"
                                                        class="btn btn-dark"
                                                        data-dismiss="modal">
                                                    Cancel
                                                </button>

                                                <button type="submit"
                                                        class="btn btn-primary">
                                                    Update
                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <tr>
                                <td colspan="4"
                                    class="text-center">
                                    No schema found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</div>




{{-- Add Modal --}}
<div class="modal fade"
     id="addSchemaModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form action="{{ route('admin.schema.store') }}"
                  method="POST">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Add Schema Setting
                    </h5>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Page</label>

                        <input type="text"
                               name="page"
                               class="form-control"
                               placeholder="home / about / contact"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Schema JSON</label>

                        <textarea name="schema_json"
                                  rows="12"
                                  class="form-control"
                                  required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label><br>

                        <input type="hidden"
                               name="status"
                               value="0">

                        <label class="switch-toggle">

                            <input type="checkbox"
                                   name="status"
                                   value="1"
                                   checked>

                            <span class="slider-toggle">
                                <span class="on-text">Enable</span>
                                <span class="off-text">Disable</span>
                            </span>

                        </label>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-dark"
                            data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Save
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection