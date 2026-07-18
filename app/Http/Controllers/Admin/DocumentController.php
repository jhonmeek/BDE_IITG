<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/Documents/Index', [
            'documents' => Document::query()
                ->with('author')
                ->latest()
                ->get()
                ->map(fn (Document $document) => $this->documentData($document)),
            'categories' => ['general', 'proces-verbal', 'reglement', 'rapport', 'partenariat'],
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $path = $this->storeUpload($request->file('file'), 'documents');

        Document::create([
            'title' => $request->string('title'),
            'category' => $request->string('category'),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'is_public' => $request->boolean('is_public'),
            'uploaded_by' => $request->user()?->id,
        ]);

        return to_route('admin.documents.index')->with('success', 'Document ajoute.');
    }

    public function edit(Document $document): Response
    {
        return Inertia::render('Admin/Documents/Edit', [
            'document' => $this->documentData($document->load('author')),
            'categories' => ['general', 'proces-verbal', 'reglement', 'rapport', 'partenariat'],
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $request->validated();
        $file = $request->file('file');

        if ($file) {
            $data['file_path'] = $this->replaceUpload($file, $document->file_path, 'documents');
            $data['original_name'] = $file->getClientOriginalName();
        }

        $data['is_public'] = $request->boolean('is_public');

        $document->update($data);

        return to_route('admin.documents.index')->with('success', 'Document mis a jour.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->deleteUpload($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprime.');
    }

    private function documentData(Document $document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'category' => $document->category,
            'original_name' => $document->original_name,
            'is_public' => $document->is_public,
            'download_url' => Storage::url($document->file_path),
            'author' => $document->author?->name,
            'created_at' => $document->created_at?->toIso8601String(),
        ];
    }
}
