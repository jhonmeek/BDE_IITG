<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashBanner from '@/Components/FlashBanner.vue';

defineProps({
    title: String,
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const settings = computed(() => page.props.settings ?? {});

const mobileNavOpen = ref(false);

// Toute navigation Inertia referme le panneau mobile.
watch(() => page.url, () => {
    mobileNavOpen.value = false;
});

const nav = [
    { label: 'Accueil', href: route('home'), active: route().current('home') },
    { label: 'Bureau', href: route('bureau'), active: route().current('bureau') },
    { label: 'Clubs', href: route('clubs.index'), active: route().current('clubs.*') },
    { label: 'Événements', href: route('events.index'), active: route().current('events.*') },
    { label: 'Historique', href: route('history'), active: route().current('history') },
    { label: 'Médias', href: route('media.index'), active: route().current('media.*') },
    { label: 'Contact', href: route('contact'), active: route().current('contact') },
];
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="flex min-w-0 items-center gap-3">
                    <img
                        v-if="settings.branding_logo_url"
                        :src="settings.branding_logo_url"
                        :alt="settings.branding_name || 'CEA'"
                        class="h-10 w-10 flex-none rounded-full border border-stone-200 bg-white p-0.5 sm:h-11 sm:w-11"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold leading-tight text-brand sm:text-base">
                            {{ settings.organization_name || 'Bureau des Étudiants IITG' }}
                        </p>
                        <p class="truncate text-xs text-slate-500">
                            {{ settings.organization_full_name || 'Comité pour l’Épanouissement Académique' }}
                        </p>
                    </div>
                </Link>

                <!-- Navigation plein écran à partir de lg -->
                <div class="hidden items-center gap-1 lg:flex">
                    <nav class="flex items-center gap-0.5">
                        <Link
                            v-for="item in nav"
                            :key="item.href"
                            :href="item.href"
                            class="site-nav-link"
                            :class="{ active: item.active }"
                            :aria-current="item.active ? 'page' : undefined"
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

                <!-- Bouton burger sous lg -->
                <button
                    type="button"
                    class="-mr-1 inline-flex h-11 w-11 flex-none items-center justify-center rounded-lg border border-stone-300 text-slate-700 transition hover:bg-stone-50 lg:hidden"
                    :aria-expanded="mobileNavOpen"
                    aria-controls="site-mobile-nav"
                    :aria-label="mobileNavOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
                    @click="mobileNavOpen = !mobileNavOpen"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path v-if="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="-translate-y-2 opacity-0"
                leave-active-class="transition duration-100 ease-in"
                leave-to-class="-translate-y-2 opacity-0"
            >
                <nav
                    v-show="mobileNavOpen"
                    id="site-mobile-nav"
                    class="border-t border-stone-200 bg-white lg:hidden"
                >
                    <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
                        <Link
                            v-for="item in nav"
                            :key="item.href"
                            :href="item.href"
                            class="block rounded-lg px-3 py-2.5 text-base font-medium text-slate-700 hover:bg-stone-50"
                            :class="{ 'bg-stone-50 font-semibold text-brand ring-1 ring-stone-200': item.active }"
                            :aria-current="item.active ? 'page' : undefined"
                            @click="mobileNavOpen = false"
                        >
                            {{ item.label }}
                        </Link>

                        <Link
                            v-if="authUser"
                            :href="route('admin.dashboard')"
                            class="btn-primary mt-3 w-full"
                            @click="mobileNavOpen = false"
                        >
                            Espace admin
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="btn-secondary mt-3 w-full"
                            @click="mobileNavOpen = false"
                        >
                            Connexion
                        </Link>
                    </div>
                </nav>
            </Transition>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <FlashBanner />
            <slot />
        </main>

        <footer class="site-footer mt-12">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 text-sm sm:grid-cols-2 sm:px-6 sm:py-12 lg:grid-cols-4 lg:px-8">
                <div class="sm:col-span-2 lg:col-span-1">
                    <h3 class="text-base font-semibold">{{ settings.organization_name || 'Bureau des Étudiants IITG' }}</h3>
                    <p class="mt-3 leading-6 text-slate-300">
                        {{ settings.footer_about || 'Le bureau accompagne la vie étudiante du campus : clubs, événements, entraide et représentation.' }}
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Liens utiles</h4>
                    <div class="mt-3 flex flex-col gap-2 text-slate-300">
                        <Link :href="route('home')">Accueil</Link>
                        <Link :href="route('events.index')">Événements</Link>
                        <Link :href="route('clubs.index')">Nos clubs</Link>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Contact</h4>
                    <div class="mt-3 space-y-2 text-slate-300">
                        <p v-if="settings.contact_email" class="break-words">{{ settings.contact_email }}</p>
                        <p v-if="settings.contact_phone">{{ settings.contact_phone }}</p>
                        <p v-if="settings.contact_address">{{ settings.contact_address }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Suivez-nous</h4>
                    <div class="mt-3 flex flex-col gap-2 text-slate-300">
                        <a v-if="settings.social_facebook_url" :href="settings.social_facebook_url" target="_blank" rel="noreferrer">Facebook</a>
                        <a v-if="settings.social_tiktok_url" :href="settings.social_tiktok_url" target="_blank" rel="noreferrer">TikTok</a>
                        <Link :href="route('contact')">Nous écrire</Link>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <p class="mx-auto max-w-7xl px-4 py-4 text-xs text-slate-400 sm:px-6 lg:px-8">
                    © {{ new Date().getFullYear() }} — {{ settings.organization_name || 'Bureau des Étudiants IITG' }}
                </p>
            </div>
        </footer>
    </div>
</template>
