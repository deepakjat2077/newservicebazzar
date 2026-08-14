<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /** Slug of the role that always holds every permission. */
    public const SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'name', 'slug', 'description', 'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }

    /**
     * Permission keys this role holds. Super admin implicitly holds every
     * key in the registry, so nobody can accidentally strip its access.
     */
    public function permissionKeys(): array
    {
        if ($this->isSuperAdmin()) {
            return self::allPermissionKeys();
        }

        return $this->permissions->pluck('permission')->all();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissions->contains('permission', $permission);
    }

    /**
     * Replace this role's grants with exactly the keys given. Unknown keys
     * are dropped so a tampered form can't store junk.
     */
    public function syncPermissions(array $permissions): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        $valid = array_values(array_intersect(
            array_unique($permissions),
            self::allPermissionKeys()
        ));

        $this->permissions()->delete();

        foreach ($valid as $permission) {
            $this->permissions()->create(['permission' => $permission]);
        }

        $this->load('permissions');
    }

    /** Every permission key defined in config/permissions.php. */
    public static function allPermissionKeys(): array
    {
        $keys = [];

        foreach (config('permissions.groups', []) as $group) {
            $keys = array_merge($keys, array_keys($group));
        }

        return $keys;
    }
}
