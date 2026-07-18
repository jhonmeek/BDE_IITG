<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_document_upload_rejects_php_file(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('admin.documents.store'), [
            'title' => 'Document pirate',
            'category' => 'general',
            'file' => UploadedFile::fake()->create('shell.php', 10, 'application/x-httpd-php'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_document_upload_accepts_pdf(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('admin.documents.store'), [
            'title' => 'Reglement interieur',
            'category' => 'reglement',
            'file' => UploadedFile::fake()->create('reglement.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.documents.index'));
        $this->assertDatabaseCount('documents', 1);
    }

    public function test_transaction_attachment_rejects_executable(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('admin.transactions.store'), [
            'type' => 'expense',
            'category' => 'Fournitures',
            'amount' => 100,
            'description' => 'Achat test',
            'transaction_date' => '2026-07-01',
            'attachment' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_media_asset_rejects_svg_file(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('admin.media-assets.store'), [
            'title' => 'Logo malveillant',
            'collection' => 'gallery',
            'media_type' => 'image',
            'file' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media_assets', 0);
    }
}
