@extends('admin.layouts.master')

@section('title', $title)

@section('content')

<style>
    .perm-group{
        border:1px solid #e9ecef;
        border-radius:12px;
        background:#fff;
        margin-bottom:14px;
        overflow:hidden;
    }
    .perm-group-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:11px 16px;
        background:#f8f9fa;
        border-bottom:1px solid #eef0f2;
    }
    .perm-group-title{
        font-size:14px;
        font-weight:700;
        color:#212529;
        margin:0;
    }
    .perm-group-count{
        font-size:11.5px;
        font-weight:700;
        color:#495057;
        background:#e9ecef;
        border-radius:20px;
        padding:2px 9px;
        margin-left:8px;
    }
    .perm-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(255px, 1fr));
        gap:2px 18px;
        padding:12px 16px;
    }
    .perm-item{
        display:flex;
        align-items:center;
        gap:9px;
        padding:7px 0;
        font-size:13.5px;
        cursor:pointer;
        margin:0;
    }
    .perm-item input{
        width:16px;
        height:16px;
        cursor:pointer;
        flex:0 0 16px;
    }
    .perm-item code{
        font-size:11px;
        color:#adb5bd;
        background:none;
        padding:0;
    }
    /*
     * NOT `.toggle-group` — that class belongs to bootstrap-toggle.min.css,
     * which the admin layout loads, and it applies
     * `position:absolute; width:200%; top:0; bottom:0` to it. Reusing the
     * name turned every "Toggle all" span into a full-page invisible overlay
     * that swallowed clicks meant for the name field and the checkboxes.
     */
    .perm-toggle-all{
        font-size:12px;
        font-weight:600;
        color:#1c7ed6;
        cursor:pointer;
        user-select:none;
    }
    .perm-toggle-all:hover{ text-decoration:underline; }
    .sticky-actions{
        position:sticky;
        bottom:0;
        background:#fff;
        border-top:1px solid #e9ecef;
        padding:12px 16px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        z-index:5;
    }
    .locked-note{
        background:#fff4e0;
        border:1px solid #ffe3b0;
        color:#b45309;
        border-radius:10px;
        padding:14px 16px;
        font-size:13.5px;
    }
</style>

@if($role->exists && $role->is_locked)

    {{--
        A locked role has nothing to configure. Rendering the normal form with
        every field disabled just looked broken, so this is a read-only
        summary instead.
    --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="las la-lock"></i> {{ $role->name }}
            </h4>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-dark btn-sm">
                <i class="las la-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <div class="locked-note mb-4">
                <strong>{{ $role->name }}</strong> ek locked role hai. Isko hamesha
                <strong>saari {{ count(\App\Models\Role::allPermissionKeys()) }} permissions</strong>
                milti hain aur ye edit nahi ho sakta — isi se guarantee rehti hai ki panel
                se kabhi lockout na ho.

                <div class="mt-2">
                    Permissions control karni hain to
                    <a href="{{ route('admin.roles.create') }}">naya role banao</a>
                    ya koi doosra role edit karo.
                </div>
            </div>

            @foreach($groups as $groupName => $permissions)
                <div class="perm-group">
                    <div class="perm-group-head">
                        <div>
                            <span class="perm-group-title">{{ $groupName }}</span>
                            <span class="perm-group-count">{{ count($permissions) }}/{{ count($permissions) }}</span>
                        </div>

                        <span class="text-success" style="font-size:12px;font-weight:600">
                            <i class="las la-check-circle"></i> Full access
                        </span>
                    </div>

                    <div class="perm-grid">
                        @foreach($permissions as $key => $label)
                            <div class="perm-item">
                                <i class="las la-check-circle text-success" style="font-size:17px"></i>
                                <span>
                                    {{ $label }}<br>
                                    <code>{{ $key }}</code>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@else

    <form action="{{ $role->exists ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}"
          method="POST" id="roleForm">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $title }}</h4>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-dark btn-sm">
                    <i class="las la-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label>Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $role->name) }}"
                               placeholder="e.g. Content Manager" required>

                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-7 mb-3">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description', $role->description) }}"
                               placeholder="Ye role kya kar sakta hai">
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div>
                        <h5 class="mb-1">Permissions</h5>
                        <small class="text-muted">
                            <span id="selectedCount">0</span>
                            of {{ count(\App\Models\Role::allPermissionKeys()) }} selected
                        </small>
                    </div>

                    <div>
                        <button type="button" class="btn btn-light btn-sm" id="checkAll">Select all</button>
                        <button type="button" class="btn btn-light btn-sm" id="uncheckAll">Clear all</button>
                    </div>
                </div>

                @foreach($groups as $groupName => $permissions)
                    @php
                        $groupHeld = collect(array_keys($permissions))
                            ->filter(fn ($key) => in_array($key, old('permissions', $held), true))
                            ->count();
                    @endphp

                    <div class="perm-group" data-group="{{ Str::slug($groupName) }}">
                        <div class="perm-group-head">
                            <div>
                                <span class="perm-group-title">{{ $groupName }}</span>
                                <span class="perm-group-count">
                                    <span class="group-selected">{{ $groupHeld }}</span>/{{ count($permissions) }}
                                </span>
                            </div>

                            <span class="perm-toggle-all">Toggle all</span>
                        </div>

                        <div class="perm-grid">
                            @foreach($permissions as $key => $label)
                                <label class="perm-item">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $key }}"
                                           class="perm-check"
                                           {{ in_array($key, old('permissions', $held), true) ? 'checked' : '' }}>

                                    <span>
                                        {{ $label }}<br>
                                        <code>{{ $key }}</code>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sticky-actions">
                <small class="text-muted">
                    Super Admin role pe ye settings lagu nahi hoti — usko hamesha full access rehta hai.
                </small>

                <div>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save"></i> {{ $role->exists ? 'Update Role' : 'Create Role' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

@endif

@push('js')
<script>
(function ($) {
    "use strict";

    // Locked roles render a read-only view with no form.
    if (!$('#roleForm').length) {
        return;
    }

    function refreshCounts() {
        $('#selectedCount').text($('.perm-check:checked').length);

        $('.perm-group').each(function () {
            var $group = $(this);
            $group.find('.group-selected').text($group.find('.perm-check:checked').length);
        });
    }

    $('.perm-toggle-all').on('click', function () {
        var $boxes = $(this).closest('.perm-group').find('.perm-check');

        // A partially selected group fills up first; a full one clears.
        var makeChecked = $boxes.filter(':checked').length !== $boxes.length;

        $boxes.prop('checked', makeChecked);
        refreshCounts();
    });

    $('#checkAll').on('click', function () {
        $('.perm-check').prop('checked', true);
        refreshCounts();
    });

    $('#uncheckAll').on('click', function () {
        $('.perm-check').prop('checked', false);
        refreshCounts();
    });

    $(document).on('change', '.perm-check', refreshCounts);

    refreshCounts();
})(jQuery);
</script>
@endpush

@endsection
