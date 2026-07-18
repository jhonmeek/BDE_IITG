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
        SiteSetting::updateOrCreate(['key' => 'branding_logo_path'], ['value' => $this->legacyFile('legacy/branding/cea-c5f8cd56.svg'), 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'hero_image_path'], ['value' => $this->legacyFile('legacy/hero/sport-iitg-5198b458.jpg'), 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_email'], ['value' => 'bureau.etudiants@iitg.example', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+241 89 76 54 32', 'type' => 'string']);
        SiteSetting::updateOrCreate(['key' => 'contact_address'], ['value' => 'Institut International de Technologie et de Gestion, Libreville', 'type' => 'string']);

        $members = [
            ['name' => 'Luckas Mboungana', 'role_title' => 'President du BDE', 'mandate_label' => 'Mandat 2026', 'bio' => 'Coordonne les projets du bureau et les relations avec l administration.', 'sort_order' => 1, 'photo_path' => $this->legacyFile('legacy/bureau/presi-copy-6cb3ad8b.jpg')],
            ['name' => 'Kevine Ndjondo', 'role_title' => 'Vice-presidente', 'mandate_label' => 'Mandat 2026', 'bio' => 'Soutient la gouvernance du bureau et les actions communautaires.', 'sort_order' => 2],
            ['name' => 'Esiace Mouele', 'role_title' => 'Secretaire general', 'mandate_label' => 'Mandat 2026', 'bio' => 'Assure le suivi administratif et documentaire du bureau.', 'sort_order' => 3, 'photo_path' => $this->legacyFile('legacy/bureau/esiace2-143d341e.jpg')],
            ['name' => 'Annie Ruth Edoh', 'role_title' => 'Tresoriere', 'mandate_label' => 'Mandat 2026', 'bio' => 'Pilote la tresorerie, les justificatifs et les budgets du BDE.', 'sort_order' => 4, 'photo_path' => $this->legacyFile('legacy/bureau/annie-ruth-df9d9502.jpg')],
        ];

        foreach ($members as $member) {
            BureauMember::updateOrCreate(
                ['name' => $member['name']],
                [...$member, 'is_active' => true],
            );
        }

        $clubs = [
            ['name' => 'Club Anglais', 'slug' => 'club-anglais', 'category' => 'educatif', 'lead_name' => 'Responsable Anglais', 'summary' => 'Pratique orale et ecrite de l anglais.', 'description' => 'Le club accompagne les etudiants dans la prise de parole, la redaction et la confiance en anglais.', 'budget_allocated' => 150000, 'status' => 'active', 'is_published' => true, 'image_path' => $this->legacyFile('legacy/clubs/eng-club-5ff68204.png')],
            ['name' => 'Club Informatique', 'slug' => 'club-informatique', 'category' => 'educatif', 'lead_name' => 'Responsable Tech', 'summary' => 'Initiation aux outils et projets numeriques.', 'description' => 'Le club informatique organise des ateliers de code, de design et de productivite numerique.', 'budget_allocated' => 220000, 'status' => 'active', 'is_published' => true, 'image_path' => $this->legacyFile('legacy/clubs/tech-b3dd0843.jpg')],
            ['name' => 'Club Basketball', 'slug' => 'club-basketball', 'category' => 'sportif', 'lead_name' => 'Responsable Sport', 'summary' => 'Sport, cohesion et discipline.', 'description' => 'Le club basketball anime les entrainements, les selections et les rencontres inter-etudiantes.', 'budget_allocated' => 180000, 'status' => 'active', 'is_published' => true, 'image_path' => $this->legacyFile('legacy/clubs/bskt-0091969b.jpg')],
        ];

        foreach ($clubs as $club) {
            Club::updateOrCreate(['slug' => $club['slug']], $club);
        }

        $events = [
            ['name' => 'Journee d integration IITG', 'slug' => 'journee-integration-iitg', 'location' => 'Campus principal', 'excerpt' => 'Accueil des nouveaux etudiants et presentation du BDE.', 'description' => 'Une journee d integration avec animations, clubs, presentation du bureau et rencontres avec les equipes pedagogiques.', 'starts_at' => now()->addWeeks(2), 'budget_allocated' => 500000, 'capacity' => 300, 'participants_count' => 120, 'registration_enabled' => true, 'is_published' => true, 'cover_image_path' => $this->legacyFile('legacy/events/integre-day-7bcf4ee9.jpg')],
            ['name' => 'Forum des clubs', 'slug' => 'forum-des-clubs', 'location' => 'Hall IITG', 'excerpt' => 'Decouvrez les clubs et leurs responsables.', 'description' => 'Un forum pour mettre en avant les clubs educatifs, sportifs et communautaires et ouvrir les inscriptions.', 'starts_at' => now()->addMonth(), 'budget_allocated' => 250000, 'capacity' => 200, 'participants_count' => 90, 'registration_enabled' => true, 'is_published' => true, 'cover_image_path' => $this->legacyFile('legacy/events/cocot-tables-1e3e9707.jpg')],
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
            ['period_label' => '2024-2025', 'event_date' => now()->subYear()->toDateString(), 'content' => 'Le bureau a consolide ses poles activites, tresorerie, communication et partenariat.', 'sort_order' => 1, 'is_published' => true, 'image_path' => $this->legacyFile('legacy/history/debat-bde-presi-5642d602.jpg')],
        );

        HistoricalEntry::updateOrCreate(
            ['title' => 'Digitalisation progressive'],
            ['period_label' => '2025-2026', 'event_date' => now()->subMonths(6)->toDateString(), 'content' => 'Le BDE a engage la centralisation numerique de ses inscriptions, contenus et evenements.', 'sort_order' => 2, 'is_published' => true, 'image_path' => $this->legacyFile('legacy/history/iitg-office-864ecebd.jpg')],
        );

        MediaAsset::updateOrCreate(
            ['title' => 'Video historique du bureau'],
            ['collection' => 'video-history', 'media_type' => 'video', 'external_url' => null, 'file_path' => $this->legacyFile('legacy/media/whatsapp-video-2025-06-30-at-221328-b393a901-b105f871.mp4'), 'caption' => 'Capsule historique du bureau.', 'sort_order' => 1, 'is_published' => true],
        );

        $galleryItems = [
            ['title' => 'Journee d integration', 'file' => 'legacy/media/integre-day-7bcf4ee9.jpg', 'caption' => 'Accueil des nouveaux etudiants sur le campus.'],
            ['title' => 'Vie sportive IITG', 'file' => 'legacy/media/sport-iitg-5198b458.jpg', 'caption' => 'Les activites sportives du campus.'],
            ['title' => 'Debat inter-etudiants', 'file' => 'legacy/media/debat-bde-presi-5642d602.jpg', 'caption' => 'Les joutes oratoires organisees par le bureau.'],
            ['title' => 'Moments de convivialite', 'file' => 'legacy/media/cocot-tables-1e3e9707.jpg', 'caption' => 'Les rencontres et repas partages du BDE.'],
            ['title' => 'Le bureau reuni', 'file' => 'legacy/media/bde-untd.jpg', 'caption' => 'L equipe du BDE rassemblee.'],
            ['title' => 'Rencontres sportives', 'file' => 'legacy/media/bdr-sport2.jpg', 'caption' => 'Les tournois et rencontres inter-classes.'],
            ['title' => 'Integration des nouveaux', 'file' => 'legacy/media/intre-day2.jpg', 'caption' => 'Un autre regard sur la journee d integration.'],
            ['title' => 'Esprit d equipe', 'file' => 'legacy/media/sprt-bdr.jpg', 'caption' => 'Le sport comme lien entre etudiants.'],
            ['title' => 'Autour de la table', 'file' => 'legacy/media/tbl-sbg.jpg', 'caption' => 'Les moments d echange de la communaute.'],
            ['title' => 'Salle de classe IITG', 'file' => 'legacy/media/iitg-classroom.jpg', 'caption' => 'Le cadre d etude au quotidien.'],
        ];

        foreach ($galleryItems as $index => $item) {
            MediaAsset::updateOrCreate(
                ['title' => $item['title']],
                ['collection' => 'gallery', 'media_type' => 'image', 'external_url' => null, 'file_path' => $this->legacyFile($item['file']), 'caption' => $item['caption'], 'sort_order' => $index + 2, 'is_published' => true],
            );
        }

        MediaAsset::where('title', 'Galerie evenementielle')->delete();

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

    private function legacyFile(string $path): ?string
    {
        return Storage::disk('public')->exists($path) ? $path : null;
    }
}
