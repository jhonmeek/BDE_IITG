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
    { label: 'Accueil', href: route('home') },
    { label: 'Bureau', href: route('bureau') },
    { label: 'Clubs', href: route('clubs.index') },
    { label: 'Evenements', href: route('events.index') },
    { label: 'Historique', href: route('history') },
    { label: 'Videos', href: route('media.index') },
    { label: 'Contact', href: route('contact') },
];
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen">
        <header>
            <div class="site-topbar">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-2 text-sm sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div class="flex items-center gap-3 font-semibold">
                        <span>FR</span>
                        <span class="opacity-70">|</span>
                        <span>EN</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-medium sm:text-sm">
                        <span>{{ settings.organization_full_name || 'Comite pour l Epanouissement Academique' }}</span>
                        <Link v-if="authUser" :href="route('admin.dashboard')" class="rounded-full bg-white/20 px-3 py-1.5 font-semibold text-white hover:bg-white/30">
                            Espace admin
                        </Link>
                        <Link v-else :href="route('login')" class="rounded-full bg-emerald-600 px-3 py-1.5 font-semibold text-white hover:bg-emerald-500">
                            Connexion
                        </Link>
                    </div>
                </div>
            </div>

            <div class="border-b border-white/60 bg-white/90 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <Link :href="route('home')" class="flex items-center gap-4">
                        <img
                            v-if="settings.branding_logo_url"
                            :src="settings.branding_logo_url"
                            :alt="settings.branding_name || 'CEA'"
                            class="h-12 w-12 rounded-full bg-white p-1 shadow-sm"
                        />
                        <div>
                            <p class="text-lg font-semibold tracking-[0.18em]" style="color: var(--bde-blue);">
                                {{ settings.branding_name || 'CEA' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ settings.organization_name || 'Bureau des Etudiants IITG' }}
                            </p>
                        </div>
                    </Link>

                    <nav class="flex flex-wrap items-center gap-2">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="site-nav-link"
                    >
                        {{ item.label }}
                    </Link>
                    </nav>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <FlashBanner />
            <slot />
        </main>

        <footer class="site-footer border-t border-white/10">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 text-sm sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <h3 class="text-base font-semibold">{{ settings.organization_name || 'Bureau des Etudiants IITG' }}</h3>
                    <p class="mt-3 text-slate-200/80">
                        {{ settings.footer_about || 'Plateforme de gestion centralisee des clubs, inscriptions, evenements, documents et contenus du bureau.' }}
                    </p>
                </div>
                <div>
                    <h4 class="text-base font-semibold">Liens utiles</h4>
                    <div class="mt-2 flex flex-col gap-2">
                        <Link :href="route('home')">Accueil</Link>
                        <Link :href="route('events.index')">Evenements</Link>
                        <Link :href="route('clubs.index')">Nos activites</Link>
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-semibold">Contact</h4>
                    <div class="mt-2 space-y-2 text-slate-200/80">
                        <p><span class="font-semibold text-white">Email :</span> {{ settings.contact_email }}</p>
                        <p><span class="font-semibold text-white">Telephone :</span> {{ settings.contact_phone }}</p>
                        <p><span class="font-semibold text-white">Adresse :</span> {{ settings.contact_address }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-semibold">Suivez-nous</h4>
                    <div class="mt-2 flex flex-col gap-2">
                        <a v-if="settings.social_facebook_url" :href="settings.social_facebook_url" target="_blank" rel="noreferrer">Facebook IITG</a>
                        <a v-if="settings.social_tiktok_url" :href="settings.social_tiktok_url" target="_blank" rel="noreferrer">TikTok CEA</a>
                        <Link :href="route('contact')">Contact direct</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
