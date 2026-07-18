<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHistoricalEntryRequest;
use App\Http\Requests\UpdateHistoricalEntryRequest;
use App\Models\HistoricalEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HistoricalEntryController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/HistoricalEntries/Index', [
            'entries' => HistoricalEntry::query()
                ->orderBy('sort_order')
                ->latest('event_date')
                ->get()
                ->map(fn (HistoricalEntry $entry) => $this->entryData($entry)),
        ]);
    }

    public function store(StoreHistoricalEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['image_path'] = $this->storeUpload($request->file('image'), 'history');
        unset($data['image']);

        HistoricalEntry::create($data);

        return to_route('admin.historical-entries.index')->with('success', 'Entree historique ajoutee.');
    }

    public function edit(HistoricalEntry $historicalEntry): Response
    {
        return Inertia::render('Admin/HistoricalEntries/Edit', [
            'entry' => $this->entryData($historicalEntry),
        ]);
    }

    public function update(UpdateHistoricalEntryRequest $request, HistoricalEntry $historicalEntry): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['image_path'] = $this->replaceUpload($request->file('image'), $historicalEntry->image_path, 'history');
        unset($data['image']);

        $historicalEntry->update($data);

        return to_route('admin.historical-entries.index')->with('success', 'Entree historique mise a jour.');
    }

    public function destroy(HistoricalEntry $historicalEntry): RedirectResponse
    {
        $this->deleteUpload($historicalEntry->image_path);
        $historicalEntry->delete();

        return back()->with('success', 'Entree historique supprimee.');
    }

    private function entryData(HistoricalEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'period_label' => $entry->period_label,
            'event_date' => $entry->event_date?->toDateString(),
            'content' => $entry->content,
            'sort_order' => $entry->sort_order,
            'is_published' => $entry->is_published,
            'image_url' => $entry->image_path ? Storage::url($entry->image_path) : null,
        ];
    }
}
