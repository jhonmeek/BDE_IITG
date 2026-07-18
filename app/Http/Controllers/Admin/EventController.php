<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/Events/Index', [
            'events' => Event::query()
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Event $event) => $this->eventData($event)),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['registration_enabled'] = $request->boolean('registration_enabled');
        $data['is_published'] = $request->boolean('is_published');
        $data['budget_allocated'] = $data['budget_allocated'] ?? 0;
        $data['participants_count'] = $data['participants_count'] ?? 0;
        $data['cover_image_path'] = $this->storeUpload($request->file('cover_image'), 'events');
        unset($data['cover_image']);

        Event::create($data);

        return to_route('admin.events.index')->with('success', 'Evenement ajoute avec succes.');
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Admin/Events/Edit', [
            'event' => $this->eventData($event->loadCount('registrations')),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['registration_enabled'] = $request->boolean('registration_enabled');
        $data['is_published'] = $request->boolean('is_published');
        $data['budget_allocated'] = $data['budget_allocated'] ?? 0;
        $data['participants_count'] = $data['participants_count'] ?? 0;
        $data['cover_image_path'] = $this->replaceUpload($request->file('cover_image'), $event->cover_image_path, 'events');
        unset($data['cover_image']);

        $event->update($data);

        return to_route('admin.events.index')->with('success', 'Evenement mis a jour.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteUpload($event->cover_image_path);
        $event->delete();

        return back()->with('success', 'Evenement supprime.');
    }

    private function eventData(Event $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'location' => $event->location,
            'excerpt' => $event->excerpt,
            'description' => $event->description,
            'starts_at' => $event->starts_at?->format('Y-m-d\TH:i'),
            'budget_allocated' => (float) $event->budget_allocated,
            'capacity' => $event->capacity,
            'participants_count' => $event->participants_count,
            'registration_enabled' => $event->registration_enabled,
            'is_published' => $event->is_published,
            'registrations_count' => $event->registrations_count ?? 0,
            'cover_image_url' => $event->cover_image_path ? Storage::url($event->cover_image_path) : null,
        ];
    }
}
