<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bde_member_cannot_access_user_management(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_create_a_member_account(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.users.store'), [
            'name' => 'Nouveau Membre',
            'email' => 'nouveau@bde-iitg.org',
            'password' => 'MotDePasseSolide2026',
            'role' => 'membre_bde',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user = User::where('email', 'nouveau@bde-iitg.org')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('membre_bde'));
        $this->assertTrue($user->is_active);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->delete(route('admin.users.destroy', $admin))->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_can_deactivate_another_account(): void
    {
        $this->actingAsSuperAdmin();

        $member = User::factory()->create(['is_active' => true]);
        $member->syncRoles(['membre_bde']);

        $this->put(route('admin.users.update', $member), [
            'name' => $member->name,
            'email' => $member->email,
            'role' => 'membre_bde',
            'is_active' => false,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->is_active);
    }
}
