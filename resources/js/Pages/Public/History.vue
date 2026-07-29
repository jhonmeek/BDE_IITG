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
            <p class="eyebrow">Historique</p>
            <h1 class="page-title section-title--rule mt-2">Les temps forts du Bureau des Étudiants</h1>
            <p class="lead mt-5">
                Une lecture chronologique des initiatives qui ont structuré la vie étudiante et les projets du bureau.
            </p>
        </section>

        <div class="space-y-6 border-l border-amber-200 pl-5 sm:pl-6">
            <article v-for="entry in entries" :key="entry.id" class="shell-card card-pad relative">
                <span class="absolute -left-[27px] top-8 h-3 w-3 rounded-full bg-amber-500 sm:-left-[31px]" />
                <p class="text-accent text-sm font-medium">{{ entry.period_label }}</p>
                <h2 class="mt-3 text-xl font-semibold sm:text-2xl">{{ entry.title }}</h2>
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
