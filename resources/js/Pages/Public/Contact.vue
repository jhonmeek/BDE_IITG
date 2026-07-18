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
            <section class="shell-card p-8">
                <p class="badge-soft">Contact</p>
                <h1 class="section-title mt-4">Parler au Bureau des Etudiants</h1>
                <div class="mt-6 space-y-4 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-slate-900">Email :</span> {{ settings.contact_email }}</p>
                    <p><span class="font-semibold text-slate-900">Telephone :</span> {{ settings.contact_phone }}</p>
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
                            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700"
                        >
                            <span>{{ document.title }}</span>
                            <span class="font-semibold text-amber-700">{{ document.category }}</span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="shell-card p-8">
                <p class="badge-soft">Formulaire</p>
                <h2 class="mt-4 text-2xl font-semibold">Envoyer un message</h2>
                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Nom</label>
                        <input v-model="form.name" type="text" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
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
