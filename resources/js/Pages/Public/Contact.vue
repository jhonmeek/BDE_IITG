<script setup>
import InputError from '@/Components/InputError.vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    settings: Object,
    documents: Array,
});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => form.post(route('contact.store'));
</script>

<template>
    <SiteLayout title="Contact">
        <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="shell-card card-pad">
                <p class="eyebrow">Contact</p>
                <h1 class="page-title section-title--rule mt-2">Parler au Bureau des Étudiants</h1>
                <div class="mt-6 space-y-4 text-sm leading-7 text-slate-600">
                    <p class="break-words"><span class="font-semibold text-slate-900">E-mail :</span> {{ settings.contact_email }}</p>
                    <p><span class="font-semibold text-slate-900">Téléphone :</span> {{ settings.contact_phone }}</p>
                    <p><span class="font-semibold text-slate-900">Adresse :</span> {{ settings.contact_address }}</p>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-semibold">Documents publics</h2>
                    <div class="mt-4 space-y-3">
                        <a
                            v-for="document in documents"
                            :key="document.id"
                            :href="document.download_url"
                            target="_blank"
                            rel="noreferrer"
                            class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 hover:border-stone-300"
                        >
                            <span class="min-w-0">{{ document.title }}</span>
                            <span class="text-accent font-semibold">{{ document.category }}</span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="shell-card card-pad">
                <p class="eyebrow">Formulaire</p>
                <h2 class="section-title mt-2">Envoyer un message</h2>
                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Nom</label>
                        <input v-model="form.name" type="text" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">E-mail</label>
                        <input v-model="form.email" type="email" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Sujet</label>
                        <input v-model="form.subject" type="text" class="input-shell" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Message</label>
                        <textarea v-model="form.message" rows="6" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.message" />
                    </div>
                    <button class="btn-primary w-full" :disabled="form.processing">Envoyer au bureau</button>
                </form>
            </section>
        </div>
    </SiteLayout>
</template>
