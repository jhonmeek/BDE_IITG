<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashBanner from '@/Components/FlashBanner.vue';

const props = defineProps({
    title: String,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const settings = computed(() => page.props.settings ?? {});

const nav = [
    { label: 'Accueil', href: route('home'), active: route().current('home') },
    { label: 'Bureau', href: route('bureau'), active: route().current('bureau') },
    { label: 'Clubs', href: route('clubs.index'), active: route().current('clubs.*') },
    { label: 'Evenements', href: route('events.index'), active: route().current('events.*') },
    { label: 'Historique', href: route('history'), active: route().current('history') },
    { label: 'Medias', href: route('media.index'), active: route().current('media.*') },
    { label: 'Contact', href: route('contact'), active: route().current('contact') },
];
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <img
                        v-if="settings.branding_logo_url"
                        :src="settings.branding_logo_url"
                        :alt="settings.branding_name || 'CEA'"
                        class="h-11 w-11 rounded-full border border-stone-200 bg-white p-0.5"
                    />
                    <div>
                        <p class="text-base font-bold leading-tight" style="color: var(--bde-blue);">
                            {{ settings.organization_name || 'Bureau des Etudiants IITG' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ settings.organization_full_name || 'Comite pour l Epanouissement Academique' }}
                        </p>
                    </div>
                </Link>

                <div class="flex flex-wrap items-center gap-1">
                    <nav class="flex flex-wrap items-center gap-1">
                        <Link
                            v-for="item in nav"
                            :key="item.href"
                            :href="item.href"
                            class="site-nav-link"
                            :class="{ active: item.active }"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <Link
                        v-if="authUser"
                        :href="route('admin.dashboard')"
                        class="btn-primary ml-2 !px-4 !py-2"
                    >
                        Espace admin
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="btn-secondary ml-2 !px-4 !py-2"
                    >
                        Connexion
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <FlashBanner />
            <slot />
        </main>

        <footer class="site-footer mt-12">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-sm sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <h3 class="text-base font-semibold">{{ settings.organization_name || 'Bureau des Etudiants IITG' }}</h3>
                    <p class="mt-3 leading-6 text-slate-300">
                        {{ settings.footer_about || 'Le bureau accompagne la vie etudiante du campus : clubs, evenements, entraide et representation.' }}
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Liens utiles</h4>
                    <div class="mt-3 flex flex-col gap-2 text-slate-300">
                        <Link :href="route('home')">Accueil</Link>
                        <Link :href="route('events.index')">Evenements</Link>
                        <Link :href="route('clubs.index')">Nos clubs</Link>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Contact</h4>
                    <div class="mt-3 space-y-2 text-slate-300">
                        <p v-if="settings.contact_email">{{ settings.contact_email }}</p>
                        <p v-if="settings.contact_phone">{{ settings.contact_phone }}</p>
                        <p v-if="settings.contact_address">{{ settings.contact_address }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Suivez-nous</h4>
                    <div class="mt-3 flex flex-col gap-2 text-slate-300">
                        <a v-if="settings.social_facebook_url" :href="settings.social_facebook_url" target="_blank" rel="noreferrer">Facebook</a>
                        <a v-if="settings.social_tiktok_url" :href="settings.social_tiktok_url" target="_blank" rel="noreferrer">TikTok</a>
                        <Link :href="route('contact')">Nous ecrire</Link>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <p class="mx-auto max-w-7xl px-4 py-4 text-xs text-slate-400 sm:px-6 lg:px-8">
                    {{ new Date().getFullYear() }} — {{ settings.organization_name || 'Bureau des Etudiants IITG' }}
                </p>
            </div>
        </footer>
    </div>
</template>
