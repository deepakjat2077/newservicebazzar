<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'mobile',
        'role', 'role_id', 'status', 'password', 'image',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Deliberately NOT called role(): `role` is already a string column that
     * ~20 views compare against ('super_admin'). A relation of the same name
     * would shadow that column whenever it isn't in the selected attributes.
     */
    public function adminRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * The legacy `role` string column is still read by older views and
     * controllers, so it is kept in sync with the assigned role rather than
     * being replaced outright.
     */
    public function isSuperAdmin(): bool
    {
        if ($this->getAttribute('role') === Role::SUPER_ADMIN) {
            return true;
        }

        return $this->role_id && optional($this->adminRole)->isSuperAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (bool) optional($this->adminRole)->hasPermission($permission);
    }

    /**
     * Where to send this admin when they can't see the dashboard.
     *
     * A role can legitimately be narrowed to, say, listings only — that admin
     * must still land somewhere usable after logging in instead of a 403.
     * Returns null when the role grants nothing at all.
     */
    public function firstAccessibleRoute(): ?string
    {
        $landingPages = [
            'dashboard.view'   => 'admin.dashboard',
            'listing.view'     => 'admin.listing.index',
            'user.view'        => 'admin.users.all',
            'enquiry.view'     => 'admin.enquiry.index',
            'guestchat.view'   => 'admin.guest.chats.index',
            'category.view'    => 'admin.category.index',
            'subcategory.view' => 'admin.subcategories.index',
            'location.view'    => 'admin.location.index',
            'analytics.view'   => 'admin.analytics.index',
            'seo.audit'        => 'admin.seo.audit',
            'package.manage'   => 'admin.package.index',
            'banner.manage'    => 'admin.banners.index',
            'deposit.view'     => 'admin.deposit.all',
            'subscriber.view'  => 'admin.subscriber.index',
            'agent.manage'     => 'admin.agents.index',
            'role.manage'      => 'admin.roles.index',
            'setting.manage'   => 'admin.setting.index',
        ];

        foreach ($landingPages as $permission => $route) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return null;
    }

    /** Every permission key this admin effectively holds. */
    public function permissionKeys(): array
    {
        if ($this->isSuperAdmin()) {
            return Role::allPermissionKeys();
        }

        return optional($this->adminRole)->permissionKeys() ?? [];
    }
}
