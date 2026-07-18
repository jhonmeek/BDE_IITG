<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClubRegistrationRequest;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\BureauMember;
use App\Models\Club;
use App\Models\ClubRegistration;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\HistoricalEntry;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicSiteController extends Controller
{
    public function home(): Response
    {
        $upcomingEvents = Event::published()
            ->where('starts_at', '>=', now())
            ->limit(4)
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = Event::query()
                ->where('is_published', true)
                ->latest('starts_at')
                ->limit(4)
                ->get();
        }

        return Inertia::render('Public/Home', [
            'settings' => $this->settings(),
            'members' => BureauMember::active()->limit(8)->get()->map(fn (BureauMember $member) => $this->memberData($member)),
            'clubs' => Club::published()->limit(6)->get()->map(fn (Club $club) => $this->clubData($club)),
            'events' => $upcomingEvents->map(fn (Event $event) => $this->eventData($event, true)),
            'history' => HistoricalEntry::published()->limit(3)->get()->map(fn (HistoricalEntry $entry) => $this->historyData($entry)),
            'media' => MediaAsset::published()->limit(6)->get()->map(fn (MediaAsset $media) => $this->mediaData($media)),
            'stats' => [
                'clubs' => Club::count(),
                'events' => Event::count(),
                'registrations' => ClubRegistration::count() + EventRegistration::count(),
                'documents' => Document::count(),
            ],
        ]);
    }

    public function bureau(): Response
    {
        return Inertia::render('Public/Bureau', [
            'settings' => $this->settings(),
            'members' => BureauMember::active()->get()->map(fn (BureauMember $member) => $this->memberData($member)),
        ]);
    }

    public function clubs(): Response
    {
        return Inertia::render('Public/Clubs/Index', [
            'settings' => $this->settings(),
            'clubs' => Club::published()
                ->withCount('registrations')
                ->get()
                ->map(fn (Club $club) => $this->clubData($club, true)),
        ]);
    }

    public function clubShow(Club $club): Response
    {
        abort_if(! $club->is_published, 404);

        return Inertia::render('Public/Clubs/Show', [
            'settings' => $this->settings(),
            'club' => $this->clubData($club->loadCount('registrations'), true),
            'otherClubs' => Club::published()
                ->whereKeyNot($club->id)
                ->limit(3)
                ->get()
                ->map(fn (Club $item) => $this->clubData($item)),
        ]);
    }

    public function events(): Response
    {
        return Inertia::render('Public/Events/Index', [
            'settings' => $this->settings(),
            'events' => Event::published()
                ->withCount('registrations')
                ->orderBy('starts_at')
                ->get()
                ->map(fn (Event $event) => $this->eventData($event, true)),
        ]);
    }

    public function eventShow(Event $event): Response
    {
        abort_if(! $event->is_published, 404);

        return Inertia::render('Public/Events/Show', [
            'settings' => $this->settings(),
            'event' => $this->eventData($event->loadCount('registrations'), true),
            'relatedEvents' => Event::published()
                ->whereKeyNot($event->id)
                ->limit(3)
                ->get()
                ->map(fn (Event $item) => $this->eventData($item)),
        ]);
    }

    public function history(): Response
    {
        return Inertia::render('Public/History', [
            'settings' => $this->settings(),
            'entries' => HistoricalEntry::published()->get()->map(fn (HistoricalEntry $entry) => $this->historyData($entry)),
        ]);
    }

    public function media(): Response
    {
        return Inertia::render('Public/Media', [
            'settings' => $this->settings(),
            'items' => MediaAsset::published()->get()->map(fn (MediaAsset $media) => $this->mediaData($media)),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Public/Contact', [
            'settings' => $this->settings(),
            'documents' => Document::public()->limit(6)->get()->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'category' => $document->category,
                'download_url' => Storage::url($document->file_path),
            ]),
        ]);
    }

    public function storeContact(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return back()->with('success', 'Votre message a bien ete envoye au bureau.');
    }

    public function registerClub(StoreClubRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['club_ids'] as $clubId) {
            ClubRegistration::create([
                'club_id' => $clubId,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'class_name' => $data['class_name'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Votre demande d inscription aux clubs a ete enregistree.');
    }

    public function registerEvent(StoreEventRegistrationRequest $request, Event $event): RedirectResponse
    {
        abort_if(! $event->registration_enabled || ! $event->is_published, 404);

        EventRegistration::create([
            ...$request->validated(),
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Votre inscription a l evenement a ete enregistree.');
    }

    private function settings(): array
    {
        $settings = SiteSetting::query()->pluck('value', 'key')->all();

        foreach ($settings as $key => $value) {
            if ($value && Str::endsWith($key, '_path')) {
                $settings[Str::replaceLast('_path', '_url', $key)] = Storage::url($value);
            }
        }

        return $settings;
    }

    private function memberData(BureauMember $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'role_title' => $member->role_title,
            'mandate_label' => $member->mandate_label,
            'email' => $member->email,
            'phone' => $member->phone,
            'bio' => $member->bio,
            'photo_url' => $member->photo_path ? Storage::url($member->photo_path) : null,
            'sort_order' => $member->sort_order,
        ];
    }

    private function clubData(Club $club, bool $full = false): array
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'slug' => $club->slug,
            'category' => $club->category,
            'lead_name' => $club->lead_name,
            'summary' => $club->summary,
            'description' => $full ? $club->description : null,
            'budget_allocated' => (float) $club->budget_allocated,
            'status' => $club->status,
            'image_url' => $club->image_path ? Storage::url($club->image_path) : null,
            'registrations_count' => $club->registrations_count ?? null,
        ];
    }

    private function eventData(Event $event, bool $full = false): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'location' => $event->location,
            'excerpt' => $event->excerpt,
            'description' => $full ? $event->description : null,
            'starts_at' => $event->starts_at?->toIso8601String(),
            'budget_allocated' => (float) $event->budget_allocated,
            'capacity' => $event->capacity,
            'participants_count' => $event->participants_count,
            'registrations_count' => $event->registrations_count ?? null,
            'registration_enabled' => $event->registration_enabled,
            'cover_image_url' => $event->cover_image_path ? Storage::url($event->cover_image_path) : null,
        ];
    }

    private function historyData(HistoricalEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'period_label' => $entry->period_label,
            'event_date' => $entry->event_date?->toDateString(),
            'content' => $entry->content,
            'image_url' => $entry->image_path ? Storage::url($entry->image_path) : null,
        ];
    }

    private function mediaData(MediaAsset $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title,
            'collection' => $media->collection,
            'media_type' => $media->media_type,
            'caption' => $media->caption,
            'url' => $media->external_url ?: ($media->file_path ? Storage::url($media->file_path) : null),
        ];
    }
}
