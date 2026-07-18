<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    use HandlesUploads;

    public function index(): Response
    {
        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => Transaction::query()
                ->with(['attachments', 'author'])
                ->latest('transaction_date')
                ->get()
                ->map(fn (Transaction $transaction) => $this->transactionData($transaction)),
            'categories' => ['Evenements', 'Administratif', 'Fournitures', 'Sponsoring', 'Autre'],
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['attachment']);
        $data['created_by'] = $request->user()?->id;

        $transaction = Transaction::create($data);

        if ($request->file('attachment')) {
            $path = $this->storeUpload($request->file('attachment'), 'transactions', 'local');
            TransactionAttachment::create([
                'transaction_id' => $transaction->id,
                'original_name' => $request->file('attachment')->getClientOriginalName(),
                'file_path' => $path,
            ]);
        }

        return to_route('admin.transactions.index')->with('success', 'Transaction enregistree.');
    }

    public function edit(Transaction $transaction): Response
    {
        return Inertia::render('Admin/Transactions/Edit', [
            'transaction' => $this->transactionData($transaction->load(['attachments', 'author'])),
            'categories' => ['Evenements', 'Administratif', 'Fournitures', 'Sponsoring', 'Autre'],
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validated();
        unset($data['attachment']);
        $transaction->update($data);

        if ($request->file('attachment')) {
            foreach ($transaction->attachments as $attachment) {
                $this->deleteUpload($attachment->file_path, 'local');
                $attachment->delete();
            }

            $path = $this->storeUpload($request->file('attachment'), 'transactions', 'local');
            TransactionAttachment::create([
                'transaction_id' => $transaction->id,
                'original_name' => $request->file('attachment')->getClientOriginalName(),
                'file_path' => $path,
            ]);
        }

        return to_route('admin.transactions.index')->with('success', 'Transaction mise a jour.');
    }

    public function downloadAttachment(TransactionAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        foreach ($transaction->attachments as $attachment) {
            $this->deleteUpload($attachment->file_path, 'local');
        }

        $transaction->delete();

        return back()->with('success', 'Transaction supprimee.');
    }

    private function transactionData(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'category' => $transaction->category,
            'amount' => (float) $transaction->amount,
            'description' => $transaction->description,
            'transaction_date' => $transaction->transaction_date?->toDateString(),
            'notes' => $transaction->notes,
            'author' => $transaction->author?->name,
            'attachments' => $transaction->attachments->map(fn (TransactionAttachment $attachment) => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'download_url' => route('admin.transactions.attachments.download', $attachment),
            ])->values(),
        ];
    }
}
