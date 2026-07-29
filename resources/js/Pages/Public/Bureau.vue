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
            <p class="eyebrow">Le bureau</p>
            <h1 class="page-title section-title--rule mt-2">Le bureau des étudiants et ses responsables</h1>
            <p class="lead mt-5">
                Le BDE représente les étudiants auprès de l’administration et coordonne les clubs, l’animation du campus, la communication et la trésorerie.
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
                <div v-else class="flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-amber-100 text-2xl font-semibold text-amber-800">
                    {{ member.name.charAt(0) }}
                </div>
                <h2 class="mt-4 text-xl font-semibold sm:text-2xl">{{ member.name }}</h2>
                <p class="text-accent mt-1 text-sm font-medium">{{ member.role_title }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ member.mandate_label }}</p>
                <p class="mt-4 text-sm leading-6 text-slate-600">{{ member.bio }}</p>
                <div class="mt-5 space-y-2 text-sm text-slate-500">
                    <p v-if="member.email" class="break-words">E-mail : {{ member.email }}</p>
                    <p v-if="member.phone">Téléphone : {{ member.phone }}</p>
                </div>
            </article>
        </div>
    </SiteLayout>
</template>
