<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Creates the two roles the panel already behaved as if it had, and points
 * every existing admin at the right one based on its legacy `role` string.
 *
 * Safe to re-run: roles are matched by slug and admins are only touched when
 * they have no role_id yet.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => Role::SUPER_ADMIN],
            [
                'name'        => 'Super Admin',
                'description' => 'Full access to everything. Cannot be edited or deleted.',
                'is_locked'   => true,
            ]
        );

        $agent = Role::firstOrCreate(
            ['slug' => 'agent'],
            [
                'name'        => 'Agent',
                'description' => 'Day-to-day panel access without system settings or deletions.',
                'is_locked'   => false,
            ]
        );

        // Only seed the agent grants on first creation — re-running must not
        // wipe permissions an admin has since customised.
        if ($agent->permissions()->count() === 0) {
            $agent->syncPermissions(config('permissions.default_agent', []));
        }

        Admin::whereNull('role_id')->get()->each(function (Admin $admin) use ($superAdmin, $agent) {
            $admin->role_id = $admin->getAttribute('role') === Role::SUPER_ADMIN
                ? $superAdmin->id
                : $agent->id;

            $admin->save();
        });
    }
}
