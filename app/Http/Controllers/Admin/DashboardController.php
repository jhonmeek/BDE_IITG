<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BureauMember;
use App\Models\Club;
use App\Models\ClubRegistration;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $income = (float) Transaction::where('type', 'income')->sum('amount');
        $expense = (float) Transaction::where('type', 'expense')->sum('amount');

        $since = now()->subMonths(5)->startOfMonth();

        $recentTransactions = Transaction::query()
            ->where('transaction_date', '>=', $since->toDateString())
            ->get(['type', 'amount', 'transaction_date']);

        $monthly = collect(range(5, 0))->map(function (int $offset) use ($recentTransactions) {
            $date = now()->subMonths($offset);
            $ofMonth = $recentTransactions->filter(
                fn (Transaction $transaction) => $transaction->transaction_date->isSameMonth($date)
            );

            return [
                'label' => $date->translatedFormat('M Y'),
                'income' => (float) $ofMonth->where('type', 'income')->sum('amount'),
                'expense' => (float) $ofMonth->where('type', 'expense')->sum('amount'),
            ];
        });

        $eventsWithCounts = Event::withCount('registrations')->orderBy('starts_at')->limit(6)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'members' => BureauMember::where('is_active', true)->count(),
                'clubs' => Club::count(),
                'pendingClubRegistrations' => ClubRegistration::where('status', 'pending')->count(),
                'upcomingEvents' => Event::where('starts_at', '>=', now())->count(),
                'pendingEventRegistrations' => EventRegistration::where('status', 'pending')->count(),
                'documents' => Document::count(),
                'messages' => ContactMessage::where('status', 'new')->count(),
                'balance' => $income - $expense,
                'income' => $income,
                'expense' => $expense,
            ],
            'charts' => [
                'finance' => [
                    'labels' => $monthly->pluck('label')->values(),
                    'income' => $monthly->pluck('income')->values(),
                    'expense' => $monthly->pluck('expense')->values(),
                ],
                'events' => [
                    'labels' => $eventsWithCounts->pluck('name'),
                    'registrations' => $eventsWithCounts->pluck('registrations_count'),
                ],
            ],
            'recent' => [
                'transactions' => Transaction::latest('transaction_date')->limit(5)->get()->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'category' => $transaction->category,
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date?->toDateString(),
                ]),
                'events' => Event::orderBy('starts_at')->limit(5)->get()->map(fn (Event $event) => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'starts_at' => $event->starts_at?->toIso8601String(),
                ]),
                'messages' => ContactMessage::latest()->limit(5)->get()->map(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'status' => $message->status,
                    'created_at' => Carbon::parse($message->created_at)->toIso8601String(),
                ]),
            ],
        ]);
    }
}
