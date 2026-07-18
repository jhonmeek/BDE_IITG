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

        $monthly = collect(range(5, 0))
            ->reverse()
            ->map(function (int $offset) {
                $date = now()->subMonths($offset);

                return [
                    'label' => $date->translatedFormat('M Y'),
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            });

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
                    'income' => $monthly->map(fn (array $month) => (float) Transaction::where('type', 'income')->whereBetween('transaction_date', [$month['start']->toDateString(), $month['end']->toDateString()])->sum('amount'))->values(),
                    'expense' => $monthly->map(fn (array $month) => (float) Transaction::where('type', 'expense')->whereBetween('transaction_date', [$month['start']->toDateString(), $month['end']->toDateString()])->sum('amount'))->values(),
                ],
                'events' => [
                    'labels' => Event::withCount('registrations')->orderBy('starts_at')->limit(6)->get()->pluck('name'),
                    'registrations' => Event::withCount('registrations')->orderBy('starts_at')->limit(6)->get()->pluck('registrations_count'),
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
