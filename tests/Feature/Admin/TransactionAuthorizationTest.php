<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bde_member_cannot_access_transactions(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.transactions.index'))->assertForbidden();
    }

    public function test_super_admin_can_access_transactions(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin.transactions.index'))->assertOk();
    }

    public function test_bde_member_still_accesses_other_admin_pages(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.clubs.index'))->assertOk();
    }
}
