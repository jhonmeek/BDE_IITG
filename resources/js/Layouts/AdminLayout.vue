<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashBanner from '@/Components/FlashBanner.vue';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isSuperAdmin = computed(() => (user.value?.roles ?? []).some((role) => role.name === 'super_admin'));

// Deux etats distincts : le tiroir mobile (superpose) et le repli du rail
// sur grand ecran (la colonne disparait, le contenu prend toute la largeur).
const sidebarOpen = ref(false);
const railCollapsed = ref(false);

const RAIL_KEY = 'bde-admin-rail-collapsed';

// Toute navigation Inertia referme le tiroir mobile.
watch(() => page.url, () => {
    sidebarOpen.value = false;
});

// Le tiroir mobile bloque le defilement du fond tant qu'il est ouvert.
watch(sidebarOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

const toggleRail = () => {
    railCollapsed.value = !railCollapsed.value;
    localStorage.setItem(RAIL_KEY, railCollapsed.value ? '1' : '0');
};

const onKeydown = (event) => {
    if (event.key === 'Escape' && sidebarOpen.value) {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    railCollapsed.value = localStorage.getItem(RAIL_KEY) === '1';
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

const links = computed(() => [
    { label: 'Tableau de bord', href: route('admin.dashboard'), active: route().current('admin.dashboard') },
    { label: 'Membres', href: route('admin.bureau-members.index'), active: route().current('admin.bureau-members.*') },
    { label: 'Clubs', href: route('admin.clubs.index'), active: route().current('admin.clubs.*') },
    { label: 'Inscriptions clubs', href: route('admin.club-registrations.index'), active: route().current('admin.club-registrations.*') },
    { label: 'Événements', href: route('admin.events.index'), active: route().current('admin.events.*') },
    { label: 'Inscriptions événements', href: route('admin.event-registrations.index'), active: route().current('admin.event-registrations.*') },
    { label: 'Documents', href: route('admin.documents.index'), active: route().current('admin.documents.*') },
    { label: 'Historique', href: route('admin.historical-entries.index'), active: route().current('admin.historical-entries.*') },
    { label: 'Médias', href: route('admin.media-assets.index'), active: route().current('admin.media-assets.*') },
    { label: 'Messages', href: route('admin.contact-messages.index'), active: route().current('admin.contact-messages.*') },
    ...(isSuperAdmin.value
        ? [
              { label: 'Trésorerie', href: route('admin.transactions.index'), active: route().current('admin.transactions.*') },
              { label: 'Comptes', href: route('admin.users.index'), active: route().current('admin.users.*') },
          ]
        : []),
]);
</script>

<template>
    <Head :title="title" />

    <div class="admin-shell min-h-screen">
        <!-- Barre supérieure : seul point d'entrée de la navigation sous lg -->
        <header class="sticky top-0 z-40 border-b bg-white/95 backdrop-blur lg:hidden" style="border-color: var(--bde-line);">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <button
                    type="button"
                    class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-lg border border-stone-300 text-slate-700 transition hover:bg-stone-50 active:scale-95"
                    :aria-expanded="sidebarOpen"
                    aria-controls="admin-sidebar"
                    aria-label="Ouvrir le menu d’administration"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
                <p class="text-brand min-w-0 flex-1 truncate text-center text-sm font-semibold">{{ title }}</p>
                <Link :href="route('home')" class="text-brand text-sm font-semibold">Site</Link>
            </div>
        </header>

        <!-- Voile du tiroir -->
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-show="sidebarOpen"
                class="fixed inset-0 z-40 lg:hidden"
                style="background-color: rgba(11, 15, 86, 0.55);"
                aria-hidden="true"
                @click="sidebarOpen = false"
            />
        </Transition>

        <div
            class="admin-grid mx-auto grid min-h-screen max-w-[1600px] gap-6 p-4"
            :class="railCollapsed ? 'lg:grid-cols-[0px_minmax(0,1fr)] lg:gap-0' : 'lg:grid-cols-[280px_minmax(0,1fr)]'"
        >
            <!-- Tiroir mobile -->
            <Transition
                enter-active-class="transition-transform duration-200 ease-out"
                enter-from-class="-translate-x-full"
                leave-active-class="transition-transform duration-150 ease-in"
                leave-to-class="-translate-x-full"
            >
                <aside
                    v-show="sidebarOpen"
                    id="admin-sidebar"
                    class="fixed inset-y-0 left-0 z-50 w-[19rem] max-w-[85vw] overflow-y-auto border-r border-stone-200 bg-white p-4 lg:hidden"
                >
                    <div class="mb-4 flex justify-end">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-stone-300 text-slate-700 transition active:scale-95"
                            aria-label="Fermer le menu"
                            @click="sidebarOpen = false"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    <div class="brand-surface rounded-3xl px-5 py-6">
                        <p class="eyebrow-on-dark">BDE IITG</p>
                        <h1 class="mt-3 text-2xl font-semibold text-white">Administration</h1>
                        <p class="mt-2 text-sm text-slate-300">Pilotage des activités, clubs, événements, contenus et trésorerie.</p>
                    </div>

                    <nav class="mt-4 space-y-1">
                        <Link
                            v-for="item in links"
                            :key="item.href"
                            :href="item.href"
                            class="admin-link"
                            :class="{ active: item.active }"
                            :aria-current="item.active ? 'page' : undefined"
                            @click="sidebarOpen = false"
                        >
                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>

                    <div class="admin-panel mt-6 rounded-3xl border p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">{{ user?.name }}</p>
                        <p class="mt-1">{{ user?.title || 'Compte interne BDE' }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <Link :href="route('home')" class="btn-secondary !px-4 !py-2">Site public</Link>
                            <Link :href="route('logout')" method="post" as="button" class="btn-primary !px-4 !py-2">Déconnexion</Link>
                        </div>
                    </div>
                </aside>
            </Transition>

            <!-- Rail statique à partir de lg, repliable au clic -->
            <aside
                id="admin-rail"
                class="hidden h-fit overflow-hidden lg:sticky lg:top-4 lg:block"
                :class="railCollapsed ? 'lg:pointer-events-none lg:opacity-0' : 'shell-card p-4 lg:opacity-100'"
                :aria-hidden="railCollapsed"
                :inert="railCollapsed || undefined"
                style="transition: opacity 200ms ease;"
            >
                <div class="brand-surface rounded-3xl px-5 py-6">
                    <p class="eyebrow-on-dark">BDE IITG</p>
                    <h1 class="mt-3 text-2xl font-semibold text-white">Administration</h1>
                    <p class="mt-2 text-sm text-slate-300">Pilotage des activités, clubs, événements, contenus et trésorerie.</p>
                </div>

                <nav class="mt-4 space-y-1">
                    <Link
                        v-for="item in links"
                        :key="item.href"
                        :href="item.href"
                        class="admin-link"
                        :class="{ active: item.active }"
                        :aria-current="item.active ? 'page' : undefined"
                    >
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="admin-panel mt-6 rounded-3xl border p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">{{ user?.name }}</p>
                    <p class="mt-1">{{ user?.title || 'Compte interne BDE' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Link :href="route('home')" class="btn-secondary !px-4 !py-2">Site public</Link>
                        <Link :href="route('logout')" method="post" as="button" class="btn-primary !px-4 !py-2">Déconnexion</Link>
                    </div>
                </div>
            </aside>

            <main class="min-w-0 py-2">
                <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <!-- Repli du rail : visible seulement quand le rail existe -->
                        <button
                            type="button"
                            class="mt-1 hidden h-10 w-10 flex-none items-center justify-center rounded-lg border border-stone-300 bg-white text-slate-600 transition hover:bg-stone-50 active:scale-95 lg:inline-flex"
                            :aria-expanded="!railCollapsed"
                            aria-controls="admin-rail"
                            :aria-label="railCollapsed ? 'Afficher le menu latéral' : 'Masquer le menu latéral'"
                            :title="railCollapsed ? 'Afficher le menu latéral' : 'Masquer le menu latéral'"
                            @click="toggleRail"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <path stroke-linecap="round" d="M9 4v16" />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="railCollapsed ? 'M13 9l3 3-3 3' : 'M17 9l-3 3 3 3'"
                                />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <p class="eyebrow">Espace interne</p>
                            <h2 class="page-title mt-2">{{ title }}</h2>
                        </div>
                    </div>
                    <div class="text-sm text-slate-500">
                        Gestion centralisée du Bureau des Étudiants.
                    </div>
                </header>

                <FlashBanner />

                <!-- Le layout n'est pas persistant : il se remonte à chaque
                     navigation Inertia, l'animation se rejoue d'elle-même. -->
                <div class="page-enter">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
