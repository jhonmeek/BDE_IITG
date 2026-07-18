<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        if (! app()->environment('local')) {
            return;
        }

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

        $admin->syncRoles(['super_admin']);

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

        $member->syncRoles(['membre_bde']);

        $this->call(DemoContentSeeder::class);
    }
}
