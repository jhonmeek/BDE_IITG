<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistersPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_messages_index_is_paginated(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 30; $i++) {
            ContactMessage::create([
                'name' => "Contact {$i}",
                'email' => "contact{$i}@example.com",
                'message' => 'Message de test suffisamment long.',
                'status' => 'new',
            ]);
        }

        $this->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ContactMessages/Index')
                ->has('messages.data', 25)
                ->has('messages.links')
            );
    }
}
