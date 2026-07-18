<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    private function makeDocument(bool $isPublic): Document
    {
        Storage::disk('local')->put('documents/fichier.pdf', 'contenu pdf');

        return Document::create([
            'title' => 'Reglement',
            'category' => 'reglement',
            'original_name' => 'reglement.pdf',
            'file_path' => 'documents/fichier.pdf',
            'is_public' => $isPublic,
        ]);
    }

    private function makeAttachment(): TransactionAttachment
    {
        Storage::disk('local')->put('transactions/recu.pdf', 'contenu recu');

        $transaction = Transaction::create([
            'type' => 'expense',
            'category' => 'Fournitures',
            'amount' => 50,
            'description' => 'Achat',
            'transaction_date' => '2026-07-01',
        ]);

        return TransactionAttachment::create([
            'transaction_id' => $transaction->id,
            'original_name' => 'recu.pdf',
            'file_path' => 'transactions/recu.pdf',
        ]);
    }

    public function test_guest_can_download_public_document(): void
    {
        $document = $this->makeDocument(true);

        $this->get(route('documents.download', $document))
            ->assertOk()
            ->assertDownload('reglement.pdf');
    }

    public function test_guest_cannot_download_private_document(): void
    {
        $document = $this->makeDocument(false);

        $this->get(route('documents.download', $document))->assertNotFound();
    }

    public function test_bde_member_can_download_private_document(): void
    {
        $document = $this->makeDocument(false);

        $this->actingAsBdeMember();

        $this->get(route('documents.download', $document))
            ->assertOk()
            ->assertDownload('reglement.pdf');
    }

    public function test_guest_cannot_download_transaction_attachment(): void
    {
        $attachment = $this->makeAttachment();

        $this->get(route('admin.transactions.attachments.download', $attachment))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_download_transaction_attachment(): void
    {
        $attachment = $this->makeAttachment();

        $this->actingAsSuperAdmin();

        $this->get(route('admin.transactions.attachments.download', $attachment))
            ->assertOk()
            ->assertDownload('recu.pdf');
    }
}
