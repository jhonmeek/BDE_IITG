<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaAssetRequest;
use App\Http\Requests\UpdateMediaAssetRequest;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaAssetController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/MediaAssets/Index', [
            'items' => MediaAsset::query()
                ->orderBy('sort_order')
                ->latest()
                ->get()
                ->map(fn (MediaAsset $item) => $this->mediaData($item)),
            'collections' => ['gallery', 'video-history', 'hero'],
        ]);
    }

    public function store(StoreMediaAssetRequest $request): RedirectResponse
    {
        if (! $request->file('file') && ! $request->filled('external_url')) {
            return back()->with('error', 'Ajoutez un fichier ou une URL externe.');
        }

        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['file_path'] = $this->storeUpload($request->file('file'), 'media-assets');
        unset($data['file']);

        MediaAsset::create($data);

        return to_route('admin.media-assets.index')->with('success', 'Media ajoute.');
    }

    public function edit(MediaAsset $mediaAsset): Response
    {
        return Inertia::render('Admin/MediaAssets/Edit', [
            'item' => $this->mediaData($mediaAsset),
            'collections' => ['gallery', 'video-history', 'hero'],
        ]);
    }

    public function update(UpdateMediaAssetRequest $request, MediaAsset $mediaAsset): RedirectResponse
    {
        if (! $request->file('file') && ! $request->filled('external_url') && ! $mediaAsset->file_path) {
            return back()->with('error', 'Ajoutez un fichier ou une URL externe.');
        }

        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['file_path'] = $this->replaceUpload($request->file('file'), $mediaAsset->file_path, 'media-assets');
        unset($data['file']);

        $mediaAsset->update($data);

        return to_route('admin.media-assets.index')->with('success', 'Media mis a jour.');
    }

    public function destroy(MediaAsset $mediaAsset): RedirectResponse
    {
        $this->deleteUpload($mediaAsset->file_path);
        $mediaAsset->delete();

        return back()->with('success', 'Media supprime.');
    }

    private function mediaData(MediaAsset $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title,
            'collection' => $media->collection,
            'media_type' => $media->media_type,
            'caption' => $media->caption,
            'external_url' => $media->external_url,
            'sort_order' => $media->sort_order,
            'is_published' => $media->is_published,
            'url' => $media->external_url ?: ($media->file_path ? Storage::url($media->file_path) : null),
        ];
    }
}
