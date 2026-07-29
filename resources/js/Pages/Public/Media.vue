<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    settings: Object,
    items: Array,
});

const normalizedItems = computed(() =>
    props.items.map((item) => {
        if (item.media_type === 'video' && item.url?.includes('youtube.com/watch?v=')) {
            return {
                ...item,
                embedUrl: item.url.replace('watch?v=', 'embed/'),
            };
        }

        return item;
    }),
);
</script>

<template>
    <SiteLayout title="Vidéos et médias">
        <section class="mb-10">
            <p class="eyebrow">Médiathèque</p>
            <h1 class="page-title section-title--rule mt-2">Vidéos historiques et médias du bureau</h1>
            <p class="lead mt-5">
                Retrouvez les capsules, archives visuelles et moments marquants de la vie du campus.
            </p>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <article v-for="item in normalizedItems" :key="item.id" class="shell-card card-pad">
                <div class="aspect-video overflow-hidden rounded-3xl bg-slate-100">
                    <iframe
                        v-if="item.media_type === 'video' && item.embedUrl"
                        class="h-full w-full"
                        :src="item.embedUrl"
                        :title="item.title"
                        allowfullscreen
                    />
                    <video
                        v-else-if="item.media_type === 'video' && item.url"
                        class="h-full w-full object-cover"
                        :src="item.url"
                        controls
                        preload="metadata"
                    />
                    <img
                        v-else-if="item.media_type === 'image' && item.url"
                        :src="item.url"
                        :alt="item.title"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full items-center justify-center p-6 text-center text-sm font-medium text-slate-500">
                        Média disponible via un lien externe.
                    </div>
                </div>
                <h2 class="mt-5 text-xl font-semibold sm:text-2xl">{{ item.title }}</h2>
                <p class="text-accent mt-1 text-sm font-medium">{{ item.collection }}</p>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ item.caption }}</p>
                <a v-if="item.url" :href="item.url" target="_blank" rel="noreferrer" class="mt-4 inline-flex text-sm font-semibold" style="color: var(--bde-red);">
                    Ouvrir le média
                </a>
            </article>
        </div>
    </SiteLayout>
</template>
