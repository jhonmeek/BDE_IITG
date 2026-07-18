<script setup>
import MetricCard from '@/Components/MetricCard.vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDateTime } from '@/composables/useFormatters';

defineProps({
    settings: Object,
    members: Array,
    clubs: Array,
    events: Array,
    history: Array,
    media: Array,
    stats: Object,
});
</script>

<template>
    <SiteLayout title="Accueil">
        <Head title="Accueil" />

        <section class="hero-panel overflow-hidden rounded-[2rem] px-6 py-10 sm:px-10 lg:px-12">
            <div class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]">
                <div v-motion-slide-visible-once-bottom>
                    <p class="badge-soft bg-white/10 text-white">Plateforme officielle</p>
                    <h1 class="mt-5 text-4xl font-semibold text-white sm:text-5xl">
                        {{ settings.hero_title || 'Bureau des Etudiants IITG' }}
                    </h1>
                    <p class="mt-4 text-lg font-medium text-emerald-300">
                        {{ settings.hero_subtitle }}
                    </p>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-white/80">
                        {{ settings.hero_description || settings.hero_tagline || settings.hero_subtitle }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link :href="route('clubs.index')" class="btn-primary">Explorer les clubs</Link>
                        <Link :href="route('events.index')" class="btn-secondary">Voir les evenements</Link>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="legacy-photo-frame sm:col-span-2">
                        <img
                            v-if="settings.hero_image_url"
                            :src="settings.hero_image_url"
                            alt="Vie etudiante IITG"
                            class="legacy-grid-image aspect-[16/9]"
                        />
                        <div v-else class="flex aspect-[16/9] items-center justify-center bg-white/10 text-sm font-semibold text-white/80">
                            Image du campus
                        </div>
                    </div>
                    <MetricCard label="Clubs actifs" :value="stats.clubs" hint="Espaces educatifs, sportifs et communautaires." />
                    <MetricCard label="Evenements" :value="stats.events" hint="Actions du bureau et rendez-vous de campus." />
                    <MetricCard label="Inscriptions" :value="stats.registrations" hint="Demandes clubs et evenements centralisees." />
                    <MetricCard label="Documents" :value="stats.documents" hint="Historique administratif et ressources internes." />
                </div>
            </div>
        </section>

        <section class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="badge-soft">Equipe du bureau</p>
                    <h2 class="section-title mt-4">Les membres qui portent la vie etudiante</h2>
                </div>
                <Link :href="route('bureau')" class="btn-secondary">Voir tout le bureau</Link>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="member in members"
                    :key="member.id"
                    class="shell-card p-6"
                    v-motion-slide-visible-once-bottom
                >
                    <img
                        v-if="member.photo_url"
                        :src="member.photo_url"
                        :alt="member.name"
                        class="h-20 w-20 rounded-3xl object-cover"
                    />
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-xl font-semibold text-amber-800">
                        {{ member.name.charAt(0) }}
                    </div>
                    <h3 class="mt-4 text-xl font-semibold">{{ member.name }}</h3>
                    <p class="mt-1 text-sm font-medium uppercase tracking-[0.14em]" style="color: var(--bde-red);">{{ member.role_title }}</p>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ member.bio }}</p>
                </article>
            </div>
        </section>

        <section class="mt-12 grid gap-8 lg:grid-cols-2">
            <div class="shell-card p-6 sm:p-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="badge-soft">Clubs</p>
                        <h2 class="section-title mt-4">Des espaces pour apprendre, creer et se depasser</h2>
                    </div>
                    <Link :href="route('clubs.index')" class="btn-secondary">Tous les clubs</Link>
                </div>

                <div class="mt-6 space-y-4">
                    <article
                        v-for="club in clubs"
                        :key="club.id"
                        class="rounded-3xl border border-slate-200 bg-white p-5"
                    >
                        <img
                            v-if="club.image_url"
                            :src="club.image_url"
                            :alt="club.name"
                            class="mb-4 h-20 w-20 rounded-3xl object-cover"
                        />
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color: var(--bde-red);">{{ club.category }}</p>
                                <h3 class="mt-2 text-xl font-semibold">{{ club.name }}</h3>
                            </div>
                            <Link :href="route('clubs.show', club.slug)" class="text-sm font-semibold" style="color: var(--bde-red);">
                                Ouvrir
                            </Link>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ club.summary }}</p>
                    </article>
                </div>
            </div>

            <div class="shell-card p-6 sm:p-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="badge-soft">Agenda</p>
                        <h2 class="section-title mt-4">Les prochains rendez-vous du BDE</h2>
                    </div>
                    <Link :href="route('events.index')" class="btn-secondary">Agenda complet</Link>
                </div>

                <div class="mt-6 space-y-4">
                    <article
                        v-for="event in events"
                        :key="event.id"
                        class="rounded-3xl border border-slate-200 bg-white p-5"
                    >
                        <img
                            v-if="event.cover_image_url"
                            :src="event.cover_image_url"
                            :alt="event.name"
                            class="mb-4 aspect-[16/9] w-full rounded-3xl object-cover"
                        />
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">{{ formatDateTime(event.starts_at) }}</p>
                        <h3 class="mt-2 text-xl font-semibold">{{ event.name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ event.location }}</p>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ event.excerpt }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mt-12 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="shell-card p-6 sm:p-8">
                <p class="badge-soft">Historique</p>
                <h2 class="section-title mt-4">La progression du bureau au fil des mandats</h2>
                <div class="mt-6 space-y-6 border-l border-amber-200 pl-6">
                    <article v-for="entry in history" :key="entry.id" class="relative">
                        <span class="absolute -left-[31px] top-1 h-3 w-3 rounded-full bg-amber-500" />
                        <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--bde-red);">{{ entry.period_label }}</p>
                        <h3 class="mt-2 text-xl font-semibold">{{ entry.title }}</h3>
                        <img
                            v-if="entry.image_url"
                            :src="entry.image_url"
                            :alt="entry.title"
                            class="mt-4 aspect-[16/9] w-full rounded-3xl object-cover"
                        />
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ entry.content }}</p>
                    </article>
                </div>
            </div>

            <div class="shell-card p-6 sm:p-8">
                <p class="badge-soft">Videos et medias</p>
                <h2 class="section-title mt-4">Une memoire visuelle des activites</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <article v-for="item in media" :key="item.id" class="rounded-3xl border border-slate-200 bg-white p-4">
                        <div class="aspect-video overflow-hidden rounded-2xl bg-slate-100">
                            <img v-if="item.media_type === 'image' && item.url" :src="item.url" :alt="item.title" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full items-center justify-center text-sm font-semibold text-slate-500">Video historique</div>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">{{ item.title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ item.caption }}</p>
                    </article>
                </div>
                <Link :href="route('media.index')" class="btn-secondary mt-6">Voir tous les medias</Link>
            </div>
        </section>
    </SiteLayout>
</template>
