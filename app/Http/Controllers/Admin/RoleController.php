<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['admins', 'permissions'])
            ->orderByDesc('is_locked')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', [
            'title' => 'Roles & Permissions',
            'roles' => $roles,
            'totalPermissions' => count(Role::allPermissionKeys()),
        ]);
    }

    public function create()
    {
        return view('admin.roles.form', [
            'title'  => 'Add Role',
            'role'   => new Role(),
            'groups' => config('permissions.groups', []),
            'held'   => [],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:80|unique:roles,name',
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'slug'        => $this->uniqueSlug($request->name),
            'description' => $request->description,
            'is_locked'   => false,
        ]);

        $role->syncPermissions($request->input('permissions', []));

        $alert = ['success', 'Role created successfully'];

        return redirect()->route('admin.roles.index')->withAlert($alert);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('admin.roles.form', [
            'title'  => 'Edit Role',
            'role'   => $role,
            'groups' => config('permissions.groups', []),
            'held'   => $role->permissionKeys(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        // The super admin role is what guarantees somebody can always get
        // back in — its name and grants are not editable.
        if ($role->is_locked) {
            $alert = ['danger', 'The ' . $role->name . ' role is locked and cannot be edited'];

            return back()->withAlert($alert);
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->ignore($role->id)],
            'description'   => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        $role->syncPermissions($request->input('permissions', []));

        $alert = ['success', 'Role updated successfully'];

        return redirect()->route('admin.roles.index')->withAlert($alert);
    }

    public function delete($id)
    {
        $role = Role::withCount('admins')->findOrFail($id);

        if ($role->is_locked) {
            $alert = ['danger', 'The ' . $role->name . ' role is locked and cannot be deleted'];

            return back()->withAlert($alert);
        }

        // Deleting a role in use would leave those admins with no
        // permissions at all — make the admin reassign them first.
        if ($role->admins_count > 0) {
            $alert = ['danger', $role->admins_count . ' admin(s) still use this role. Move them to another role first.'];

            return back()->withAlert($alert);
        }

        $role->delete();

        $alert = ['success', 'Role deleted successfully'];

        return redirect()->route('admin.roles.index')->withAlert($alert);
    }

    /**
     * Admins assigned to a role, so the index can show who is affected
     * before anyone edits or deletes it.
     */
    public function members($id)
    {
        $role = Role::findOrFail($id);

        $admins = Admin::where('role_id', $role->id)
            ->select(['id', 'first_name', 'last_name', 'username', 'email', 'status'])
            ->get();

        return response()->json([
            'status' => true,
            'role'   => $role->name,
            'admins' => $admins,
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::strToKey(Str::slug($name)) ?: 'role';
        $slug = $base;
        $i    = 2;

        while (Role::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }

        return $slug;
    }
}
