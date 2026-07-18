<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage dashboard',
        'manage bureau members',
        'manage clubs',
        'manage events',
        'manage registrations',
        'manage transactions',
        'manage documents',
        'manage historical entries',
        'manage media assets',
        'manage contact messages',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $member = Role::findOrCreate('membre_bde', 'web');

        $superAdmin->syncPermissions(self::PERMISSIONS);
        $member->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
