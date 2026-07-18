<?php

namespace Tests\Feature;

use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_stats_only_count_published_content(): void
    {
        Club::create([
            'name' => 'Club Visible',
            'slug' => 'club-visible',
            'lead_name' => 'Alex',
            'description' => 'Club publie.',
            'is_published' => true,
        ]);

        Club::create([
            'name' => 'Club Cache',
            'slug' => 'club-cache',
            'lead_name' => 'Sam',
            'description' => 'Club en brouillon.',
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Home')
                ->where('stats.clubs', 1)
            );
    }
}
