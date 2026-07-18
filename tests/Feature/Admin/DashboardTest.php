<?php

namespace Tests\Feature\Admin;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_finance_chart_over_six_months(): void
    {
        $this->actingAsSuperAdmin();

        Transaction::create([
            'type' => 'income',
            'category' => 'Sponsoring',
            'amount' => 500,
            'description' => 'Sponsor local',
            'transaction_date' => now()->startOfMonth()->toDateString(),
        ]);

        Transaction::create([
            'type' => 'expense',
            'category' => 'Evenements',
            'amount' => 200,
            'description' => 'Achat deco',
            'transaction_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('charts.finance.labels', 6)
                ->has('charts.finance.income', 6)
                ->has('charts.finance.expense', 6)
                ->where('stats.income', 500)
                ->where('stats.expense', 200)
                ->where('stats.balance', 300)
            );
    }
}
