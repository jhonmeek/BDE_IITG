<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClubRequest;
use App\Http\Requests\UpdateClubRequest;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/Clubs/Index', [
            'clubs' => Club::query()
                ->withCount('registrations')
                ->orderBy('name')
                ->get()
                ->map(fn (Club $club) => $this->clubData($club)),
            'categories' => ['educatif', 'sportif', 'culturel', 'communautaire'],
            'statuses' => ['active', 'archived'],
        ]);
    }

    public function store(StoreClubRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_published'] = $request->boolean('is_published');
        $data['budget_allocated'] = $data['budget_allocated'] ?? 0;
        $data['image_path'] = $this->storeUpload($request->file('image'), 'clubs');
        unset($data['image']);

        Club::create($data);

        return to_route('admin.clubs.index')->with('success', 'Club ajoute avec succes.');
    }

    public function edit(Club $club): Response
    {
        return Inertia::render('Admin/Clubs/Edit', [
            'club' => $this->clubData($club->loadCount('registrations')),
            'categories' => ['educatif', 'sportif', 'culturel', 'communautaire'],
            'statuses' => ['active', 'archived'],
        ]);
    }

    public function update(UpdateClubRequest $request, Club $club): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_published'] = $request->boolean('is_published');
        $data['budget_allocated'] = $data['budget_allocated'] ?? 0;
        $data['image_path'] = $this->replaceUpload($request->file('image'), $club->image_path, 'clubs');
        unset($data['image']);

        $club->update($data);

        return to_route('admin.clubs.index')->with('success', 'Club mis a jour.');
    }

    public function destroy(Club $club): RedirectResponse
    {
        $this->deleteUpload($club->image_path);
        $club->delete();

        return back()->with('success', 'Club supprime.');
    }

    private function clubData(Club $club): array
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'slug' => $club->slug,
            'category' => $club->category,
            'lead_name' => $club->lead_name,
            'summary' => $club->summary,
            'description' => $club->description,
            'budget_allocated' => (float) $club->budget_allocated,
            'status' => $club->status,
            'is_published' => $club->is_published,
            'registrations_count' => $club->registrations_count ?? 0,
            'image_url' => $club->image_path ? Storage::url($club->image_path) : null,
        ];
    }
}
