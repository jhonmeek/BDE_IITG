<script setup>
import InputError from '@/Components/InputError.vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { formatCurrency, formatDateTime } from '@/composables/useFormatters';

const props = defineProps({
    settings: Object,
    event: Object,
    relatedEvents: Array,
});

const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    class_name: '',
    notes: '',
});

const submit = () => form.post(route('events.register', props.event.slug));
</script>

<template>
    <SiteLayout :title="event.name">
        <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <article class="shell-card p-8 sm:p-10">
                <p class="badge-soft">Evenement BDE</p>
                <h1 class="mt-5 text-4xl font-semibold">{{ event.name }}</h1>
                <img
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url"
                    :alt="event.name"
                    class="mt-6 aspect-[16/9] w-full rounded-[1.75rem] object-cover"
                />
                <div class="mt-5 grid gap-3 text-sm text-slate-500 sm:grid-cols-2">
                    <p>Date : {{ formatDateTime(event.starts_at) }}</p>
                    <p>Lieu : {{ event.location }}</p>
                    <p>Budget : {{ formatCurrency(event.budget_allocated) }}</p>
                    <p v-if="event.capacity">Capacite : {{ event.capacity }}</p>
                </div>
                <p class="mt-6 text-base leading-8 text-slate-600">{{ event.description }}</p>
            </article>

            <div class="shell-card p-6 sm:p-8">
                <p class="badge-soft">Participation</p>
                <h2 class="mt-4 text-2xl font-semibold">S inscrire a cet evenement</h2>

                <form v-if="event.registration_enabled" class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Nom complet</label>
                        <input v-model="form.full_name" type="text" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.full_name" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input v-model="form.email" type="email" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Telephone</label>
                            <input v-model="form.phone" type="text" class="input-shell" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Classe</label>
                            <input v-model="form.class_name" type="text" class="input-shell" />
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Message</label>
                        <textarea v-model="form.notes" rows="4" class="input-shell" />
                    </div>
                    <button class="btn-primary w-full" :disabled="form.processing">Valider mon inscription</button>
                </form>
                <div v-else class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                    Les inscriptions en ligne ne sont pas ouvertes pour cet evenement.
                </div>
            </div>
        </div>

        <section class="mt-12">
            <h2 class="text-2xl font-semibold">Autres evenements a venir</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-3">
                <article v-for="item in relatedEvents" :key="item.id" class="shell-card p-5">
                    <img
                        v-if="item.cover_image_url"
                        :src="item.cover_image_url"
                        :alt="item.name"
                        class="mb-4 aspect-[16/9] w-full rounded-[1.25rem] object-cover"
                    />
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">{{ formatDateTime(item.starts_at) }}</p>
                    <h3 class="mt-3 text-xl font-semibold">{{ item.name }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ item.excerpt }}</p>
                    <Link :href="route('events.show', item.slug)" class="mt-4 inline-flex text-sm font-semibold" style="color: var(--bde-red);">
                        Ouvrir
                    </Link>
                </article>
            </div>
        </section>
    </SiteLayout>
</template>
