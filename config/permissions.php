<?php

/*
|--------------------------------------------------------------------------
| Admin permissions
|--------------------------------------------------------------------------
|
| The single source of truth for what a role can be granted. Keys are stored
| in the role_permission table; the labels below drive the checkbox grid on
| the role edit screen.
|
| Adding a permission here makes it appear in the UI immediately — no
| migration needed. Removing one hides it; stale rows in role_permission are
| simply ignored.
|
| `default_agent` lists what a non-super-admin could already do before roles
| existed, so seeding preserves today's behaviour instead of locking agents
| out of the panel.
|
*/

return [

    'groups' => [

        'Dashboard & Analytics' => [
            'dashboard.view'  => 'View dashboard',
            'analytics.view'  => 'View analytics',
            'analytics.export' => 'Export analytics',
            'history.view'    => 'View login history',
        ],

        'Listings' => [
            'listing.view'    => 'View listings',
            'listing.create'  => 'Add listing',
            'listing.edit'    => 'Edit listing',
            'listing.delete'  => 'Delete listing',
            'listing.approve' => 'Approve / reject listings',
            'listing.export'  => 'Export listings',
        ],

        'Categories' => [
            'category.view'      => 'View categories',
            'category.manage'    => 'Add / edit categories',
            'category.delete'    => 'Delete categories',
            'subcategory.view'   => 'View subcategories',
            'subcategory.manage' => 'Add / edit subcategories',
            'subcategory.delete' => 'Delete subcategories',
        ],

        'Locations' => [
            'location.view'   => 'View locations',
            'location.manage' => 'Add / edit locations',
            'location.delete' => 'Delete locations',
        ],

        'Users' => [
            'user.view'        => 'View users',
            'user.manage'      => 'Add / edit users',
            'user.delete'      => 'Delete users',
            'user.impersonate' => 'Login as user',
            'user.mail'        => 'Send mail to users',
        ],

        'Enquiries & Chats' => [
            'enquiry.view'    => 'View enquiries',
            'enquiry.manage'  => 'Manage enquiries',
            'guestchat.view'  => 'View guest chats',
            'guestchat.reply' => 'Reply to guest chats',
        ],

        'WhatsApp' => [
            'whatsapp.view'   => 'View WhatsApp panel & replies',
            'whatsapp.send'   => 'Send WhatsApp messages',
            'whatsapp.delete' => 'Delete WhatsApp messages & chats',
        ],

        'Content' => [
            'banner.manage'   => 'Manage banners',
            'seasonal.manage' => 'Manage seasonal sections',
            'home.manage'     => 'Manage home content sections',
            'service.manage'  => 'Manage services',
            'keyword.manage'  => 'Manage service keywords',
            'hbb.manage'      => 'Manage HBB section',
        ],

        'SEO' => [
            'seo.audit'    => 'View SEO audit',
            'seo.settings' => 'Edit SEO settings',
            'schema.manage' => 'Manage schema settings',
        ],

        'Payments' => [
            'package.manage' => 'Manage packages',
            'gateway.manage' => 'Manage payment gateways',
            'deposit.view'   => 'View deposits',
        ],

        'Communication' => [
            'subscriber.view' => 'View subscribers',
            'subscriber.mail' => 'Mail subscribers',
            'notification.manage' => 'Manage notification templates',
        ],

        'System' => [
            'setting.manage'  => 'General settings',
            'language.manage' => 'Manage languages',
            'theme.manage'    => 'Manage themes',
            'social.manage'   => 'Manage social sites',
            'agent.manage'    => 'Manage admins / agents',
            'role.manage'     => 'Manage roles & permissions',
        ],
    ],

    /*
     * What the seeded "Agent" role starts with — everything an agent could
     * already reach before roles existed. The super-admin-only actions
     * (deletes, impersonation, agents, roles, settings) are left out.
     */
    'default_agent' => [
        'dashboard.view',
        'analytics.view',
        'history.view',
        'listing.view',
        'listing.create',
        'listing.edit',
        'listing.approve',
        'category.view',
        'category.manage',
        'subcategory.view',
        'subcategory.manage',
        'location.view',
        'location.manage',
        'user.view',
        'user.manage',
        'user.mail',
        'enquiry.view',
        'enquiry.manage',
        'whatsapp.view',
        'whatsapp.send',
        'guestchat.view',
        'guestchat.reply',
        'banner.manage',
        'seasonal.manage',
        'home.manage',
        'service.manage',
        'keyword.manage',
        'hbb.manage',
        'seo.audit',
        'package.manage',
        'deposit.view',
        'subscriber.view',
    ],
];
