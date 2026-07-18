<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_a_super_admin(): void
    {
        $this->artisan('bde:create-admin', [
            'email' => 'presi@bde-iitg.org',
            '--name' => 'Presidente BDE',
            '--password' => 'MotDePasseSolide2026',
        ])->assertExitCode(0);

        $user = User::where('email', 'presi@bde-iitg.org')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('MotDePasseSolide2026', $user->password));
    }

    public function test_command_rejects_weak_password(): void
    {
        $this->artisan('bde:create-admin', [
            'email' => 'presi@bde-iitg.org',
            '--password' => 'abc',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }
}
