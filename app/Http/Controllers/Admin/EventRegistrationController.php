<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventRegistrationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/EventRegistrations/Index', [
            'registrations' => EventRegistration::query()
                ->with('event')
                ->latest()
                ->get()
                ->map(fn (EventRegistration $registration) => [
                    'id' => $registration->id,
                    'event_name' => $registration->event?->name,
                    'full_name' => $registration->full_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'class_name' => $registration->class_name,
                    'status' => $registration->status,
                    'notes' => $registration->notes,
                    'created_at' => $registration->created_at?->toIso8601String(),
                ]),
            'statuses' => ['pending', 'validated', 'rejected'],
        ]);
    }

    public function update(Request $request, EventRegistration $eventRegistration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,validated,rejected'],
        ]);

        $eventRegistration->update($data);

        return back()->with('success', 'Statut de l inscription evenement mis a jour.');
    }

    public function destroy(EventRegistration $eventRegistration): RedirectResponse
    {
        $eventRegistration->delete();

        return back()->with('success', 'Inscription evenement supprimee.');
    }
}
