<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/composables/useFormatters';

defineProps({
    settings: Object,
    club: Object,
    otherClubs: Array,
});
</script>

<template>
    <SiteLayout :title="club.name">
        <article class="shell-card p-8 sm:p-10">
            <p class="badge-soft">{{ club.category }}</p>
            <h1 class="mt-5 text-4xl font-semibold">{{ club.name }}</h1>
            <img
                v-if="club.image_url"
                :src="club.image_url"
                :alt="club.name"
                class="mt-6 h-28 w-28 rounded-[2rem] object-cover"
            />
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
                <span>Responsable : {{ club.lead_name }}</span>
                <span>Budget : {{ formatCurrency(club.budget_allocated) }}</span>
                <span v-if="club.registrations_count !== null">Inscriptions : {{ club.registrations_count }}</span>
            </div>
            <p class="mt-6 max-w-3xl text-base leading-8 text-slate-600">{{ club.description }}</p>
            <Link :href="route('clubs.index')" class="btn-secondary mt-8">Retour aux clubs</Link>
        </article>

        <section class="mt-12">
            <h2 class="text-2xl font-semibold">Autres clubs a explorer</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-3">
                <article v-for="item in otherClubs" :key="item.id" class="shell-card p-5">
                    <img
                        v-if="item.image_url"
                        :src="item.image_url"
                        :alt="item.name"
                        class="mb-4 h-16 w-16 rounded-[1.25rem] object-cover"
                    />
                    <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--bde-red);">{{ item.category }}</p>
                    <h3 class="mt-3 text-xl font-semibold">{{ item.name }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ item.summary }}</p>
                    <Link :href="route('clubs.show', item.slug)" class="mt-4 inline-flex text-sm font-semibold" style="color: var(--bde-red);">
                        Ouvrir
                    </Link>
                </article>
            </div>
        </section>
    </SiteLayout>
</template>
