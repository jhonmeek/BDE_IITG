# Plan d'implémentation — Correctifs audit BDE_IITG (sécurité, métier, perf)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Amener l'application BDE_IITG à une version « correcte » : failles de sécurité corrigées (uploads, fichiers privés, dépendances, comptes par défaut), règles métier appliquées (capacité, doublons, anti-spam, clubs publiés), autorisation trésorerie, gestion des comptes, et corrections de cohérence/performance — le tout couvert par des tests Feature.

**Architecture:** Laravel 12 + Inertia/Vue 3 (Breeze), spatie/laravel-permission (rôles `super_admin` / `membre_bde`), PostgreSQL en prod, SQLite `:memory:` pour les tests (`phpunit.xml` déjà configuré). Les corrections se font in-place, tâche par tâche, chaque tâche livrant un état vert (`php artisan test`) et un commit.

**Tech Stack:** PHP 8.2+, Laravel 12, Inertia v2, Vue 3, Tailwind, spatie/laravel-permission v6, PHPUnit 11.

**Répertoire de travail pour TOUTES les commandes :** `E:/COMPUTER-SCIENCE/BDE/BDE_IITG`

**Conventions :**
- Tests : `php artisan test` (SQLite en mémoire, aucune base à préparer).
- Un commit par tâche minimum, messages en `type: description`.
- Le code applicatif existant écrit ses chaînes utilisateur sans accents (`"Transaction enregistree."`) — conserver ce style dans les nouveaux messages.

---

## Tâche 1 : Initialiser git et établir la ligne de base

Le dossier n'est **pas** un dépôt git. Sans historique, aucun correctif n'est traçable ni réversible.

**Files:**
- Aucun fichier modifié (init + commit).

- [ ] **Step 1 : Initialiser le dépôt**

```bash
cd "E:/COMPUTER-SCIENCE/BDE/BDE_IITG"
git init -b main
git add -A
git commit -m "chore: etat initial avant correctifs audit"
```

Attendu : commit initial créé. Le `.gitignore` Laravel existant exclut déjà `vendor/`, `node_modules/`, `.env`.

- [ ] **Step 2 : Lancer la suite de tests existante (ligne de base)**

```bash
php artisan test
```

Attendu : les tests `ProfileTest`, `AuthenticationTest`, `PasswordResetTest`, `PasswordUpdateTest`, `PasswordConfirmationTest` passent. **`RegistrationTest` échoue** (la route `/register` a été retirée mais le test Breeze est resté) et `EmailVerificationTest` peut être instable. C'est attendu : ces tests correspondent au code mort supprimé en Tâche 2. Noter le nombre exact de tests qui passent/échouent.

---

## Tâche 2 : Supprimer le code mort (register + vérification email) → suite verte

`RegisteredUserController`, les 3 contrôleurs de vérification d'email et leurs pages Vue ne sont plus routés (ou pointent vers un `User` qui n'implémente pas `MustVerifyEmail`). Les tests Breeze associés font échouer la suite.

**Files:**
- Delete: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Delete: `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
- Delete: `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
- Delete: `app/Http/Controllers/Auth/VerifyEmailController.php`
- Delete: `resources/js/Pages/Auth/Register.vue`
- Delete: `resources/js/Pages/Auth/VerifyEmail.vue`
- Delete: `resources/js/Pages/Welcome.vue` (scaffold Breeze non rendu, référence `route('register')`)
- Delete: `resources/js/Pages/Dashboard.vue` (non rendu : `/dashboard` redirige vers `admin.dashboard`)
- Delete: `tests/Feature/Auth/RegistrationTest.php`
- Delete: `tests/Feature/Auth/EmailVerificationTest.php`
- Modify: `routes/auth.php`

- [ ] **Step 1 : Supprimer les fichiers morts**

```bash
rm app/Http/Controllers/Auth/RegisteredUserController.php \
   app/Http/Controllers/Auth/EmailVerificationNotificationController.php \
   app/Http/Controllers/Auth/EmailVerificationPromptController.php \
   app/Http/Controllers/Auth/VerifyEmailController.php \
   resources/js/Pages/Auth/Register.vue \
   resources/js/Pages/Auth/VerifyEmail.vue \
   resources/js/Pages/Welcome.vue \
   resources/js/Pages/Dashboard.vue \
   tests/Feature/Auth/RegistrationTest.php \
   tests/Feature/Auth/EmailVerificationTest.php
```

- [ ] **Step 2 : Retirer les routes de vérification d'email de `routes/auth.php`**

Supprimer les imports devenus inutiles et les 3 routes de vérification. Le fichier complet après modification :

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

- [ ] **Step 3 : Vérifier qu'aucune référence ne subsiste**

```bash
grep -rn "RegisteredUserController\|VerifyEmailController\|EmailVerificationPrompt\|EmailVerificationNotification\|route('register')\|verification\." app/ routes/ resources/js/ tests/ --include="*.php" --include="*.vue"
```

Attendu : **aucun résultat** (ou uniquement des occurrences sans lien, ex. `verification.` dans `node_modules` exclu par les includes).

- [ ] **Step 4 : Lancer les tests**

```bash
php artisan test
```

Attendu : **tous les tests passent** (suite verte, ~20 tests).

- [ ] **Step 5 : Commit**

```bash
git add -A
git commit -m "chore: supprime le code mort inscription et verification email"
```

---

## Tâche 3 : Déplacer `spatie/laravel-permission` en dépendance de production

Le package est en `require-dev` alors que le middleware `role:` en dépend au runtime : un `composer install --no-dev` en production casse toute l'application.

**Files:**
- Modify: `composer.json` (via composer), `composer.lock`

- [ ] **Step 1 : Déplacer le package**

```bash
composer remove --dev spatie/laravel-permission
composer require "spatie/laravel-permission:^6.0"
```

Attendu : les deux commandes se terminent sans erreur (réseau requis).

- [ ] **Step 2 : Vérifier le déplacement**

```bash
php -r "\$j = json_decode(file_get_contents('composer.json'), true); echo isset(\$j['require']['spatie/laravel-permission']) ? 'OK require' : 'ERREUR'; echo PHP_EOL; echo isset(\$j['require-dev']['spatie/laravel-permission']) ? 'ERREUR encore en dev' : 'OK plus en dev'; echo PHP_EOL;"
```

Attendu : `OK require` puis `OK plus en dev`.

- [ ] **Step 3 : Lancer les tests**

```bash
php artisan test
```

Attendu : suite verte.

- [ ] **Step 4 : Commit**

```bash
git add composer.json composer.lock
git commit -m "fix: spatie/laravel-permission en dependance de production"
```

---

## Tâche 4 : Seeder partagé rôles/permissions + helpers de test + comptes démo limités au local

Trois objectifs : (1) extraire rôles/permissions dans un seeder réutilisable par les tests, (2) donner aux tests des helpers `actingAsSuperAdmin()` / `actingAsBdeMember()`, (3) empêcher la création des comptes `admin@bde-iitg.test / password` hors environnement local.

**Files:**
- Create: `database/seeders/RolePermissionSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `tests/TestCase.php`

- [ ] **Step 1 : Créer `database/seeders/RolePermissionSeeder.php`**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage dashboard',
        'manage bureau members',
        'manage clubs',
        'manage events',
        'manage registrations',
        'manage transactions',
        'manage documents',
        'manage historical entries',
        'manage media assets',
        'manage contact messages',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $member = Role::findOrCreate('membre_bde', 'web');

        $superAdmin->syncPermissions(self::PERMISSIONS);
        $member->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
```

Note : le rôle `public` (jamais utilisé dans le code) n'est volontairement plus créé. La restriction des permissions de `membre_bde` viendra en Tâche 12.

