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
    <SiteLayout title="Videos et medias">
        <section class="mb-10">
            <p class="badge-soft">Galerie</p>
            <h1 class="section-title mt-4">Videos historiques et medias du bureau</h1>
            <p class="section-copy mt-4">
                Retrouvez les capsules, archives visuelles et moments marquants de la vie du campus.
            </p>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <article v-for="item in normalizedItems" :key="item.id" class="shell-card p-6">
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
                        Media disponible via un lien externe.
                    </div>
                </div>
                <h2 class="mt-5 text-2xl font-semibold">{{ item.title }}</h2>
                <p class="mt-2 text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--bde-red);">{{ item.collection }}</p>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ item.caption }}</p>
                <a v-if="item.url" :href="item.url" target="_blank" class="mt-4 inline-flex text-sm font-semibold" style="color: var(--bde-red);">
                    Ouvrir le media
                </a>
            </article>
        </div>
    </SiteLayout>
</template>
