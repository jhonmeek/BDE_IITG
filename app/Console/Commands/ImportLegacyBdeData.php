<?php

namespace App\Console\Commands;

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
use App\Models\TransactionAttachment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportLegacyBdeData extends Command
{
    protected $signature = 'app:import-legacy-bde-data
        {--legacy-root= : Chemin absolu vers le dossier legacy BDE-web}
        {--keep-existing : Conserver les contenus existants au lieu de les remplacer}
        {--skip-mysql : Ignorer la tentative d import des tables MySQL legacy}';

    protected $description = 'Importe les contenus, fichiers et medias de l ancienne application BDE-web.';

    public function handle(): int
    {
        $legacyRoot = $this->resolveLegacyRoot();

        if (! $legacyRoot) {
            return self::FAILURE;
        }

        $admin = User::query()->role('super_admin')->first() ?? User::query()->first();

        if (! $this->option('keep-existing')) {
            $this->purgeCurrentContent();
        }

        $this->info("Import depuis: {$legacyRoot}");

        $this->importSettings($legacyRoot);
        $this->importMembers($legacyRoot);
        $this->importClubs($legacyRoot);
        $this->importHistoricalEntries($legacyRoot);
        $this->importEvents($legacyRoot);
        $this->importMediaAssets($legacyRoot);
        $this->importDocuments($legacyRoot, $admin?->id);

        if (! $this->option('skip-mysql')) {
            $this->importMySqlData();
        }

        $this->newLine();
        $this->info('Import legacy termine.');
        $this->line('Contenus importes:');
        $this->line('- '.BureauMember::count().' membres du bureau');
        $this->line('- '.Club::count().' clubs');
        $this->line('- '.HistoricalEntry::count().' entrees historiques');
        $this->line('- '.Event::count().' evenements');
        $this->line('- '.MediaAsset::count().' medias');
        $this->line('- '.Document::count().' documents');
        $this->line('- '.ClubRegistration::count().' inscriptions clubs');
        $this->line('- '.ContactMessage::count().' messages de contact');

        return self::SUCCESS;
    }

    private function resolveLegacyRoot(): ?string
    {
        $configuredRoot = $this->option('legacy-root') ?: config('legacy.root');

        if (! $configuredRoot) {
            $this->error('Aucun chemin legacy configure. Ajoutez LEGACY_BDE_ROOT ou utilisez --legacy-root=');

            return null;
        }

        $legacyRoot = realpath($configuredRoot);

        if (! $legacyRoot || ! File::isDirectory($legacyRoot)) {
            $this->error("Le dossier legacy est introuvable: {$configuredRoot}");

            return null;
        }

        return $legacyRoot;
    }

    private function purgeCurrentContent(): void
    {
        $this->warn('Nettoyage des contenus de demonstration avant import...');

        DB::transaction(function (): void {
            TransactionAttachment::query()->delete();
            ClubRegistration::query()->delete();
            EventRegistration::query()->delete();
            ContactMessage::query()->delete();
            Document::query()->delete();
            MediaAsset::query()->delete();
            HistoricalEntry::query()->delete();
            Transaction::query()->delete();
            Event::query()->delete();
            Club::query()->delete();
            BureauMember::query()->delete();
            SiteSetting::query()->delete();
        });
    }

    private function importSettings(string $legacyRoot): void
    {
        $this->info('Import des parametres du site...');

        $logoPath = $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.'Assets'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.'CEA.svg', 'legacy/branding');
        $faviconPath = $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.'Assets'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.'CEA.jpeg', 'legacy/branding');
        $heroImagePath = $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.'Assets'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.'bde'.DIRECTORY_SEPARATOR.'sport-IITG.jpg', 'legacy/hero');
        $heroSecondaryImagePath = $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.'Assets'.DIRECTORY_SEPARATOR.'Images'.DIRECTORY_SEPARATOR.'iitg-office.jpg', 'legacy/hero');

        $settings = [
            'branding_name' => 'CEA',
            'organization_name' => 'Bureau des Etudiants IITG',
            'organization_full_name' => "Comite pour l'Epanouissement Academique",
            'hero_title' => 'Bienvenue au Bureau des Etudiants',
            'hero_subtitle' => 'Rassemblement. Ambition. Dynamisme.',
            'hero_tagline' => "Comite pour l'Epanouissement academique",
            'hero_description' => "Le BDE est le relais entre les etudiants, les clubs et l'administration. Il soutient l'accueil, la representation, les evenements et les projets du campus.",
            'footer_about' => 'Nous representons et accompagnons les etudiants dans leurs initiatives, evenements et projets tout au long de leur parcours universitaire.',
            'contact_email' => 'bureau.etudiants@univ-exemple.edu',
            'contact_phone' => '+241 8976543',
            'contact_address' => "Institut International de Technologie et de Gestion, Libreville Avenue des Grandes ecoles, 100m de l'ENS",
            'social_facebook_url' => 'https://www.facebook.com/iitg.gabon',
            'social_tiktok_url' => 'https://www.tiktok.com/@ceabdeiitg',
            'branding_logo_path' => $logoPath,
            'branding_favicon_path' => $faviconPath,
            'hero_image_path' => $heroImagePath,
            'hero_secondary_image_path' => $heroSecondaryImagePath,
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => Str::endsWith($key, '_path') ? 'file' : 'string'],
            );
        }
    }

    private function importMembers(string $legacyRoot): void
    {
        $this->info('Import des membres du bureau...');

        $members = [
            ['name' => 'MBOUNGANA BOULINGUI Luckas', 'role_title' => 'President du BDE', 'photo' => 'Assets/Images/bde/Presi - Copy.jpg', 'bio' => "Coordonne la representation etudiante et le dialogue avec l'administration."],
            ['name' => 'NDJONDO MASSANDE Kevine', 'role_title' => 'Vice-President', 'photo' => 'Assets/Images/bde/Samel.jpg', 'bio' => 'Accompagne la gouvernance du bureau et le suivi des actions collectives.'],
            ['name' => 'MOUELE MOUELE Esiace', 'role_title' => 'Secretaire General', 'photo' => 'Assets/Images/bde/esiace2.jpg', 'bio' => 'Assure le suivi administratif, les comptes rendus et la circulation des informations.'],
            ['name' => 'MITCHIDICKA Jenifer', 'role_title' => 'Secretaire Generale Adjointe', 'photo' => 'Assets/Images/bde/mia.jpg', 'bio' => 'Soutient le secretariat et la coordination quotidienne des activites du bureau.'],
            ['name' => 'EDOH Annie Ruth', 'role_title' => 'Tresoriere', 'photo' => 'Assets/Images/bde/Annie-Ruth.jpg', 'bio' => 'Suit les ressources, justificatifs et besoins financiers des actions du BDE.'],
            ['name' => 'MAMADOU ZENABOU', 'role_title' => 'Commissaire aux comptes', 'photo' => 'Assets/Images/bde/zenab.jpg', 'bio' => 'Veille a la transparence et au controle des operations financieres.'],
            ['name' => 'BEBONA Bruslain Fanel', 'role_title' => 'Charge de la Communication', 'photo' => 'Assets/Images/bde/Fanel.jpg', 'bio' => 'Anime la communication du bureau et la mise en avant des initiatives etudiantes.'],
            ['name' => 'EDOU BIBANG Brunel', 'role_title' => 'Charge de la Communication Adjoint', 'photo' => 'Assets/Images/pinkPaint.jpg', 'bio' => 'Participe a la valorisation visuelle et numerique des activites du BDE.'],
            ['name' => 'NGOMAYILLA NDEMA Christopher', 'role_title' => 'Charge des relations exterieures', 'photo' => 'Assets/Images/bde/carl.jpg', 'bio' => 'Developpe les partenariats et les relations avec les acteurs externes du campus.'],
            ['name' => 'MOUNGOUNGOU Darlene', 'role_title' => 'Chargee des relations exterieures Adjointe', 'photo' => 'Assets/Images/bde/darlene2.jpg', 'bio' => 'Renforce les liens avec les partenaires et appuie la coordination institutionnelle.'],
            ['name' => 'NGUIMBI Yorish Carlain', 'role_title' => 'Charge des Sports', 'photo' => 'Assets/Images/bde/Yorish.jpg', 'bio' => 'Porte les activites sportives et la cohesion entre les etudiants.'],
            ['name' => 'WAGNA MALONGO Andrixon', 'role_title' => 'Charge des Sports Adjoint', 'photo' => 'Assets/Images/bde/Andrix.jpg', 'bio' => 'Soutient l organisation des rencontres, entrainements et temps forts sportifs.'],
        ];

        foreach ($members as $index => $member) {
            BureauMember::query()->updateOrCreate(
                ['name' => $member['name']],
                [
                    'role_title' => $member['role_title'],
                    'mandate_label' => 'Mandat CEA',
                    'photo_path' => $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $member['photo']), 'legacy/bureau'),
                    'bio' => $member['bio'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function importClubs(string $legacyRoot): void
    {
        $this->info('Import des clubs...');

        $clubs = [
            [
                'name' => 'Club Anglais',
                'slug' => 'club-anglais',
                'category' => 'educatif',
                'lead_name' => 'Coordination BDE',
                'summary' => "Ameliore ton anglais a l'oral et a l'ecrit.",
                'description' => "Le club anglais aide les etudiants a pratiquer l'expression orale, la redaction et la confiance dans les situations academiques et professionnelles.",
                'image' => 'Assets/Images/logo/eng-club.png',
            ],
            [
                'name' => "Club d'Art Oratoire",
                'slug' => 'club-art-oratoire',
                'category' => 'educatif',
                'lead_name' => 'Coordination BDE',
                'summary' => 'Ameliore tes competences oratoires.',
                'description' => "Le club d'art oratoire travaille la prise de parole, l'argumentation et la maitrise de l'expression en public.",
                'image' => 'Assets/Images/logo/oratoire.jpg',
            ],
            [
                'name' => 'Club de Basketball',
                'slug' => 'club-basketball',
                'category' => 'sportif',
                'lead_name' => 'Coordination BDE',
                'summary' => 'Nourris ta passion pour le basket.',
                'description' => 'Le club basketball structure les entrainements, les matchs amicaux et les temps de cohesion sportive du campus.',
                'image' => 'Assets/Images/logo/bskt.jpg',
            ],
            [
                'name' => 'Club de Football',
                'slug' => 'club-football',
                'category' => 'sportif',
                'lead_name' => 'Coordination BDE',
                'summary' => 'Nourris ta passion pour le foot.',
                'description' => 'Le club football fait vivre les rencontres sportives et la convivialite autour du jeu collectif.',
                'image' => 'Assets/Images/logo/foot.jpg',
            ],
            [
                'name' => 'Club Culture Gabonaise',
                'slug' => 'club-culture-gabonaise',
                'category' => 'culturel',
                'lead_name' => 'Coordination BDE',
                'summary' => 'Decouvrons ensemble nos racines culturelles.',
                'description' => 'Le club met en avant les traditions, langues, arts et references culturelles gabonaises au sein de la vie etudiante.',
                'image' => 'Assets/Images/logo/man.jpg',
            ],
            [
                'name' => "Club d'Informatique",
                'slug' => 'club-informatique',
                'category' => 'educatif',
                'lead_name' => 'Coordination BDE',
                'summary' => 'Decouvre les outils numeriques et developpe tes soft skills tech.',
                'description' => 'Le club informatique accompagne les etudiants dans la pratique des outils numeriques, la culture tech et les projets collaboratifs.',
                'image' => 'Assets/Images/logo/tech.jpg',
            ],
        ];

        foreach ($clubs as $club) {
            Club::query()->updateOrCreate(
                ['slug' => $club['slug']],
                [
                    'name' => $club['name'],
                    'category' => $club['category'],
                    'lead_name' => $club['lead_name'],
                    'summary' => $club['summary'],
                    'description' => $club['description'],
                    'budget_allocated' => 0,
                    'image_path' => $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $club['image']), 'legacy/clubs'),
                    'status' => 'active',
                    'is_published' => true,
                ],
            );
        }
    }

    private function importHistoricalEntries(string $legacyRoot): void
    {
        $this->info('Import de l historique...');

        $entries = [
            [
                'title' => 'Premiere election',
                'period_label' => '2021',
                'event_date' => '2021-11-01',
                'content' => "Premiere mise en place officielle du Bureau des Etudiants. Lancement des premiers clubs, dont le club anglais et le club d'art oratoire.",
                'image' => 'Assets/Images/bde/debat-bde-presi.jpg',
            ],
            [
                'title' => 'IITG Days',
                'period_label' => '2022',
                'event_date' => '2022-10-20',
                'content' => 'Organisation des premieres journees IITG avec competitions sportives, rencontres et conferences thematiques pour la communaute etudiante.',
                'image' => 'Assets/Images/bde/Integre-day.jpg',
            ],
            [
                'title' => 'Second mandat',
                'period_label' => '2023',
                'event_date' => '2023-11-15',
                'content' => "Reelection de l'equipe BDE, creation de nouveaux clubs comme informatique et culture gabonaise, et renforcement du projet Croix-Rouge.",
                'image' => 'Assets/Images/bde/debat-elite-carl.jpg',
            ],
            [
                'title' => 'Lancement du site web',
                'period_label' => '2024',
                'event_date' => '2024-07-01',
                'content' => "Digitalisation du BDE avec lancement du site officiel, formulaires d'inscription en ligne et mise en avant des clubs.",
                'image' => 'Assets/Images/iitg-office.jpg',
            ],
        ];

        foreach ($entries as $index => $entry) {
            HistoricalEntry::query()->updateOrCreate(
                ['title' => $entry['title']],
                [
                    'period_label' => $entry['period_label'],
                    'event_date' => $entry['event_date'],
                    'content' => $entry['content'],
                    'image_path' => $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entry['image']), 'legacy/history'),
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ],
            );
        }
    }

    private function importEvents(string $legacyRoot): void
    {
        $this->info('Import des evenements...');

        $events = [
            [
                'name' => "Journee d'integration 2024-2025",
                'slug' => 'journee-integration-2024-2025',
                'location' => 'Campus IITG',
                'excerpt' => 'Un moment de partage et de decouverte entre nouveaux etudiants.',
                'description' => "Cette journee a reuni les nouveaux etudiants autour de rencontres, d'animations et d'une presentation generale du bureau et des clubs.",
                'starts_at' => '2024-10-05 09:00:00',
                'cover' => 'Assets/Images/bde/Integre-day.jpg',
            ],
            [
                'name' => 'IITG Days',
                'slug' => 'iitg-days',
                'location' => 'Campus IITG',
                'excerpt' => 'Retour sur les activites sportives, culturelles et educatives du campus.',
                'description' => "Les IITG Days ont mis en avant l'esprit de promotion, la cohesion, le sport et les temps d'expression de la communaute etudiante.",
                'starts_at' => '2025-06-30 10:00:00',
                'cover' => 'Assets/Images/bde/bskt-club.jpg',
            ],
            [
                'name' => 'Stand Croix Rouge',
                'slug' => 'stand-croix-rouge',
                'location' => 'Campus IITG',
                'excerpt' => 'Sensibilisation et engagement humanitaire au coeur du campus.',
                'description' => "Ce stand a permis de sensibiliser les etudiants a l'engagement citoyen et humanitaire, dans la continuite des projets communautaires du BDE.",
                'starts_at' => '2025-03-15 11:00:00',
                'cover' => 'Assets/Images/cocot-tables.jpg',
            ],
        ];

        foreach ($events as $event) {
            Event::query()->updateOrCreate(
                ['slug' => $event['slug']],
                [
                    'name' => $event['name'],
                    'location' => $event['location'],
                    'excerpt' => $event['excerpt'],
                    'description' => $event['description'],
                    'starts_at' => $event['starts_at'],
                    'budget_allocated' => 0,
                    'capacity' => null,
                    'participants_count' => 0,
                    'cover_image_path' => $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $event['cover']), 'legacy/events'),
                    'registration_enabled' => false,
                    'is_published' => true,
                ],
            );
        }
    }

    private function importMediaAssets(string $legacyRoot): void
    {
        $this->info('Import des medias...');

        $items = [
            ['title' => "Debat d'installation du BDE", 'collection' => 'historique', 'media_type' => 'image', 'caption' => 'Temps fort autour de la mise en place du bureau.', 'file' => 'Assets/Images/bde/debat-bde-presi.jpg'],
            ['title' => "Journee d'integration", 'collection' => 'evenements', 'media_type' => 'image', 'caption' => "Image de la journee d'integration 2024-2025.", 'file' => 'Assets/Images/bde/Integre-day.jpg'],
            ['title' => 'Vie sportive IITG', 'collection' => 'campus', 'media_type' => 'image', 'caption' => 'La dynamique sportive etudiante sur le campus.', 'file' => 'Assets/Images/bde/sport-IITG.jpg'],
            ['title' => 'Club basketball', 'collection' => 'clubs', 'media_type' => 'image', 'caption' => 'Un apercu des activites du club basketball.', 'file' => 'Assets/Images/bde/bskt-club.jpg'],
            ['title' => 'Stand Croix Rouge', 'collection' => 'engagement', 'media_type' => 'image', 'caption' => "Un temps de sensibilisation et d'engagement humanitaire.", 'file' => 'Assets/Images/cocot-tables.jpg'],
            ['title' => 'Batiment IITG', 'collection' => 'campus', 'media_type' => 'image', 'caption' => "Vue d'ensemble de l'environnement IITG.", 'file' => 'Assets/Images/iitg-office.jpg'],
            ['title' => 'Flag IITG', 'collection' => 'videos', 'media_type' => 'video', 'caption' => "Animation autour de l'identite IITG.", 'file' => 'videos/Flag-IITG.mp4'],
            ['title' => 'IITG Days video', 'collection' => 'videos', 'media_type' => 'video', 'caption' => 'Retour video sur les activites du campus.', 'file' => 'videos/WhatsApp Video 2025-06-30 at 22.13.28_b393a901.mp4'],
            ['title' => 'Flower motion', 'collection' => 'videos', 'media_type' => 'video', 'caption' => "Capsule decorative presente dans l'ancien site.", 'file' => 'videos/flower.mp4'],
            ['title' => 'Stars motion', 'collection' => 'videos', 'media_type' => 'video', 'caption' => "Fond video heritage de l'ancien site.", 'file' => 'videos/stars.mp4'],
        ];

        foreach ($items as $index => $item) {
            MediaAsset::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'collection' => $item['collection'],
                    'media_type' => $item['media_type'],
                    'file_path' => $this->copyToPublicDisk($legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $item['file']), 'legacy/media'),
                    'caption' => $item['caption'],
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ],
            );
        }
    }

    private function importDocuments(string $legacyRoot, ?int $adminId): void
    {
        $this->info('Import des documents...');

        $documents = [
            ['title' => 'Projet EcoJeunes', 'category' => 'projet', 'file' => 'Uploads/6875504569afc-Projet_EcoJeunes.pdf'],
            ['title' => 'Lettres administratives', 'category' => 'administratif', 'file' => 'Uploads/6875505b674c0-Lettres_administratives.pdf'],
            ['title' => 'Le seminaire DEL', 'category' => 'evenementiel', 'file' => 'Uploads/68755074e6d59-LESEMINAIREDEL.docx'],
            ['title' => 'Invitation ISS', 'category' => 'courrier', 'file' => 'Uploads/6875509028995-Invitation-ISS.docx'],
        ];

        foreach ($documents as $document) {
            $sourcePath = $legacyRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $document['file']);
            $storedPath = $this->copyToPublicDisk($sourcePath, 'legacy/documents');

            if (! $storedPath) {
                continue;
            }

            Document::query()->updateOrCreate(
                ['title' => $document['title']],
                [
                    'category' => $document['category'],
                    'original_name' => basename($sourcePath),
                    'file_path' => $storedPath,
                    'is_public' => true,
                    'uploaded_by' => $adminId,
                ],
            );
        }
    }

    private function importMySqlData(): void
    {
        $database = config('legacy.mysql.database');

        if (! $database) {
            $this->warn('Import MySQL ignore: aucune base legacy configuree via LEGACY_BDE_MYSQL_DATABASE.');

            return;
        }

        $connection = 'legacy_mysql_import';

        Config::set("database.connections.{$connection}", [
            'driver' => 'mysql',
            'host' => config('legacy.mysql.host'),
            'port' => config('legacy.mysql.port'),
            'database' => $database,
            'username' => config('legacy.mysql.username'),
            'password' => config('legacy.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        try {
            DB::connection($connection)->getPdo();
        } catch (\Throwable $exception) {
            $this->warn("Connexion MySQL legacy impossible: {$exception->getMessage()}");

            return;
        }

        $this->info("Connexion MySQL legacy reussie sur la base {$database}.");

        $this->importLegacyContactMessages($connection);
        $this->importLegacyClubRegistrations($connection);
    }

    private function importLegacyContactMessages(string $connection): void
    {
        if (! DB::connection($connection)->getSchemaBuilder()->hasTable('contact_messages')) {
            $this->warn('Table legacy absente: contact_messages');

            return;
        }

        $rows = DB::connection($connection)->table('contact_messages')->get();

        foreach ($rows as $row) {
            ContactMessage::query()->updateOrCreate(
                [
                    'email' => $row->email,
                    'message' => $row->message,
                ],
                [
                    'name' => 'Contact legacy',
                    'subject' => 'Import legacy',
                    'status' => 'new',
                ],
            );
        }

        $this->line('- '.count($rows).' messages de contact legacy importes');
    }

    private function importLegacyClubRegistrations(string $connection): void
    {
        if (! DB::connection($connection)->getSchemaBuilder()->hasTable('inscriptions_clubs')) {
            $this->warn('Table legacy absente: inscriptions_clubs');

            return;
        }

        $clubsByNormalizedName = Club::query()
            ->get()
            ->mapWithKeys(fn (Club $club) => [$this->normalizeName($club->name) => $club]);

        $rows = DB::connection($connection)->table('inscriptions_clubs')->get();
        $created = 0;

        foreach ($rows as $row) {
            $clubNames = collect(explode(',', (string) $row->clubs))
                ->map(fn (string $name) => trim($name))
                ->filter();

            foreach ($clubNames as $clubName) {
                $club = $clubsByNormalizedName->get($this->normalizeName($clubName));

                if (! $club) {
                    continue;
                }

                ClubRegistration::query()->updateOrCreate(
                    [
                        'club_id' => $club->id,
                        'last_name' => $row->nom,
                        'first_name' => $row->prenom,
                        'class_name' => $row->classe,
                    ],
                    [
                        'email' => null,
                        'phone' => null,
                        'notes' => 'Import legacy MySQL',
                        'status' => 'pending',
                    ],
                );

                $created++;
            }
        }

        $this->line("- {$created} inscriptions clubs legacy importees");
    }

    private function copyToPublicDisk(string $sourcePath, string $targetDirectory): ?string
    {
        if (! File::exists($sourcePath)) {
            return null;
        }

        $extension = File::extension($sourcePath);
        $filename = Str::slug(pathinfo($sourcePath, PATHINFO_FILENAME));
        $hash = substr(md5_file($sourcePath) ?: Str::random(8), 0, 8);
        $relativePath = trim($targetDirectory, '/').'/'.$filename.'-'.$hash.($extension ? ".{$extension}" : '');

        if (! Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->put($relativePath, File::get($sourcePath));
        }

        return $relativePath;
    }

    private function normalizeName(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }
}
