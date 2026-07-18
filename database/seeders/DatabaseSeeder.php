<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
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

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $memberRole = Role::findOrCreate('membre_bde', 'web');
        Role::findOrCreate('public', 'web');

        $superAdmin->syncPermissions($permissions);
        $memberRole->syncPermissions($permissions);

        $admin = User::updateOrCreate(
            ['email' => 'admin@bde-iitg.test'],
            [
                'name' => 'Administrateur BDE',
                'username' => 'admin',
                'phone' => '+24100000000',
                'title' => 'Super administrateur',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $admin->syncRoles([$superAdmin]);

        $member = User::updateOrCreate(
            ['email' => 'membre@bde-iitg.test'],
            [
                'name' => 'Membre BDE',
                'username' => 'membre',
                'phone' => '+24111111111',
                'title' => 'Membre bureau',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $member->syncRoles([$memberRole]);

        $this->call(DemoContentSeeder::class);
    }
}
