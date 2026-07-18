<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        return Event::create([
            'name' => 'Tournoi de foot',
            'slug' => 'tournoi-de-foot',
            'location' => 'Stade du campus',
            'description' => 'Tournoi inter-classes.',
            'starts_at' => now()->addWeeks(2),
            'registration_enabled' => true,
            'is_published' => true,
            ...$overrides,
        ]);
    }

    public function test_student_can_register_once(): void
    {
        $event = $this->makeEvent();

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie Ndong',
            'email' => 'marie@example.com',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_same_email_cannot_register_twice_for_same_event(): void
    {
        $event = $this->makeEvent();

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie Ndong',
            'email' => 'marie@example.com',
        ]);

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie N.',
            'email' => 'MARIE@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_registration_is_refused_when_event_is_full(): void
    {
        $event = $this->makeEvent(['capacity' => 1]);

        EventRegistration::create([
            'event_id' => $event->id,
            'full_name' => 'Premier Inscrit',
            'email' => 'premier@example.com',
            'status' => 'pending',
        ]);

        $this->post(route('events.register', $event), [
            'full_name' => 'Deuxieme Inscrit',
            'email' => 'deuxieme@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_registrations', 1);
    }
}
