<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsSuperAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['super_admin']);

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsBdeMember(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['membre_bde']);

        $this->actingAs($user);

        return $user;
    }
}
