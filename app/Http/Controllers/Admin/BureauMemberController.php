<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBureauMemberRequest;
use App\Http\Requests\UpdateBureauMemberRequest;
use App\Models\BureauMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BureauMemberController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/BureauMembers/Index', [
            'members' => BureauMember::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (BureauMember $member) => $this->memberData($member)),
        ]);
    }

    public function store(StoreBureauMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['photo_path'] = $this->storeUpload($request->file('photo'), 'bureau-members');
        unset($data['photo']);

        BureauMember::create($data);

        return to_route('admin.bureau-members.index')->with('success', 'Membre du bureau ajoute.');
    }

    public function edit(BureauMember $bureauMember): Response
    {
        return Inertia::render('Admin/BureauMembers/Edit', [
            'member' => $this->memberData($bureauMember),
        ]);
    }

    public function update(UpdateBureauMemberRequest $request, BureauMember $bureauMember): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['photo_path'] = $this->replaceUpload($request->file('photo'), $bureauMember->photo_path, 'bureau-members');
        unset($data['photo']);

        $bureauMember->update($data);

        return to_route('admin.bureau-members.index')->with('success', 'Membre du bureau mis a jour.');
    }

    public function destroy(BureauMember $bureauMember): RedirectResponse
    {
        $this->deleteUpload($bureauMember->photo_path);
        $bureauMember->delete();

        return back()->with('success', 'Membre du bureau supprime.');
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
            'sort_order' => $member->sort_order,
            'is_active' => $member->is_active,
            'photo_url' => $member->photo_path ? Storage::url($member->photo_path) : null,
        ];
    }
}
