<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFormsThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Etudiant Test',
            'email' => 'etudiant@example.com',
            'message' => 'Bonjour, ceci est un message de test valide.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload)->assertRedirect();
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
    }

    public function test_event_registration_is_rate_limited(): void
    {
        $event = Event::create([
            'name' => 'Gala annuel',
            'slug' => 'gala-annuel',
            'location' => 'Campus',
            'description' => 'Le grand gala du BDE.',
            'starts_at' => now()->addMonth(),
            'registration_enabled' => true,
            'is_published' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('events.register', $event), [
                'full_name' => "Participant {$i}",
                'email' => "participant{$i}@example.com",
            ])->assertRedirect();
        }

        $this->post(route('events.register', $event), [
            'full_name' => 'Participant 6',
            'email' => 'participant6@example.com',
        ])->assertStatus(429);
    }

    public function test_club_registration_is_rate_limited(): void
    {
        $club = Club::create([
            'name' => 'Club Echecs',
            'slug' => 'club-echecs',
            'lead_name' => 'Alex',
            'description' => 'Club d echecs du campus.',
            'is_published' => true,
        ]);

        $payload = [
            'last_name' => 'Test',
            'first_name' => 'Etudiant',
            'class_name' => 'L3 Info',
            'club_ids' => [$club->id],
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('clubs.register'), $payload)->assertRedirect();
        }

        $this->post(route('clubs.register'), $payload)->assertStatus(429);
    }
}