- [ ] **Step 2 : Réécrire `database/seeders/DatabaseSeeder.php`**

Les comptes démo et le contenu démo ne sont créés qu'en environnement `local` :

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        if (! app()->environment('local')) {
            return;
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@bde-iitg.test'],
            [
                'name' => 'Administrateur BDE',
                'username' => 'admin',
                'phone' => '+24100000000',
                'title' => 'Super administrateur',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $admin->syncRoles(['super_admin']);

        $member = User::updateOrCreate(
            ['email' => 'membre@bde-iitg.test'],
            [
                'name' => 'Membre BDE',
                'username' => 'membre',
                'phone' => '+24111111111',
                'title' => 'Membre bureau',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $member->syncRoles(['membre_bde']);

        $this->call(DemoContentSeeder::class);
    }
}
```

- [ ] **Step 3 : Ajouter les helpers dans `tests/TestCase.php`**

Fichier complet après modification :

```php
<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsSuperAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['super_admin']);

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsBdeMember(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles(['membre_bde']);

        $this->actingAs($user);

        return $user;
    }
}
```

- [ ] **Step 4 : Vérifier que le seed fonctionne toujours et que la suite est verte**

```bash
php artisan test
```

Attendu : suite verte (les tests existants n'utilisent pas encore les helpers, on vérifie l'absence de régression).

- [ ] **Step 5 : Commit**

```bash
git add database/seeders/ tests/TestCase.php
git commit -m "refactor: seeder roles/permissions partage, comptes demo limites au local, helpers de test"
```

---

## Tâche 5 : Restreindre les types de fichiers uploadés (TDD)

Aujourd'hui `file|max:` accepte n'importe quelle extension (`.php`, `.svg`, `.html`) stockée sur le disque public → XSS/RCE. Les requêtes `Update*` déléguant à `(new Store*Request())->rules()`, corriger les 3 `Store*` couvre aussi les updates. Les images (`photo`, `image`, `cover_image`) utilisent déjà la règle `image` : rien à changer pour elles.

**Files:**
- Test: `tests/Feature/Admin/UploadValidationTest.php` (create)
- Modify: `app/Http/Requests/StoreDocumentRequest.php:28`
- Modify: `app/Http/Requests/StoreTransactionRequest.php:32`
- Modify: `app/Http/Requests/StoreMediaAssetRequest.php:33`

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/Admin/UploadValidationTest.php`**

```php
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
```

- [ ] **Step 2 : Vérifier que les tests échouent**

```bash
php artisan test --filter=UploadValidationTest
```

Attendu : `test_document_upload_rejects_php_file`, `test_transaction_attachment_rejects_executable` et `test_media_asset_rejects_svg_file` **FAIL** (aucune erreur de validation levée) ; `test_document_upload_accepts_pdf` PASS.

- [ ] **Step 3 : Corriger les règles de validation**

Dans `app/Http/Requests/StoreDocumentRequest.php`, remplacer :

```php
'file' => ['required', 'file', 'max:10240'],
```

par :

```php
'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg', 'max:10240'],
```

Dans `app/Http/Requests/StoreTransactionRequest.php`, remplacer :

```php
'attachment' => ['nullable', 'file', 'max:5120'],
```

par :

```php
'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
```

Dans `app/Http/Requests/StoreMediaAssetRequest.php`, remplacer :

```php
'file' => ['nullable', 'file', 'max:51200'],
```

par :

```php
'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm', 'max:51200'],
```

- [ ] **Step 4 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte, dont les 4 tests `UploadValidationTest`.

- [ ] **Step 5 : Commit**

```bash
git add app/Http/Requests/ tests/Feature/Admin/UploadValidationTest.php
git commit -m "fix(securite): liste blanche des types de fichiers uploades"
```

---

## Tâche 6 : Stockage privé des documents et justificatifs + routes de téléchargement contrôlées (TDD)

Les documents (`is_public = false` inclus) et les justificatifs comptables sont sur le disque `public` : accessibles sans authentification. On les déplace sur le disque `local` (racine `storage/app/private`, jamais servi par le serveur web) et on les sert via des routes qui vérifient les droits. Les images du site vitrine (bureau, clubs, événements, médias, historique) restent sur le disque public — c'est leur rôle.

**Files:**
- Test: `tests/Feature/PrivateFilesTest.php` (create)
- Create: `app/Http/Controllers/DocumentDownloadController.php`
- Modify: `app/Http/Controllers/Concerns/HandlesUploads.php`
- Modify: `app/Http/Controllers/Admin/DocumentController.php`
- Modify: `app/Http/Controllers/Admin/TransactionController.php`
- Modify: `app/Http/Controllers/PublicSiteController.php` (méthode `contact()`)
- Modify: `routes/web.php`

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/PrivateFilesTest.php`**

```php
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
        Storage::disk('local')->put('transactions/recu.pdf', 'contenu recu');

        $transaction = Transaction::create([
            'type' => 'expense',
            'category' => 'Fournitures',
            'amount' => 50,
            'description' => 'Achat',
            'transaction_date' => '2026-07-01',
        ]);

        $attachment = TransactionAttachment::create([
            'transaction_id' => $transaction->id,
            'original_name' => 'recu.pdf',
            'file_path' => 'transactions/recu.pdf',
        ]);

        $this->get(route('admin.transactions.attachments.download', $attachment))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_download_transaction_attachment(): void
    {
        Storage::disk('local')->put('transactions/recu.pdf', 'contenu recu');

        $transaction = Transaction::create([
            'type' => 'expense',
            'category' => 'Fournitures',
            'amount' => 50,
            'description' => 'Achat',
            'transaction_date' => '2026-07-01',
        ]);

        $attachment = TransactionAttachment::create([
            'transaction_id' => $transaction->id,
            'original_name' => 'recu.pdf',
            'file_path' => 'transactions/recu.pdf',
        ]);

        $this->actingAsSuperAdmin();

        $this->get(route('admin.transactions.attachments.download', $attachment))
            ->assertOk()
            ->assertDownload('recu.pdf');
    }
}
```

- [ ] **Step 2 : Vérifier que les tests échouent**

```bash
php artisan test --filter=PrivateFilesTest
```

Attendu : FAIL avec `Route [documents.download] not defined.`

- [ ] **Step 3 : Paramétrer le disque dans `app/Http/Controllers/Concerns/HandlesUploads.php`**

Fichier complet après modification :

```php
<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    protected function storeUpload(?UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, $disk);
    }

    protected function replaceUpload(?UploadedFile $file, ?string $currentPath, string $directory, string $disk = 'public'): ?string
    {
        if (! $file) {
            return $currentPath;
        }

        $this->deleteUpload($currentPath, $disk);

        return $this->storeUpload($file, $directory, $disk);
    }

    protected function deleteUpload(?string $path, string $disk = 'public'): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
```

- [ ] **Step 4 : Créer `app/Http/Controllers/DocumentDownloadController.php`**

Un document privé renvoie 404 (et non 403) pour ne pas révéler son existence :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document): StreamedResponse
    {
        if (! $document->is_public) {
            abort_unless(
                $request->user()?->hasAnyRole(['super_admin', 'membre_bde']),
                404,
            );
        }

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }
}
```

- [ ] **Step 5 : Basculer `DocumentController` sur le disque `local`**

Dans `app/Http/Controllers/Admin/DocumentController.php` :

Dans `store()`, remplacer :
```php
$path = $this->storeUpload($request->file('file'), 'documents');
```
par :
```php
$path = $this->storeUpload($request->file('file'), 'documents', 'local');
```

Dans `update()`, remplacer :
```php
$data['file_path'] = $this->replaceUpload($file, $document->file_path, 'documents');
```
par :
```php
$data['file_path'] = $this->replaceUpload($file, $document->file_path, 'documents', 'local');
```

