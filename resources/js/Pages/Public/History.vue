<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { formatDate } from '@/composables/useFormatters';

defineProps({
    settings: Object,
    entries: Array,
});
</script>

<template>
    <SiteLayout title="Historique">
        <section class="mb-10">
            <p class="badge-soft">Historique</p>
            <h1 class="section-title mt-4">Les temps forts du Bureau des Etudiants</h1>
            <p class="section-copy mt-4">
                Une lecture chronologique des initiatives qui ont structure la vie etudiante et les projets du bureau.
            </p>
        </section>

        <div class="space-y-6 border-l border-amber-200 pl-6">
            <article v-for="entry in entries" :key="entry.id" class="shell-card relative p-6 sm:p-8">
                <span class="absolute -left-[31px] top-8 h-3 w-3 rounded-full bg-amber-500" />
                <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--bde-red);">{{ entry.period_label }}</p>
                <h2 class="mt-3 text-2xl font-semibold">{{ entry.title }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ formatDate(entry.event_date) }}</p>
                <img
                    v-if="entry.image_url"
                    :src="entry.image_url"
                    :alt="entry.title"
                    class="mt-5 aspect-[16/9] w-full rounded-[1.75rem] object-cover"
                />
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ entry.content }}</p>
            </article>
        </div>
    </SiteLayout>
</template>
