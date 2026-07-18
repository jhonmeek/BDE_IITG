<?php

namespace Tests\Feature;

use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubRegistrationRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeClub(array $overrides = []): Club
    {
        static $i = 0;
        $i++;

        return Club::create([
            'name' => "Club Test {$i}",
            'slug' => "club-test-{$i}",
            'lead_name' => 'Responsable',
            'description' => 'Description du club.',
            'is_published' => true,
            ...$overrides,
        ]);
    }

    public function test_cannot_register_to_unpublished_club(): void
    {
        $club = $this->makeClub(['is_published' => false]);

        $this->post(route('clubs.register'), [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$club->id],
        ])->assertSessionHasErrors('club_ids.0');

        $this->assertDatabaseCount('club_registrations', 0);
    }

    public function test_double_submission_does_not_duplicate(): void
    {
        $club = $this->makeClub();

        $payload = [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'email' => 'paul@example.com',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$club->id],
        ];

        $this->post(route('clubs.register'), $payload)->assertRedirect();
        $this->post(route('clubs.register'), $payload)->assertRedirect();

        $this->assertDatabaseCount('club_registrations', 1);
    }

    public function test_can_register_to_multiple_clubs_at_once(): void
    {
        $clubA = $this->makeClub();
        $clubB = $this->makeClub();

        $this->post(route('clubs.register'), [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$clubA->id, $clubB->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('club_registrations', 2);
    }
}