Dans `destroy()`, remplacer :
```php
$this->deleteUpload($document->file_path);
```
par :
```php
$this->deleteUpload($document->file_path, 'local');
```

Dans `documentData()`, remplacer :
```php
'download_url' => Storage::url($document->file_path),
```
par :
```php
'download_url' => route('documents.download', $document),
```

L'import `use Illuminate\Support\Facades\Storage;` devient inutilisé dans ce fichier : le supprimer.

- [ ] **Step 6 : Basculer `TransactionController` sur le disque `local` et ajouter le téléchargement**

Dans `app/Http/Controllers/Admin/TransactionController.php` :

Dans `store()` et `update()`, remplacer chaque :
```php
$path = $this->storeUpload($request->file('attachment'), 'transactions');
```
par :
```php
$path = $this->storeUpload($request->file('attachment'), 'transactions', 'local');
```

Dans `update()` et `destroy()`, remplacer chaque :
```php
$this->deleteUpload($attachment->file_path);
```
par :
```php
$this->deleteUpload($attachment->file_path, 'local');
```

Dans `transactionData()`, remplacer :
```php
'download_url' => Storage::url($attachment->file_path),
```
par :
```php
'download_url' => route('admin.transactions.attachments.download', $attachment),
```

Ajouter la méthode de téléchargement (avant `transactionData()`) et les imports :

```php
use Symfony\Component\HttpFoundation\StreamedResponse;
```

```php
    public function downloadAttachment(TransactionAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }
```

