<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashBanner from '@/Components/FlashBanner.vue';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const links = [
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
];
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-slate-100">
        <div class="mx-auto grid min-h-screen max-w-[1600px] gap-6 p-4 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="shell-card h-fit p-4 lg:sticky lg:top-4">
                <div class="rounded-3xl bg-slate-950 px-5 py-6 text-white">
                    <p class="text-xs uppercase tracking-[0.28em] text-amber-300">BDE IITG</p>
                    <h1 class="mt-3 text-2xl font-semibold text-white">Administration</h1>
                    <p class="mt-2 text-sm text-slate-300">Pilotage des activites, clubs, evenements, contenus et tresorerie.</p>
                </div>

                <nav class="mt-4 space-y-1">
                    <Link
                        v-for="item in links"
                        :key="item.href"
                        :href="item.href"
                        class="admin-link"
                        :class="{ active: item.active }"
                    >
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">{{ user?.name }}</p>
                    <p class="mt-1">{{ user?.title || 'Compte interne BDE' }}</p>
                    <div class="mt-3 flex gap-2">
                        <Link :href="route('home')" class="btn-secondary !px-4 !py-2">Site public</Link>
                        <Link :href="route('logout')" method="post" as="button" class="btn-primary !px-4 !py-2">Deconnexion</Link>
                    </div>
                </div>
            </aside>

            <main class="py-2">
                <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.22em] text-amber-700">Espace interne</p>
                        <h2 class="mt-2 text-3xl font-semibold text-slate-950">{{ title }}</h2>
                    </div>
                    <div class="text-sm text-slate-500">
                        Gestion centralisee du Bureau des Etudiants.
                    </div>
                </header>

                <FlashBanner />
                <slot />
            </main>
        </div>
    </div>
</template>
