@extends('admin.layouts.master')

@section('title', 'Home Content Section')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Home Content Section</h4>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-sm">
                    Back
                </a>
            </div>

            <div class="card-body">

                @if(isset($section) && $section)

                    <form action="{{ route('admin.home.content.sections.update', $section->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Title</label>

                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       value="{{ old('title', $section->title) }}"
                                       required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Content</label>

                                <textarea name="content"
                                          class="form-control richEditor"
                                          rows="10"
                                          required>{{ old('content', $section->content) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Status</label>

                                <select name="status" class="form-control">
                                    <option value="1"
                                        {{ $section->status == 1 ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="0"
                                        {{ $section->status == 0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Section
                        </button>

                    </form>

                @else

                    <form action="{{ route('admin.home.content.sections.store') }}"
                          method="POST">

                        @csrf

                        <div class="row">

                            <div class="col-md-12 mb-3">
                                <label>Title</label>

                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       value="{{ old('title') }}"
                                       required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Content</label>

                                <textarea name="content"
                                          class="form-control richEditor"
                                          rows="10"
                                          required>{{ old('content') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Status</label>

                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success">
                            Add Section
                        </button>

                    </form>

                @endif

            </div>

        </div>

    </div>
</div>

@endsection