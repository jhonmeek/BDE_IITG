<?php

namespace Database\Seeders;

use App\Models\BureauMember;
use App\Models\Club;
use App\Models\ClubRegistration;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\HistoricalEntry;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        SiteSetting::updateOrCreate(['key' => 'hero_title'], ['value' => 'Bureau des Etudiants IITG', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'hero_subtitle'], ['value' => 'Une gestion centralisee de la vie etudiante, des clubs et des projets du bureau.', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'bureau.etudiants@iitg.example', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+241 89 76 54 32', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_address'], ['value' => 'Institut International de Technologie et de Gestion, Libreville', 'type' => 'string']);

        $members = [
            ['name' => 'Luckas Mboungana', 'role_title' => 'President du BDE', 'mandate_label' => 'Mandat 2026', 'bio' => 'Coordonne les projets du bureau et les relations avec l administration.', 'sort_order' => 1],
            ['name' => 'Kevine Ndjondo', 'role_title' => 'Vice-presidente', 'mandate_label' => 'Mandat 2026', 'bio' => 'Soutient la gouvernance du bureau et les actions communautaires.', 'sort_order' => 2],
            ['name' => 'Esiace Mouele', 'role_title' => 'Secretaire general', 'mandate_label' => 'Mandat 2026', 'bio' => 'Assure le suivi administratif et documentaire du bureau.', 'sort_order' => 3],
            ['name' => 'Annie Ruth Edoh', 'role_title' => 'Tresoriere', 'mandate_label' => 'Mandat 2026', 'bio' => 'Pilote la tresorerie, les justificatifs et les budgets du BDE.', 'sort_order' => 4],
        ];

        foreach ($members as $member) {
            BureauMember::updateOrCreate(
                ['name' => $member['name']],
                [...$member, 'is_active' => true],
            );
        }

        $clubs = [
            ['name' => 'Club Anglais', 'slug' => 'club-anglais', 'category' => 'educatif', 'lead_name' => 'Responsable Anglais', 'summary' => 'Pratique orale et ecrite de l anglais.', 'description' => 'Le club accompagne les etudiants dans la prise de parole, la redaction et la confiance en anglais.', 'budget_allocated' => 150000, 'status' => 'active', 'is_published' => true],
            ['name' => 'Club Informatique', 'slug' => 'club-informatique', 'category' => 'educatif', 'lead_name' => 'Responsable Tech', 'summary' => 'Initiation aux outils et projets numeriques.', 'description' => 'Le club informatique organise des ateliers de code, de design et de productivite numerique.', 'budget_allocated' => 220000, 'status' => 'active', 'is_published' => true],
            ['name' => 'Club Basketball', 'slug' => 'club-basketball', 'category' => 'sportif', 'lead_name' => 'Responsable Sport', 'summary' => 'Sport, cohesion et discipline.', 'description' => 'Le club basketball anime les entrainements, les selections et les rencontres inter-etudiantes.', 'budget_allocated' => 180000, 'status' => 'active', 'is_published' => true],
        ];

        foreach ($clubs as $club) {
            Club::updateOrCreate(['slug' => $club['slug']], $club);
        }

        $events = [
            ['name' => 'Journee d integration IITG', 'slug' => 'journee-integration-iitg', 'location' => 'Campus principal', 'excerpt' => 'Accueil des nouveaux etudiants et presentation du BDE.', 'description' => 'Une journee d integration avec animations, clubs, presentation du bureau et rencontres avec les equipes pedagogiques.', 'starts_at' => now()->addWeeks(2), 'budget_allocated' => 500000, 'capacity' => 300, 'participants_count' => 120, 'registration_enabled' => true, 'is_published' => true],
            ['name' => 'Forum des clubs', 'slug' => 'forum-des-clubs', 'location' => 'Hall IITG', 'excerpt' => 'Decouvrez les clubs et leurs responsables.', 'description' => 'Un forum pour mettre en avant les clubs educatifs, sportifs et communautaires et ouvrir les inscriptions.', 'starts_at' => now()->addMonth(), 'budget_allocated' => 250000, 'capacity' => 200, 'participants_count' => 90, 'registration_enabled' => true, 'is_published' => true],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(['slug' => $event['slug']], $event);
        }

        $englishClub = Club::where('slug', 'club-anglais')->first();
        $integration = Event::where('slug', 'journee-integration-iitg')->first();

        if ($englishClub) {
            ClubRegistration::updateOrCreate(
                ['club_id' => $englishClub->id, 'email' => 'etudiant1@example.com'],
                ['last_name' => 'Mavoungou', 'first_name' => 'Sarah', 'phone' => '+24170000001', 'class_name' => 'L1 Informatique', 'status' => 'pending'],
            );
        }

        if ($integration) {
            EventRegistration::updateOrCreate(
                ['event_id' => $integration->id, 'email' => 'etudiant2@example.com'],
                ['full_name' => 'Junior Boussougou', 'phone' => '+24170000002', 'class_name' => 'L2 Gestion', 'status' => 'validated'],
            );
        }

        Transaction::updateOrCreate(
            ['description' => 'Cotisations de debut de mandat'],
            ['type' => 'income', 'category' => 'Administratif', 'amount' => 350000, 'transaction_date' => now()->subDays(20)->toDateString(), 'notes' => 'Contribution initiale du bureau.', 'created_by' => $admin?->id],
        );

        Transaction::updateOrCreate(
            ['description' => 'Depenses de communication'],
            ['type' => 'expense', 'category' => 'Evenements', 'amount' => 90000, 'transaction_date' => now()->subDays(8)->toDateString(), 'notes' => 'Impressions et supports visuels.', 'created_by' => $admin?->id],
        );

        HistoricalEntry::updateOrCreate(
            ['title' => 'Structuration du BDE'],
            ['period_label' => '2024-2025', 'event_date' => now()->subYear()->toDateString(), 'content' => 'Le bureau a consolide ses poles activites, tresorerie, communication et partenariat.', 'sort_order' => 1, 'is_published' => true],
        );

        HistoricalEntry::updateOrCreate(
            ['title' => 'Digitalisation progressive'],
            ['period_label' => '2025-2026', 'event_date' => now()->subMonths(6)->toDateString(), 'content' => 'Le BDE a engage la centralisation numerique de ses inscriptions, contenus et evenements.', 'sort_order' => 2, 'is_published' => true],
        );

        MediaAsset::updateOrCreate(
            ['title' => 'Video historique du bureau'],
            ['collection' => 'video-history', 'media_type' => 'video', 'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'caption' => 'Exemple de capsule historique du bureau.', 'sort_order' => 1, 'is_published' => true],
        );

        MediaAsset::updateOrCreate(
            ['title' => 'Galerie evenementielle'],
            ['collection' => 'gallery', 'media_type' => 'image', 'external_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f', 'caption' => 'Illustration de la vie etudiante et du travail en equipe.', 'sort_order' => 2, 'is_published' => true],
        );

        if (! Storage::disk('public')->exists('documents/reglement-bde.pdf')) {
            Storage::disk('public')->put('documents/reglement-bde.pdf', 'Reglement interieur du BDE IITG');
        }

        Document::updateOrCreate(
            ['title' => 'Reglement interieur du BDE'],
            ['category' => 'reglement', 'original_name' => 'reglement-bde.pdf', 'file_path' => 'documents/reglement-bde.pdf', 'is_public' => true, 'uploaded_by' => $admin?->id],
        );

        ContactMessage::updateOrCreate(
            ['email' => 'contacteur@example.com'],
            ['name' => 'Etudiant interesse', 'subject' => 'Information sur les clubs', 'message' => 'Bonjour, je souhaite savoir comment rejoindre le club informatique cette annee.', 'status' => 'new'],
        );
    }
}
