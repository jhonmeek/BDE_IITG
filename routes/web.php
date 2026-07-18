<?php

use App\Http\Controllers\Admin\BureauMemberController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubRegistrationController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventRegistrationController;
use App\Http\Controllers\Admin\HistoricalEntryController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/bureau', [PublicSiteController::class, 'bureau'])->name('bureau');
Route::get('/clubs', [PublicSiteController::class, 'clubs'])->name('clubs.index');
Route::get('/clubs/{club:slug}', [PublicSiteController::class, 'clubShow'])->name('clubs.show');
Route::post('/clubs/register', [PublicSiteController::class, 'registerClub'])->name('clubs.register');
Route::get('/events', [PublicSiteController::class, 'events'])->name('events.index');
Route::get('/events/{event:slug}', [PublicSiteController::class, 'eventShow'])->name('events.show');
Route::post('/events/{event:slug}/register', [PublicSiteController::class, 'registerEvent'])->name('events.register');
Route::get('/historique', [PublicSiteController::class, 'history'])->name('history');
Route::get('/media', [PublicSiteController::class, 'media'])->name('media.index');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicSiteController::class, 'storeContact'])->name('contact.store');
Route::get('/documents/{document}/download', DocumentDownloadController::class)->name('documents.download');

Route::get('/dashboard', fn () => to_route('admin.dashboard'))
    ->middleware(['auth', 'role:super_admin|membre_bde'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:super_admin|membre_bde'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('bureau-members', BureauMemberController::class)->except(['create', 'show']);
        Route::resource('clubs', ClubController::class)->except(['create', 'show']);
        Route::resource('club-registrations', ClubRegistrationController::class)->only(['index', 'update', 'destroy']);
        Route::resource('events', EventController::class)->except(['create', 'show']);
        Route::resource('event-registrations', EventRegistrationController::class)->only(['index', 'update', 'destroy']);
        Route::get('transactions/attachments/{attachment}/download', [TransactionController::class, 'downloadAttachment'])->name('transactions.attachments.download');
        Route::resource('transactions', TransactionController::class)->except(['create', 'show']);
        Route::resource('documents', DocumentController::class)->except(['create', 'show']);
        Route::resource('historical-entries', HistoricalEntryController::class)->except(['create', 'show']);
        Route::resource('media-assets', MediaAssetController::class)->except(['create', 'show']);
        Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'update', 'destroy']);
    });

require __DIR__.'/auth.php';
