<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';

defineProps({
    settings: Object,
    members: Array,
});
</script>

<template>
    <SiteLayout title="Bureau">
        <section class="mb-10">
            <h1 class="section-title">Le bureau des etudiants et ses responsables</h1>
            <p class="section-copy mt-4">
                Le BDE represente les etudiants aupres de l administration et coordonne les clubs, l animation du campus, la communication et la tresorerie.
            </p>
        </section>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <article v-for="member in members" :key="member.id" class="shell-card p-6">
                <img
                    v-if="member.photo_url"
                    :src="member.photo_url"
                    :alt="member.name"
                    class="h-24 w-24 rounded-[1.75rem] object-cover"
                />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-xl font-semibold text-amber-800">
                    {{ member.name.charAt(0) }}
                </div>
                <h2 class="mt-4 text-2xl font-semibold">{{ member.name }}</h2>
                <p class="mt-1 text-sm font-medium" style="color: var(--bde-gold-text);">{{ member.role_title }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ member.mandate_label }}</p>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ member.bio }}</p>
                <div class="mt-5 space-y-2 text-sm text-slate-500">
                    <p v-if="member.email">Email : {{ member.email }}</p>
                    <p v-if="member.phone">Telephone : {{ member.phone }}</p>
                </div>
            </article>
        </div>
    </SiteLayout>
</template>
