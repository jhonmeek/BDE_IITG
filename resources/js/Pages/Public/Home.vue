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

        <section class="hero-panel overflow-hidden rounded-2xl px-6 py-10 sm:px-10 lg:px-12">
            <div class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]">
                <div>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                        {{ settings.hero_title || 'Bureau des Etudiants IITG' }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">
                        {{ settings.hero_description || settings.hero_subtitle }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link :href="route('clubs.index')" class="btn-primary !bg-white !text-slate-900 hover:!bg-slate-100">
                            Explorer les clubs
                        </Link>
                        <Link :href="route('events.index')" class="btn-secondary !border-white/30 !bg-transparent !text-white hover:!border-white/60 hover:!text-white">
                            Voir les evenements
                        </Link>
                    </div>
                </div>

                <div class="legacy-photo-frame">
                    <img
                        v-if="settings.hero_image_url"
                        :src="settings.hero_image_url"
                        alt="Vie etudiante sur le campus IITG"
                        class="legacy-grid-image aspect-[4/3]"
                    />
                    <div v-else class="flex aspect-[4/3] items-center justify-center text-sm text-white/70">
                        Image du campus
                    </div>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-white/10 sm:grid-cols-4">
                <div class="bg-white/5 px-5 py-4">
                    <p class="text-2xl font-bold text-white">{{ stats.clubs }}</p>
                    <p class="mt-1 text-sm text-slate-300">Clubs actifs</p>
                </div>
                <div class="bg-white/5 px-5 py-4">
                    <p class="text-2xl font-bold text-white">{{ stats.events }}</p>
                    <p class="mt-1 text-sm text-slate-300">Evenements</p>
                </div>
                <div class="bg-white/5 px-5 py-4">
                    <p class="text-2xl font-bold text-white">{{ stats.registrations }}</p>
                    <p class="mt-1 text-sm text-slate-300">Inscriptions</p>
                </div>
                <div class="bg-white/5 px-5 py-4">
                    <p class="text-2xl font-bold text-white">{{ stats.documents }}</p>
                    <p class="mt-1 text-sm text-slate-300">Documents publics</p>
                </div>
            </div>
        </section>

        <section class="mt-14">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="section-title">L equipe du bureau</h2>
                    <p class="section-copy mt-2">Les membres qui portent la vie etudiante au quotidien.</p>
                </div>
                <Link :href="route('bureau')" class="btn-secondary">Voir tout le bureau</Link>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="member in members"
                    :key="member.id"
                    class="shell-card p-6"
                >
                    <div class="flex items-center gap-4">
                        <img
                            v-if="member.photo_url"
                            :src="member.photo_url"
                            :alt="member.name"
                            class="h-16 w-16 rounded-full border border-stone-200 object-cover"
                        />
                        <div v-else class="flex h-16 w-16 items-center justify-center rounded-full text-xl font-semibold" style="background: var(--bde-gold-soft); color: var(--bde-gold-text);">
                            {{ member.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-base font-semibold leading-snug">{{ member.name }}</h3>
                            <p class="mt-0.5 text-sm font-medium" style="color: var(--bde-gold-text);">{{ member.role_title }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-600">{{ member.bio }}</p>
                </article>
            </div>
        </section>

        <section class="mt-14 grid gap-8 lg:grid-cols-2">
            <div>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="section-title">Nos clubs</h2>
                    <Link :href="route('clubs.index')" class="text-sm font-semibold" style="color: var(--bde-blue);">
                        Tous les clubs →
                    </Link>
                </div>

                <div class="mt-6 space-y-4">
                    <article
                        v-for="club in clubs"
                        :key="club.id"
                        class="shell-card flex gap-4 p-4"
                    >
                        <img
                            v-if="club.image_url"
                            :src="club.image_url"
                            :alt="club.name"
                            class="h-24 w-24 flex-none rounded-lg object-cover"
                        />
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold">{{ club.name }}</h3>
                                <span class="badge-soft">{{ club.category }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ club.summary }}</p>
                            <Link :href="route('clubs.show', club.slug)" class="mt-2 inline-block text-sm font-semibold" style="color: var(--bde-blue);">
                                Decouvrir le club →
                            </Link>
                        </div>
                    </article>
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="section-title">Prochains rendez-vous</h2>
                    <Link :href="route('events.index')" class="text-sm font-semibold" style="color: var(--bde-blue);">
                        Agenda complet →
                    </Link>
                </div>

                <div class="mt-6 space-y-4">
                    <article
                        v-for="event in events"
                        :key="event.id"
                        class="shell-card overflow-hidden"
                    >
                        <img
                            v-if="event.cover_image_url"
                            :src="event.cover_image_url"
                            :alt="event.name"
                            class="aspect-[21/9] w-full object-cover"
                        />
                        <div class="p-5">
                            <p class="text-sm font-medium" style="color: var(--bde-gold-text);">
                                {{ formatDateTime(event.starts_at) }} · {{ event.location }}
                            </p>
                            <h3 class="mt-1.5 text-lg font-semibold">{{ event.name }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ event.excerpt }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="mt-14 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="shell-card p-6 sm:p-8">
                <h2 class="section-title">Le bureau au fil des mandats</h2>
                <div class="mt-6 space-y-8 border-l-2 pl-6" style="border-color: var(--bde-gold-soft);">
                    <article v-for="entry in history" :key="entry.id" class="relative">
                        <span class="absolute -left-[31px] top-1.5 h-3 w-3 rounded-full border-2 border-white" style="background: var(--bde-gold);" />
                        <p class="text-sm font-semibold" style="color: var(--bde-gold-text);">{{ entry.period_label }}</p>
                        <h3 class="mt-1 text-lg font-semibold">{{ entry.title }}</h3>
                        <img
                            v-if="entry.image_url"
                            :src="entry.image_url"
                            :alt="entry.title"
                            class="mt-3 aspect-[16/9] w-full rounded-lg object-cover"
                        />
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ entry.content }}</p>
                    </article>
                </div>
            </div>

            <div class="shell-card p-6 sm:p-8">
                <h2 class="section-title">En images</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <article v-for="item in media" :key="item.id">
                        <div class="aspect-video overflow-hidden rounded-lg bg-slate-100">
                            <img v-if="item.media_type === 'image' && item.url" :src="item.url" :alt="item.title" class="h-full w-full object-cover" />
                            <video v-else-if="item.media_type === 'video' && item.url" :src="item.url" class="h-full w-full object-cover" controls preload="metadata" />
                            <div v-else class="flex h-full items-center justify-center text-sm text-slate-500">Media</div>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold">{{ item.title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ item.caption }}</p>
                    </article>
                </div>
                <Link :href="route('media.index')" class="btn-secondary mt-6">Voir tous les medias</Link>
            </div>
        </section>
    </SiteLayout>
</template>
