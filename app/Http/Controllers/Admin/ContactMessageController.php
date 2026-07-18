<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ContactMessages/Index', [
            'messages' => ContactMessage::query()
                ->latest()
                ->get()
                ->map(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'status' => $message->status,
                    'responded_at' => $message->responded_at?->toIso8601String(),
                    'created_at' => $message->created_at?->toIso8601String(),
                ]),
            'statuses' => ['new', 'in_progress', 'resolved'],
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved'],
        ]);

        $data['responded_at'] = $data['status'] === 'resolved' ? Carbon::now() : null;
        $contactMessage->update($data);

        return back()->with('success', 'Statut du message mis a jour.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Message supprime.');
    }
}
