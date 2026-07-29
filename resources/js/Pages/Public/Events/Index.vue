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
    <SiteLayout title="Événements">
        <section class="mb-10">
            <p class="eyebrow">Agenda</p>
            <h1 class="page-title section-title--rule mt-2">Les événements du Bureau des Étudiants</h1>
            <p class="lead mt-5">
                Retrouvez les activités à venir, les rencontres institutionnelles, les forums et les journées de cohésion du campus.
            </p>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <article v-for="event in events" :key="event.id" class="shell-card card-pad">
                <img
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url"
                    :alt="event.name"
                    class="mb-5 aspect-[16/9] w-full rounded-[1.75rem] object-cover"
                />
                <p class="text-accent text-sm font-medium">{{ formatDateTime(event.starts_at) }}</p>
                <h2 class="mt-4 text-xl font-semibold sm:text-2xl">{{ event.name }}</h2>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-slate-500">
                    <span>{{ event.location }}</span>
                    <span>Budget : {{ formatCurrency(event.budget_allocated) }}</span>
                    <span v-if="event.capacity">Capacité : {{ event.capacity }}</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ event.description }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="route('events.show', event.slug)" class="btn-primary">Voir le détail</Link>
                    <span v-if="event.registration_enabled" class="badge-soft">Inscription ouverte</span>
                </div>
            </article>
        </div>
    </SiteLayout>
</template>
