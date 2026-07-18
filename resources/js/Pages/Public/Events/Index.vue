<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Link } from '@inertiajs/vue3';
import { formatCurrency, formatDateTime } from '@/composables/useFormatters';

defineProps({
    settings: Object,
    events: Array,
});
</script>

<template>
    <SiteLayout title="Evenements">
        <section class="mb-10">
            <h1 class="section-title">Les evenements du Bureau des Etudiants</h1>
            <p class="section-copy mt-4">
                Retrouvez les activites a venir, les rencontres institutionnelles, les forums et les journees de cohesion du campus.
            </p>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <article v-for="event in events" :key="event.id" class="shell-card p-6 sm:p-8">
                <img
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url"
                    :alt="event.name"
                    class="mb-5 aspect-[16/9] w-full rounded-[1.75rem] object-cover"
                />
                <p class="text-sm font-medium" style="color: var(--bde-gold-text);">{{ formatDateTime(event.starts_at) }}</p>
                <h2 class="mt-4 text-2xl font-semibold">{{ event.name }}</h2>
                <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-500">
                    <span>{{ event.location }}</span>
                    <span>Budget : {{ formatCurrency(event.budget_allocated) }}</span>
                    <span v-if="event.capacity">Capacite : {{ event.capacity }}</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ event.description }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="route('events.show', event.slug)" class="btn-primary">Voir le detail</Link>
                    <span v-if="event.registration_enabled" class="badge-soft">Inscription ouverte</span>
                </div>
            </article>
        </div>
    </SiteLayout>
</template>
