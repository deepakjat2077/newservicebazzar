@extends('admin.layouts.master')

@section('title', $title)

@section('content')

<style>
    .role-card{
        border:1px solid #e9ecef;
        border-radius:12px;
        background:#fff;
        height:100%;
        display:flex;
        flex-direction:column;
    }
    .role-card-head{
        padding:16px 18px 12px;
        border-bottom:1px solid #f1f3f5;
    }
    .role-name{
        font-size:17px;
        font-weight:700;
        margin:0;
    }
    .role-desc{
        font-size:12.5px;
        color:#6c757d;
        margin-top:5px;
        min-height:34px;
    }
    .role-card-body{
        padding:14px 18px;
        flex:1;
    }
    .role-metric{
        display:flex;
        align-items:baseline;
        gap:7px;
        font-size:13px;
        color:#495057;
        margin-bottom:6px;
    }
    .role-metric b{
        font-size:17px;
        color:#212529;
    }
    .perm-bar{
        height:7px;
        background:#edf0f2;
        border-radius:20px;
        overflow:hidden;
        margin-top:10px;
    }
    .perm-bar span{
        display:block;
        height:100%;
        background:#1c7ed6;
    }
    .role-card-foot{
        padding:12px 18px;
        border-top:1px solid #f1f3f5;
        display:flex;
        gap:8px;
    }
    .locked-pill{
        font-size:10.5px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.04em;
        background:#fff4e0;
        color:#b45309;
        padding:3px 8px;
        border-radius:5px;
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <div>
        <h4 class="mb-1">{{ $title }}</h4>
        <small class="text-muted">
            Har role ko alag permissions do. Admin ko role assign karne ke liye
            <a href="{{ route('admin.agents.index') }}">Agents</a> me jao.
        </small>
    </div>

    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="las la-plus"></i> Add Role
    </a>
</div>

<div class="row">
    @forelse($roles as $role)
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="role-card">

                <div class="role-card-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="role-name">{{ $role->name }}</h5>

                        @if($role->is_locked)
                            <span class="locked-pill">
                                <i class="las la-lock"></i> Locked
                            </span>
                        @endif
                    </div>

                    <div class="role-desc">{{ $role->description }}</div>
                </div>

                <div class="role-card-body">
                    @php
                        $granted = $role->is_locked && $role->slug === \App\Models\Role::SUPER_ADMIN
                            ? $totalPermissions
                            : $role->permissions_count;

                        $pct = $totalPermissions ? round($granted / $totalPermissions * 100) : 0;
                    @endphp

                    <div class="role-metric">
                        <b>{{ $granted }}</b> of {{ $totalPermissions }} permissions
                    </div>

                    <div class="perm-bar">
                        <span style="width:{{ $pct }}%"></span>
                    </div>

                    <div class="role-metric mt-3">
                        <b>{{ $role->admins_count }}</b>
                        admin{{ $role->admins_count == 1 ? '' : 's' }} using this role
                    </div>
                </div>

                <div class="role-card-foot">
                    @if($role->is_locked)
                        <button class="btn btn-light btn-sm" disabled title="Locked role">
                            <i class="las la-lock"></i> Locked
                        </button>
                    @else
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm">
                            <i class="las la-key"></i> Permissions
                        </a>

                        <form action="{{ route('admin.roles.delete', $role->id) }}" method="POST"
                              onsubmit="return confirm('Delete the {{ $role->name }} role?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-sm"
                                    {{ $role->admins_count > 0 ? 'disabled' : '' }}
                                    title="{{ $role->admins_count > 0 ? 'Reassign its admins first' : 'Delete role' }}">
                                <i class="las la-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted p-5">
                    No roles yet.
                </div>
            </div>
        </div>
    @endforelse
</div>

@endsection