(L'import `Storage` reste nécessaire ici.)

- [ ] **Step 7 : Mettre à jour `PublicSiteController::contact()`**

Remplacer :
```php
'download_url' => Storage::url($document->file_path),
```
par :
```php
'download_url' => route('documents.download', $document),
```

(Ne pas retirer l'import `Storage` : `settings()`, `memberData()`, etc. l'utilisent toujours.)

- [ ] **Step 8 : Ajouter les routes dans `routes/web.php`**

Après la ligne `Route::get('/contact', ...)` (zone publique), ajouter :

```php
Route::get('/documents/{document}/download', \App\Http\Controllers\DocumentDownloadController::class)->name('documents.download');
```

Dans le groupe `admin`, juste avant `Route::resource('transactions', ...)`, ajouter :

```php
Route::get('transactions/attachments/{attachment}/download', [TransactionController::class, 'downloadAttachment'])->name('transactions.attachments.download');
```

- [ ] **Step 9 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte, dont les 5 tests `PrivateFilesTest`. Les tests `UploadValidationTest` passent toujours (les deux disques sont fakés).

- [ ] **Step 10 : Migrer les fichiers existants (environnement de dev)**

```bash
mkdir -p storage/app/private/documents storage/app/private/transactions
if [ -d storage/app/public/documents ]; then mv storage/app/public/documents/* storage/app/private/documents/ 2>/dev/null; rmdir storage/app/public/documents 2>/dev/null; fi
if [ -d storage/app/public/transactions ]; then mv storage/app/public/transactions/* storage/app/private/transactions/ 2>/dev/null; rmdir storage/app/public/transactions 2>/dev/null; fi
true
```

Attendu : les fichiers déjà uploadés (s'il y en a) sont déplacés ; les chemins en base (`documents/x.ext`) restent valides car seule la racine du disque change. **Répéter cette étape sur chaque environnement déployé.**

- [ ] **Step 11 : Commit**

```bash
git add -A
git commit -m "fix(securite): documents et justificatifs sur disque prive avec telechargement controle"
```

---

## Tâche 7 : Commande `bde:create-admin` pour créer l'admin en production (TDD)

Sans les comptes seedés (désormais limités au local), il faut un moyen propre de créer le premier super admin en production.

**Files:**
- Test: `tests/Feature/CreateAdminCommandTest.php` (create)
- Create: `app/Console/Commands/CreateAdminUser.php`

- [ ] **Step 1 : Écrire le test qui échoue — `tests/Feature/CreateAdminCommandTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_a_super_admin(): void
    {
        $this->artisan('bde:create-admin', [
            'email' => 'presi@bde-iitg.org',
            '--name' => 'Presidente BDE',
            '--password' => 'MotDePasseSolide2026',
        ])->assertExitCode(0);

        $user = User::where('email', 'presi@bde-iitg.org')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('MotDePasseSolide2026', $user->password));
    }

    public function test_command_rejects_weak_password(): void
    {
        $this->artisan('bde:create-admin', [
            'email' => 'presi@bde-iitg.org',
            '--password' => 'abc',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }
}
```

- [ ] **Step 2 : Vérifier que le test échoue**

```bash
php artisan test --filter=CreateAdminCommandTest
```

Attendu : FAIL — `Command "bde:create-admin" is not defined`.

- [ ] **Step 3 : Créer `app/Console/Commands/CreateAdminUser.php`**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\password;

class CreateAdminUser extends Command
{
    protected $signature = 'bde:create-admin
        {email : Adresse email du compte}
        {--name=Administrateur BDE : Nom affiche}
        {--password= : Mot de passe (demande interactivement si absent)}';

    protected $description = 'Cree ou met a jour un compte super administrateur BDE';

    public function handle(): int
    {
        $plainPassword = $this->option('password') ?: password('Mot de passe du super admin');

        $validator = Validator::make(
            ['email' => $this->argument('email'), 'password' => $plainPassword],
            [
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(12)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Role::findOrCreate('super_admin', 'web');

        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'name' => $this->option('name'),
                'password' => $plainPassword,
                'is_active' => true,
            ],
        );

        $user->syncRoles(['super_admin']);

        $this->info("Compte super admin pret : {$user->email}");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4 : Vérifier que les tests passent**

```bash
php artisan test --filter=CreateAdminCommandTest
php artisan test
```

Attendu : les 2 tests passent, suite complète verte.

- [ ] **Step 5 : Commit**

```bash
git add app/Console/Commands/CreateAdminUser.php tests/Feature/CreateAdminCommandTest.php
git commit -m "feat: commande bde:create-admin pour creer le super admin hors seeder"
```

---

## Tâche 8 : Rate limiting des formulaires publics (TDD)

Contact et inscriptions sont ouverts sans aucune limite : spam trivial.

**Files:**
- Test: `tests/Feature/PublicFormsThrottleTest.php` (create)
- Modify: `routes/web.php:22,25,29`

- [ ] **Step 1 : Écrire le test qui échoue — `tests/Feature/PublicFormsThrottleTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFormsThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Etudiant Test',
            'email' => 'etudiant@example.com',
            'message' => 'Bonjour, ceci est un message de test valide.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload)->assertRedirect();
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
    }

    public function test_event_registration_is_rate_limited(): void
    {
        $event = Event::create([
            'name' => 'Gala annuel',
            'slug' => 'gala-annuel',
            'location' => 'Campus',
            'description' => 'Le grand gala du BDE.',
            'starts_at' => now()->addMonth(),
            'registration_enabled' => true,
            'is_published' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('events.register', $event), [
                'full_name' => "Participant {$i}",
                'email' => "participant{$i}@example.com",
            ])->assertRedirect();
        }

        $this->post(route('events.register', $event), [
            'full_name' => 'Participant 6',
            'email' => 'participant6@example.com',
        ])->assertStatus(429);
    }

    public function test_club_registration_is_rate_limited(): void
    {
        $club = Club::create([
            'name' => 'Club Echecs',
            'slug' => 'club-echecs',
            'lead_name' => 'Alex',
            'description' => 'Club d echecs du campus.',
            'is_published' => true,
        ]);

        $payload = [
            'last_name' => 'Test',
            'first_name' => 'Etudiant',
            'class_name' => 'L3 Info',
            'club_ids' => [$club->id],
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('clubs.register'), $payload)->assertRedirect();
        }

        $this->post(route('clubs.register'), $payload)->assertStatus(429);
    }
}
```

Note : ce test crée des inscriptions identiques répétées — c'est voulu ici ; la déduplication arrive en Tâches 9-10 et ce test devra alors continuer de passer (le throttle se déclenche avant/indépendamment de la déduplication, qui répond aussi par un redirect).

- [ ] **Step 2 : Vérifier que le test échoue**

```bash
php artisan test --filter=PublicFormsThrottleTest
```

Attendu : FAIL — la 6e requête renvoie 302 au lieu de 429.

- [ ] **Step 3 : Ajouter le throttle sur les 3 routes POST publiques dans `routes/web.php`**

Remplacer :

```php
Route::post('/clubs/register', [PublicSiteController::class, 'registerClub'])->name('clubs.register');
```
par :
```php
Route::post('/clubs/register', [PublicSiteController::class, 'registerClub'])->middleware('throttle:5,1')->name('clubs.register');
```

Remplacer :
```php
Route::post('/events/{event:slug}/register', [PublicSiteController::class, 'registerEvent'])->name('events.register');
```
par :
```php
Route::post('/events/{event:slug}/register', [PublicSiteController::class, 'registerEvent'])->middleware('throttle:5,1')->name('events.register');
```

Remplacer :
```php
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->name('contact.store');
```
par :
```php
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');
```

- [ ] **Step 4 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte.

- [ ] **Step 5 : Commit**

```bash
git add routes/web.php tests/Feature/PublicFormsThrottleTest.php
git commit -m "fix(securite): rate limiting sur contact et inscriptions publiques"
```

---

## Tâche 9 : Règles d'inscription aux événements — doublons et capacité (TDD)

Un même email peut s'inscrire indéfiniment et la capacité (`events.capacity`) n'est jamais appliquée.

**Files:**
- Test: `tests/Feature/EventRegistrationRulesTest.php` (create)
- Create: `database/migrations/xxxx_add_unique_index_to_event_registrations.php` (via artisan)
- Modify: `app/Http/Requests/StoreEventRegistrationRequest.php`

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/EventRegistrationRulesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        return Event::create([
            'name' => 'Tournoi de foot',
            'slug' => 'tournoi-de-foot',
            'location' => 'Stade du campus',
            'description' => 'Tournoi inter-classes.',
            'starts_at' => now()->addWeeks(2),
            'registration_enabled' => true,
            'is_published' => true,
            ...$overrides,
        ]);
    }

    public function test_student_can_register_once(): void
    {
        $event = $this->makeEvent();

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie Ndong',
            'email' => 'marie@example.com',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_same_email_cannot_register_twice_for_same_event(): void
    {
        $event = $this->makeEvent();

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie Ndong',
            'email' => 'marie@example.com',
        ]);

        $this->post(route('events.register', $event), [
            'full_name' => 'Marie N.',
            'email' => 'MARIE@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_registration_is_refused_when_event_is_full(): void
    {
        $event = $this->makeEvent(['capacity' => 1]);

        EventRegistration::create([
            'event_id' => $event->id,
            'full_name' => 'Premier Inscrit',
            'email' => 'premier@example.com',
            'status' => 'pending',
        ]);

        $this->post(route('events.register', $event), [
            'full_name' => 'Deuxieme Inscrit',
            'email' => 'deuxieme@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('event_registrations', 1);
    }
}
```

- [ ] **Step 2 : Vérifier que les tests échouent**

```bash
php artisan test --filter=EventRegistrationRulesTest
```

Attendu : `test_student_can_register_once` PASS ; les deux autres **FAIL** (2 lignes créées / inscription acceptée malgré la capacité).

- [ ] **Step 3 : Créer la migration d'unicité (avec déduplication préalable)**

```bash
php artisan make:migration add_unique_index_to_event_registrations --table=event_registrations
```

Remplacer le contenu du fichier généré par :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        foreach (DB::table('event_registrations')->orderBy('id')->get(['id', 'event_id', 'email']) as $row) {
            $key = $row->event_id.'|'.mb_strtolower($row->email);

            if (isset($seen[$key])) {
                DB::table('event_registrations')->where('id', $row->id)->delete();
            } else {
                $seen[$key] = true;
            }
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->unique(['event_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'email']);
        });
    }
};
```

- [ ] **Step 4 : Renforcer `app/Http/Requests/StoreEventRegistrationRequest.php`**

Fichier complet après modification (normalisation de l'email en minuscules, unicité par événement, contrôle de capacité) :

```php
<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Event|null $event */
        $event = $this->route('event');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('event_registrations', 'email')->where('event_id', $event?->id),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Event|null $event */
                $event = $this->route('event');

                if ($event && $event->capacity && $event->registrations()->count() >= $event->capacity) {
                    $validator->errors()->add('email', 'Cet evenement est complet.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est deja inscrite a cet evenement.',
        ];
    }
}
```

- [ ] **Step 5 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte, dont les 3 tests `EventRegistrationRulesTest`. Le test de throttle événement (Tâche 8) passe toujours : il utilise des emails distincts.

- [ ] **Step 6 : Commit**

```bash
git add database/migrations/ app/Http/Requests/StoreEventRegistrationRequest.php tests/Feature/EventRegistrationRulesTest.php
git commit -m "fix(metier): unicite email par evenement et respect de la capacite"
```

---

## Tâche 10 : Règles d'inscription aux clubs — clubs publiés, idempotence, transaction DB (TDD)

On peut aujourd'hui s'inscrire à un club non publié, soumettre deux fois le même formulaire (doublons), et une erreur en cours de boucle laisse des inscriptions partielles.

**Files:**
- Test: `tests/Feature/ClubRegistrationRulesTest.php` (create)
- Modify: `app/Http/Requests/StoreClubRegistrationRequest.php`
- Modify: `app/Http/Controllers/PublicSiteController.php` (méthode `registerClub()`)

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/ClubRegistrationRulesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubRegistrationRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeClub(array $overrides = []): Club
    {
        static $i = 0;
        $i++;

        return Club::create([
            'name' => "Club Test {$i}",
            'slug' => "club-test-{$i}",
            'lead_name' => 'Responsable',
            'description' => 'Description du club.',
            'is_published' => true,
            ...$overrides,
        ]);
    }

    public function test_cannot_register_to_unpublished_club(): void
    {
        $club = $this->makeClub(['is_published' => false]);

        $this->post(route('clubs.register'), [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$club->id],
        ])->assertSessionHasErrors('club_ids.0');

        $this->assertDatabaseCount('club_registrations', 0);
    }

    public function test_double_submission_does_not_duplicate(): void
    {
        $club = $this->makeClub();

        $payload = [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'email' => 'paul@example.com',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$club->id],
        ];

        $this->post(route('clubs.register'), $payload)->assertRedirect();
        $this->post(route('clubs.register'), $payload)->assertRedirect();

        $this->assertDatabaseCount('club_registrations', 1);
    }

    public function test_can_register_to_multiple_clubs_at_once(): void
    {
        $clubA = $this->makeClub();
        $clubB = $this->makeClub();

        $this->post(route('clubs.register'), [
            'last_name' => 'Obame',
            'first_name' => 'Paul',
            'class_name' => 'L2 Gestion',
            'club_ids' => [$clubA->id, $clubB->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('club_registrations', 2);
    }
}
```

- [ ] **Step 2 : Vérifier que les tests échouent**

```bash
php artisan test --filter=ClubRegistrationRulesTest
```

Attendu : `test_cannot_register_to_unpublished_club` FAIL (inscription acceptée), `test_double_submission_does_not_duplicate` FAIL (2 lignes), `test_can_register_to_multiple_clubs_at_once` PASS.

- [ ] **Step 3 : Restreindre la règle aux clubs publiés dans `StoreClubRegistrationRequest`**

Ajouter l'import :

```php
use Illuminate\Validation\Rule;
```

Remplacer :

```php
'club_ids.*' => ['exists:clubs,id'],
```

par :

```php
'club_ids.*' => [Rule::exists('clubs', 'id')->where('is_published', true)],
```

- [ ] **Step 4 : Rendre `registerClub()` idempotent et transactionnel**

Dans `app/Http/Controllers/PublicSiteController.php`, ajouter l'import :

```php
use Illuminate\Support\Facades\DB;
```

Remplacer la méthode `registerClub()` par :

```php
    public function registerClub(StoreClubRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            foreach ($data['club_ids'] as $clubId) {
                ClubRegistration::firstOrCreate(
                    [
                        'club_id' => $clubId,
                        'email' => $data['email'] ?? null,
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                    ],
                    [
                        'phone' => $data['phone'] ?? null,
                        'class_name' => $data['class_name'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'pending',
                    ],
                );
            }
        });

        return back()->with('success', 'Votre demande d inscription aux clubs a ete enregistree.');
    }
```

- [ ] **Step 5 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte. Note : le test de throttle club (Tâche 8) reste vert — les 5 premiers POST identiques redirigent (firstOrCreate silencieux), le 6e reçoit 429.

- [ ] **Step 6 : Commit**

```bash
git add app/Http/Requests/StoreClubRegistrationRequest.php app/Http/Controllers/PublicSiteController.php tests/Feature/ClubRegistrationRulesTest.php
git commit -m "fix(metier): inscription clubs limitee aux clubs publies, idempotente et transactionnelle"
```

---

## Tâche 11 : Écran de gestion des comptes BDE (réservé super_admin) (TDD)

Aucun moyen aujourd'hui de créer/désactiver un compte membre sans seeder ni tinker — bloquant pour la passation annuelle du bureau.

**Files:**
- Test: `tests/Feature/Admin/UserManagementTest.php` (create)
- Create: `app/Http/Controllers/Admin/UserController.php`
- Create: `app/Http/Requests/StoreUserRequest.php`
- Create: `app/Http/Requests/UpdateUserRequest.php`
- Create: `resources/js/Pages/Admin/Users/Index.vue`
- Modify: `routes/web.php` (groupe admin)
- Modify: `resources/js/Layouts/AdminLayout.vue`

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/Admin/UserManagementTest.php`**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bde_member_cannot_access_user_management(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_create_a_member_account(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(route('admin.users.store'), [
            'name' => 'Nouveau Membre',
            'email' => 'nouveau@bde-iitg.org',
            'password' => 'MotDePasseSolide2026',
            'role' => 'membre_bde',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user = User::where('email', 'nouveau@bde-iitg.org')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('membre_bde'));
        $this->assertTrue($user->is_active);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->delete(route('admin.users.destroy', $admin))->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_can_deactivate_another_account(): void
    {
        $this->actingAsSuperAdmin();

        $member = User::factory()->create(['is_active' => true]);
        $member->syncRoles(['membre_bde']);

        $this->put(route('admin.users.update', $member), [
            'name' => $member->name,
            'email' => $member->email,
            'role' => 'membre_bde',
            'is_active' => false,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->is_active);
    }
}
```

- [ ] **Step 2 : Vérifier que les tests échouent**

```bash
php artisan test --filter=UserManagementTest
```

Attendu : FAIL — `Route [admin.users.index] not defined.`

- [ ] **Step 3 : Créer `app/Http/Requests/StoreUserRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'title' => ['nullable', 'string', 'max:255'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:super_admin,membre_bde'],
        ];
    }
}
```

- [ ] **Step 4 : Créer `app/Http/Requests/UpdateUserRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'title' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'in:super_admin,membre_bde'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
```

- [ ] **Step 5 : Créer `app/Http/Controllers/Admin/UserController.php`**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->with('roles')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'title' => $user->title,
                    'is_active' => $user->is_active,
                    'role' => $user->roles->first()?->name,
                ]),
            'roles' => ['super_admin', 'membre_bde'],
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'title' => $data['title'] ?? null,
            'password' => $data['password'],
            'is_active' => true,
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Compte cree.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $demotesSelf = $user->is($request->user())
            && ($data['role'] !== 'super_admin' || ! ($data['is_active'] ?? true));

        if ($demotesSelf) {
            return back()->with('error', 'Vous ne pouvez pas retirer vos propres acces.');
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->title = $data['title'] ?? null;
        $user->is_active = $data['is_active'] ?? true;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Compte mis a jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Impossible de supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Compte supprime.');
    }
}
```

- [ ] **Step 6 : Ajouter les routes dans `routes/web.php`**

Dans le groupe `admin` (après la ligne `Route::get('/', DashboardController::class)...`), ajouter :

```php
        Route::middleware('role:super_admin')->group(function (): void {
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'store', 'update', 'destroy']);
        });
```

- [ ] **Step 7 : Vérifier que les tests backend passent**

```bash
php artisan test
```

Attendu : suite verte, dont les 4 tests `UserManagementTest`.

- [ ] **Step 8 : Créer la page `resources/js/Pages/Admin/Users/Index.vue`**

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    roles: Array,
    currentUserId: Number,
});

const form = useForm({ name: '', email: '', title: '', password: '', role: 'membre_bde' });

const submit = () => form.post(route('admin.users.store'), { preserveScroll: true, onSuccess: () => form.reset() });

const updateUser = (user) => {
    router.put(
        route('admin.users.update', user.id),
        { name: user.name, email: user.email, title: user.title, role: user.role, is_active: user.is_active },
        { preserveScroll: true },
    );
};

const remove = (id) => router.delete(route('admin.users.destroy', id), { preserveScroll: true });
</script>

<template>
    <AdminLayout title="Comptes BDE">
        <section class="shell-card p-6">
            <h3 class="text-lg font-semibold">Nouveau compte</h3>
            <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <input v-model="form.name" type="text" placeholder="Nom complet" class="rounded-2xl border-slate-200" required />
                <input v-model="form.email" type="email" placeholder="Email" class="rounded-2xl border-slate-200" required />
                <input v-model="form.title" type="text" placeholder="Fonction (optionnel)" class="rounded-2xl border-slate-200" />
                <input v-model="form.password" type="password" placeholder="Mot de passe" class="rounded-2xl border-slate-200" required />
                <select v-model="form.role" class="rounded-2xl border-slate-200">
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-2.5 font-semibold text-white" :disabled="form.processing">
                    Creer le compte
                </button>
            </form>
            <p v-if="Object.keys(form.errors).length" class="mt-3 text-sm text-rose-700">
                {{ Object.values(form.errors)[0] }}
            </p>
        </section>

        <section class="shell-card mt-6 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Nom</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Actif</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ user.name }}</p>
                            <p class="text-slate-500">{{ user.title }}</p>
                        </td>
                        <td class="px-5 py-4">{{ user.email }}</td>
                        <td class="px-5 py-4">
                            <select
                                v-model="user.role"
                                class="rounded-xl border-slate-200 text-sm"
                                :disabled="user.id === currentUserId"
                                @change="updateUser(user)"
                            >
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </td>
                        <td class="px-5 py-4">
                            <input
                                v-model="user.is_active"
                                type="checkbox"
                                :disabled="user.id === currentUserId"
                                @change="updateUser(user)"
                            />
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button v-if="user.id !== currentUserId" class="text-rose-700" @click="remove(user.id)">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AdminLayout>
</template>
```

- [ ] **Step 9 : Ajouter le lien de navigation (visible super_admin uniquement) dans `resources/js/Layouts/AdminLayout.vue`**

Le middleware `HandleInertiaRequests` partage déjà `auth.user` avec ses rôles (`loadMissing('roles')`). Remplacer le bloc `const links = [ ... ];` par :

```js
const isSuperAdmin = computed(() => (user.value?.roles ?? []).some((role) => role.name === 'super_admin'));

const links = computed(() => [
    { label: 'Tableau de bord', href: route('admin.dashboard'), active: route().current('admin.dashboard') },
    { label: 'Membres', href: route('admin.bureau-members.index'), active: route().current('admin.bureau-members.*') },
    { label: 'Clubs', href: route('admin.clubs.index'), active: route().current('admin.clubs.*') },
    { label: 'Inscriptions clubs', href: route('admin.club-registrations.index'), active: route().current('admin.club-registrations.*') },
    { label: 'Evenements', href: route('admin.events.index'), active: route().current('admin.events.*') },
    { label: 'Inscriptions evenements', href: route('admin.event-registrations.index'), active: route().current('admin.event-registrations.*') },
    { label: 'Tresorerie', href: route('admin.transactions.index'), active: route().current('admin.transactions.*') },
    { label: 'Documents', href: route('admin.documents.index'), active: route().current('admin.documents.*') },
    { label: 'Historique', href: route('admin.historical-entries.index'), active: route().current('admin.historical-entries.*') },
    { label: 'Medias', href: route('admin.media-assets.index'), active: route().current('admin.media-assets.*') },
    { label: 'Messages', href: route('admin.contact-messages.index'), active: route().current('admin.contact-messages.*') },
    ...(isSuperAdmin.value
        ? [{ label: 'Comptes', href: route('admin.users.index'), active: route().current('admin.users.*') }]
        : []),
]);
```

(`computed` est déjà importé en haut du fichier. Le template itère sur `links` : un `computed` s'utilise à l'identique.)

- [ ] **Step 10 : Vérifier le build frontend et la suite**

```bash
npm run build
php artisan test
```

Attendu : build Vite sans erreur ; suite verte.

- [ ] **Step 11 : Commit**

```bash
git add -A
git commit -m "feat(admin): gestion des comptes BDE reservee au super admin"
```

---

## Tâche 12 : Réserver la trésorerie au super_admin via les permissions (TDD)

`membre_bde` a aujourd'hui exactement les mêmes droits que `super_admin`, y compris la trésorerie. On retire `manage transactions` au rôle membre et on protège les routes par la permission (les permissions existent déjà, elles sont enfin utilisées).

**Files:**
- Test: `tests/Feature/Admin/TransactionAuthorizationTest.php` (create)
- Modify: `database/seeders/RolePermissionSeeder.php`
- Modify: `routes/web.php` (groupe admin)
- Modify: `resources/js/Layouts/AdminLayout.vue` (masquer « Tresorerie » aux membres)

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/Admin/TransactionAuthorizationTest.php`**

```php
<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bde_member_cannot_access_transactions(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.transactions.index'))->assertForbidden();
    }

    public function test_super_admin_can_access_transactions(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin.transactions.index'))->assertOk();
    }

    public function test_bde_member_still_accesses_other_admin_pages(): void
    {
        $this->actingAsBdeMember();

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.clubs.index'))->assertOk();
    }
}
```

- [ ] **Step 2 : Vérifier que le test échoue**

```bash
php artisan test --filter=TransactionAuthorizationTest
```

Attendu : `test_bde_member_cannot_access_transactions` FAIL (200 au lieu de 403), les deux autres PASS.

- [ ] **Step 3 : Retirer `manage transactions` au rôle membre dans `RolePermissionSeeder`**

Remplacer :

```php
        $member->syncPermissions(self::PERMISSIONS);
```

par :

```php
        $member->syncPermissions(array_values(array_diff(self::PERMISSIONS, ['manage transactions'])));
```

- [ ] **Step 4 : Protéger les routes trésorerie dans `routes/web.php`**

Dans le groupe `admin`, remplacer les deux lignes :

```php
        Route::get('transactions/attachments/{attachment}/download', [TransactionController::class, 'downloadAttachment'])->name('transactions.attachments.download');
        Route::resource('transactions', TransactionController::class)->except(['create', 'show']);
```

par :

```php
        Route::middleware('permission:manage transactions')->group(function (): void {
            Route::get('transactions/attachments/{attachment}/download', [TransactionController::class, 'downloadAttachment'])->name('transactions.attachments.download');
            Route::resource('transactions', TransactionController::class)->except(['create', 'show']);
        });
```

- [ ] **Step 5 : Masquer « Tresorerie » aux membres dans `AdminLayout.vue`**

Dans le `computed` `links` créé en Tâche 11, déplacer l'entrée Tresorerie dans le bloc conditionnel super admin :

```js
const links = computed(() => [
    { label: 'Tableau de bord', href: route('admin.dashboard'), active: route().current('admin.dashboard') },
    { label: 'Membres', href: route('admin.bureau-members.index'), active: route().current('admin.bureau-members.*') },
    { label: 'Clubs', href: route('admin.clubs.index'), active: route().current('admin.clubs.*') },
    { label: 'Inscriptions clubs', href: route('admin.club-registrations.index'), active: route().current('admin.club-registrations.*') },
    { label: 'Evenements', href: route('admin.events.index'), active: route().current('admin.events.*') },
    { label: 'Inscriptions evenements', href: route('admin.event-registrations.index'), active: route().current('admin.event-registrations.*') },
    { label: 'Documents', href: route('admin.documents.index'), active: route().current('admin.documents.*') },
    { label: 'Historique', href: route('admin.historical-entries.index'), active: route().current('admin.historical-entries.*') },
    { label: 'Medias', href: route('admin.media-assets.index'), active: route().current('admin.media-assets.*') },
    { label: 'Messages', href: route('admin.contact-messages.index'), active: route().current('admin.contact-messages.*') },
    ...(isSuperAdmin.value
        ? [
              { label: 'Tresorerie', href: route('admin.transactions.index'), active: route().current('admin.transactions.*') },
              { label: 'Comptes', href: route('admin.users.index'), active: route().current('admin.users.*') },
          ]
        : []),
]);
```

- [ ] **Step 6 : Vérifier tests + build**

```bash
php artisan test
npm run build
```

Attendu : suite verte, build sans erreur.

- [ ] **Step 7 : Commit + note de déploiement**

```bash
git add -A
git commit -m "feat(securite): tresorerie reservee a la permission manage transactions"
```

**Note de déploiement :** sur les environnements existants, relancer `php artisan db:seed --class=RolePermissionSeeder` pour appliquer le retrait de permission.

---

## Tâche 13 : Cohérence des stats publiques, dashboard optimisé, cache des réglages (TDD)

Trois correctifs de logique/performance : stats publiques cohérentes avec le contenu publié, dashboard passant de ~15 requêtes agrégées à 3, réglages du site mis en cache.

**Files:**
- Test: `tests/Feature/PublicHomeTest.php` (create)
- Test: `tests/Feature/Admin/DashboardTest.php` (create)
- Modify: `app/Http/Controllers/PublicSiteController.php` (méthodes `home()` et `settings()`)
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `app/Models/SiteSetting.php`

- [ ] **Step 1 : Écrire les tests qui échouent — `tests/Feature/PublicHomeTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_stats_only_count_published_content(): void
    {
        Club::create([
            'name' => 'Club Visible',
            'slug' => 'club-visible',
            'lead_name' => 'Alex',
            'description' => 'Club publie.',
            'is_published' => true,
        ]);

        Club::create([
            'name' => 'Club Cache',
            'slug' => 'club-cache',
            'lead_name' => 'Sam',
            'description' => 'Club en brouillon.',
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Home')
                ->where('stats.clubs', 1)
            );
    }
}
```

- [ ] **Step 2 : Écrire le test dashboard — `tests/Feature/Admin/DashboardTest.php`**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_finance_chart_over_six_months(): void
    {
        $this->actingAsSuperAdmin();

        Transaction::create([
            'type' => 'income',
            'category' => 'Sponsoring',
            'amount' => 500,
            'description' => 'Sponsor local',
            'transaction_date' => now()->startOfMonth()->toDateString(),
        ]);

        Transaction::create([
            'type' => 'expense',
            'category' => 'Evenements',
            'amount' => 200,
            'description' => 'Achat deco',
            'transaction_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('charts.finance.labels', 6)
                ->has('charts.finance.income', 6)
                ->has('charts.finance.expense', 6)
                ->where('stats.income', 500.0)
                ->where('stats.expense', 200.0)
                ->where('stats.balance', 300.0)
            );
    }
}
```

- [ ] **Step 3 : Vérifier l'état des tests**

```bash
php artisan test --filter="PublicHomeTest|DashboardTest"
```

Attendu : `PublicHomeTest` **FAIL** (`stats.clubs` vaut 2). `DashboardTest` PASS (il verrouille le comportement avant refactoring — c'est le filet de sécurité du Step 5).

- [ ] **Step 4 : Corriger les stats publiques dans `PublicSiteController::home()`**

Remplacer le bloc `'stats' => [...]` par :

```php
            'stats' => [
                'clubs' => Club::where('is_published', true)->count(),
                'events' => Event::where('is_published', true)->count(),
                'registrations' => ClubRegistration::count() + EventRegistration::count(),
                'documents' => Document::where('is_public', true)->count(),
            ],
```

- [ ] **Step 5 : Optimiser `DashboardController` (1 requête pour 6 mois de finances, 1 seule requête events)**

Remplacer le fichier complet `app/Http/Controllers/Admin/DashboardController.php` par :

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BureauMember;
use App\Models\Club;
use App\Models\ClubRegistration;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $income = (float) Transaction::where('type', 'income')->sum('amount');
        $expense = (float) Transaction::where('type', 'expense')->sum('amount');

        $since = now()->subMonths(5)->startOfMonth();

        $recentTransactions = Transaction::query()
            ->where('transaction_date', '>=', $since->toDateString())
            ->get(['type', 'amount', 'transaction_date']);

        $monthly = collect(range(5, 0))->map(function (int $offset) use ($recentTransactions) {
            $date = now()->subMonths($offset);
            $ofMonth = $recentTransactions->filter(
                fn (Transaction $transaction) => $transaction->transaction_date->isSameMonth($date)
            );

            return [
                'label' => $date->translatedFormat('M Y'),
                'income' => (float) $ofMonth->where('type', 'income')->sum('amount'),
                'expense' => (float) $ofMonth->where('type', 'expense')->sum('amount'),
            ];
        });

        $eventsWithCounts = Event::withCount('registrations')->orderBy('starts_at')->limit(6)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'members' => BureauMember::where('is_active', true)->count(),
                'clubs' => Club::count(),
                'pendingClubRegistrations' => ClubRegistration::where('status', 'pending')->count(),
                'upcomingEvents' => Event::where('starts_at', '>=', now())->count(),
                'pendingEventRegistrations' => EventRegistration::where('status', 'pending')->count(),
                'documents' => Document::count(),
                'messages' => ContactMessage::where('status', 'new')->count(),
                'balance' => $income - $expense,
                'income' => $income,
                'expense' => $expense,
            ],
            'charts' => [
                'finance' => [
                    'labels' => $monthly->pluck('label')->values(),
                    'income' => $monthly->pluck('income')->values(),
                    'expense' => $monthly->pluck('expense')->values(),
                ],
                'events' => [
                    'labels' => $eventsWithCounts->pluck('name'),
                    'registrations' => $eventsWithCounts->pluck('registrations_count'),
                ],
            ],
            'recent' => [
                'transactions' => Transaction::latest('transaction_date')->limit(5)->get()->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'category' => $transaction->category,
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date?->toDateString(),
                ]),
                'events' => Event::orderBy('starts_at')->limit(5)->get()->map(fn (Event $event) => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'location' => $event->location,
                    'starts_at' => $event->starts_at?->toIso8601String(),
                ]),
                'messages' => ContactMessage::latest()->limit(5)->get()->map(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'status' => $message->status,
                    'created_at' => Carbon::parse($message->created_at)->toIso8601String(),
                ]),
            ],
        ]);
    }
}
```

- [ ] **Step 6 : Mettre en cache les réglages du site**

Dans `app/Http/Controllers/PublicSiteController.php`, ajouter l'import :

```php
use Illuminate\Support\Facades\Cache;
```

Remplacer la méthode `settings()` par :

```php
    private function settings(): array
    {
        return Cache::rememberForever('site-settings', function (): array {
            $settings = SiteSetting::query()->pluck('value', 'key')->all();

            foreach ($settings as $key => $value) {
                if ($value && Str::endsWith($key, '_path')) {
                    $settings[Str::replaceLast('_path', '_url', $key)] = Storage::url($value);
                }
            }

            return $settings;
        });
    }
```

Dans `app/Models/SiteSetting.php`, invalider ce cache à chaque écriture — fichier complet :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site-settings'));
        static::deleted(fn () => Cache::forget('site-settings'));
    }
}
```

- [ ] **Step 7 : Vérifier que tous les tests passent**

```bash
php artisan test
```

Attendu : suite verte, dont `PublicHomeTest` et `DashboardTest`.

- [ ] **Step 8 : Commit**

```bash
git add -A
git commit -m "perf: stats publiees coherentes, dashboard en 3 requetes agregees, cache des reglages"
```

---

## Tâche 14 : Pagination des registres alimentés par le public (TDD)

Les inscriptions (clubs, événements) et messages de contact croissent sans limite : leurs index admin chargent tout. On pagine ces trois écrans (25/page). Les autres index (contenus gérés à la main, volumes faibles) restent tels quels.

**Files:**
- Test: `tests/Feature/Admin/RegistersPaginationTest.php` (create)
- Create: `resources/js/Components/Pagination.vue`
- Modify: `app/Http/Controllers/Admin/EventRegistrationController.php` (méthode `index`)
- Modify: `app/Http/Controllers/Admin/ClubRegistrationController.php` (méthode `index`)
- Modify: `app/Http/Controllers/Admin/ContactMessageController.php` (méthode `index`)
- Modify: `resources/js/Pages/Admin/EventRegistrations/Index.vue`
- Modify: `resources/js/Pages/Admin/ClubRegistrations/Index.vue`
- Modify: `resources/js/Pages/Admin/ContactMessages/Index.vue`

- [ ] **Step 1 : Écrire le test qui échoue — `tests/Feature/Admin/RegistersPaginationTest.php`**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistersPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_messages_index_is_paginated(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 30; $i++) {
            ContactMessage::create([
                'name' => "Contact {$i}",
                'email' => "contact{$i}@example.com",
                'message' => 'Message de test suffisamment long.',
                'status' => 'new',
            ]);
        }

        $this->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ContactMessages/Index')
                ->has('messages.data', 25)
                ->has('messages.links')
            );
    }
}
```

Pré-requis à vérifier : `ContactMessage` doit avoir `name`, `email`, `message`, `status` dans `$fillable` (contrôler `app/Models/ContactMessage.php` ; si `status` n'y est pas, l'ajouter).

- [ ] **Step 2 : Vérifier que le test échoue**

```bash
php artisan test --filter=RegistersPaginationTest
```

Attendu : FAIL — `messages.data` n'existe pas (la prop est un tableau plat de 30 éléments).

- [ ] **Step 3 : Paginer les trois contrôleurs**

Dans `app/Http/Controllers/Admin/ContactMessageController.php`, remplacer la méthode `index()` par :

```php
    public function index(): Response
    {
        return Inertia::render('Admin/ContactMessages/Index', [
            'messages' => ContactMessage::query()
                ->latest()
                ->paginate(25)
                ->withQueryString()
                ->through(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'status' => $message->status,
                    'responded_at' => $message->responded_at?->toIso8601String(),
                    'created_at' => $message->created_at?->toIso8601String(),
                ]),
            'statuses' => ['new', 'in_progress', 'resolved'],
        ]);
    }
```

Dans `app/Http/Controllers/Admin/EventRegistrationController.php`, remplacer la méthode `index()` par :

```php
    public function index(): Response
    {
        return Inertia::render('Admin/EventRegistrations/Index', [
            'registrations' => EventRegistration::query()
                ->with('event')
                ->latest()
                ->paginate(25)
                ->withQueryString()
                ->through(fn (EventRegistration $registration) => [
                    'id' => $registration->id,
                    'event_name' => $registration->event?->name,
                    'full_name' => $registration->full_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'class_name' => $registration->class_name,
                    'status' => $registration->status,
                    'notes' => $registration->notes,
                    'created_at' => $registration->created_at?->toIso8601String(),
                ]),
            'statuses' => ['pending', 'validated', 'rejected'],
        ]);
    }
```

Dans `app/Http/Controllers/Admin/ClubRegistrationController.php`, remplacer la méthode `index()` par :

```php
    public function index(): Response
    {
        return Inertia::render('Admin/ClubRegistrations/Index', [
            'registrations' => ClubRegistration::query()
                ->with('club')
                ->latest()
                ->paginate(25)
                ->withQueryString()
                ->through(fn (ClubRegistration $registration) => [
                    'id' => $registration->id,
                    'club_name' => $registration->club?->name,
                    'last_name' => $registration->last_name,
                    'first_name' => $registration->first_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'class_name' => $registration->class_name,
                    'status' => $registration->status,
                    'notes' => $registration->notes,
                    'created_at' => $registration->created_at?->toIso8601String(),
                ]),
            'statuses' => ['pending', 'validated', 'rejected'],
        ]);
    }
```

- [ ] **Step 4 : Créer `resources/js/Components/Pagination.vue`**

```vue
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <nav v-if="links && links.length > 3" class="flex flex-wrap gap-1 border-t border-slate-100 px-5 py-4">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-xl px-3 py-1.5 text-sm"
                :class="link.active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                v-html="link.label"
            />
            <span v-else class="rounded-xl px-3 py-1.5 text-sm text-slate-300" v-html="link.label" />
        </template>
    </nav>
</template>
```

(`v-html` est nécessaire pour les libellés `&laquo;`/`&raquo;` générés côté serveur par Laravel — contenu non-utilisateur, sans risque.)

- [ ] **Step 5 : Adapter les trois pages Vue**

`resources/js/Pages/Admin/ContactMessages/Index.vue` — trois changements :

1. Ajouter l'import : `import Pagination from '@/Components/Pagination.vue';`
2. Changer la prop : `messages: Array,` → `messages: Object,`
3. Itérer sur `.data` : `v-for="message in messages"` → `v-for="message in messages.data"`
4. Ajouter après la boucle `<article>`, juste avant `</section>` :

```html
            <div class="shell-card">
                <Pagination :links="messages.links" />
            </div>
```

`resources/js/Pages/Admin/EventRegistrations/Index.vue` :

1. Ajouter l'import : `import Pagination from '@/Components/Pagination.vue';`
2. `registrations: Array,` → `registrations: Object,`
3. `v-for="registration in registrations"` → `v-for="registration in registrations.data"`
4. Ajouter `<Pagination :links="registrations.links" />` après `</table>`, avant `</section>`.

`resources/js/Pages/Admin/ClubRegistrations/Index.vue` (même structure tableau que EventRegistrations) :

1. Ajouter l'import : `import Pagination from '@/Components/Pagination.vue';`
2. `registrations: Array,` → `registrations: Object,`
3. `v-for="registration in registrations"` → `v-for="registration in registrations.data"`
4. Ajouter `<Pagination :links="registrations.links" />` après `</table>`, avant `</section>`.

- [ ] **Step 6 : Vérifier tests + build**

```bash
php artisan test
npm run build
```

Attendu : suite verte (dont `RegistersPaginationTest`), build sans erreur.

- [ ] **Step 7 : Commit**

```bash
git add -A
git commit -m "perf(admin): pagination des inscriptions et messages de contact"
```

---

## Tâche 15 : Vérification finale

**Files:** aucun nouveau fichier (formatage éventuel par Pint).

- [ ] **Step 1 : Formater le code PHP**

```bash
php vendor/bin/pint
```

Attendu : `PASS` ou liste de fichiers corrigés automatiquement.

- [ ] **Step 2 : Suite complète + build de production**

```bash
php artisan test
npm run build
```

Attendu : **100 % des tests passent** ; build Vite OK.

- [ ] **Step 3 : Smoke test manuel**

```bash
composer dev
```

Vérifier dans le navigateur (`http://localhost:8000`) :
1. Site public : accueil, clubs, événements, contact s'affichent ; les stats de l'accueil correspondent au contenu publié.
2. Connexion `admin@bde-iitg.test` / `password` (seedé en local) : dashboard OK, menus « Tresorerie » et « Comptes » visibles.
3. Connexion `membre@bde-iitg.test` / `password` : « Tresorerie » et « Comptes » absents du menu ; accès direct à `/admin/transactions` → 403.
4. Upload d'un document non public → l'URL de téléchargement exige d'être connecté (tester en navigation privée → 404).
5. Inscription deux fois au même événement avec le même email → message d'erreur.

- [ ] **Step 4 : Commit final (si Pint a modifié des fichiers) et tag**

```bash
git add -A
git commit -m "style: formatage pint" || true
git tag v0.2.0 -m "Correctifs audit : securite, regles metier, autorisation, perf"
```

---

## Notes de déploiement (à exécuter sur chaque environnement existant)

1. `composer install --no-dev` (spatie désormais inclus).
2. `php artisan migrate` (index unique event_registrations — la migration déduplique d'abord).
3. `php artisan db:seed --class=RolePermissionSeeder` (retrait de `manage transactions` aux membres).
4. Déplacer les fichiers privés : contenu de `storage/app/public/documents` et `storage/app/public/transactions` vers `storage/app/private/documents` et `storage/app/private/transactions` (cf. Tâche 6 Step 10).
5. Créer le premier admin réel : `php artisan bde:create-admin presidence@... --name="..."` (mot de passe demandé interactivement).
6. Vérifier que les anciens comptes de démo n'existent pas en production ; sinon les supprimer.

## Hors périmètre de ce plan (suites recommandées, par valeur métier)

- **Notifications email** : accusé de réception au étudiant + alerte bureau (contact, inscriptions), notification validé/rejeté.
- **Suivi budgétaire** : lier `Transaction` à un `Event`/`Club` (colonne morphs nullable) pour rapprocher budget alloué / dépensé.
- **Mandats / années scolaires** sur événements, transactions et inscriptions (archivage, passation).
- **Exports CSV** des inscriptions (pointage jour J) et transactions (bilan financier).
- **Recherche et filtres** dans les index admin ; pagination des index restants (transactions, documents).
- **Honeypot/captcha** sur les formulaires publics en complément du throttle.
- **API Resources** pour remplacer les méthodes `xxxData()` dupliquées ; Enums PHP pour statuts et catégories.
- Libellé « Événements récents » quand la home affiche le fallback d'événements passés.
