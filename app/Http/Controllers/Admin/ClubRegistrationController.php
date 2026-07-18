<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClubRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClubRegistrationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ClubRegistrations/Index', [
            'registrations' => ClubRegistration::query()
                ->with('club')
                ->latest()
                ->paginate(25)
                ->withQueryString()
                ->through(fn (ClubRegistration $registration) => [
                    'id' => $registration->id,
                    'club_name' => $registration->club?->name,
                    'last_name' => $registration->last_name,
                    'first_name' => $registration->first_name,
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

    public function update(Request $request, ClubRegistration $clubRegistration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,validated,rejected'],
        ]);

        $clubRegistration->update($data);

        return back()->with('success', 'Statut de l inscription club mis a jour.');
    }

    public function destroy(ClubRegistration $clubRegistration): RedirectResponse
    {
        $clubRegistration->delete();

        return back()->with('success', 'Inscription club supprimee.');
    }
}
