<script setup>
import InputError from '@/Components/InputError.vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/composables/useFormatters';

const props = defineProps({
    settings: Object,
    clubs: Array,
});

const form = useForm({
    last_name: '',
    first_name: '',
    email: '',
    phone: '',
    class_name: '',
    club_ids: [],
    notes: '',
});

const submit = () => form.post(route('clubs.register'));
</script>

<template>
    <SiteLayout title="Clubs">
        <section class="mb-10">
            <p class="badge-soft">Clubs</p>
            <h1 class="section-title mt-4">Les clubs educatifs, sportifs et communautaires</h1>
            <p class="section-copy mt-4">
                Inscrivez-vous a un ou plusieurs clubs pour participer a la vie du campus et developper vos competences.
            </p>
        </section>

        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="grid gap-5 md:grid-cols-2">
                <article v-for="club in clubs" :key="club.id" class="shell-card p-6">
                    <img
                        v-if="club.image_url"
                        :src="club.image_url"
                        :alt="club.name"
                        class="mb-4 h-20 w-20 rounded-[1.5rem] object-cover"
                    />
                    <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color: var(--bde-red);">{{ club.category }}</p>
                    <h2 class="mt-3 text-2xl font-semibold">{{ club.name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Responsable : {{ club.lead_name }}</p>
                    <p class="mt-4 text-sm leading-6 text-slate-600">{{ club.description }}</p>
                    <div class="mt-5 flex items-center justify-between text-sm text-slate-500">
                        <span>Budget : {{ formatCurrency(club.budget_allocated) }}</span>
                        <Link :href="route('clubs.show', club.slug)" class="font-semibold" style="color: var(--bde-red);">Details</Link>
                    </div>
                </article>
            </div>

            <div class="shell-card p-6 sm:p-8">
                <p class="badge-soft">Inscription</p>
                <h2 class="mt-4 text-2xl font-semibold">Rejoindre un ou plusieurs clubs</h2>
                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Nom</label>
                            <input v-model="form.last_name" type="text" class="input-shell" />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Prenom</label>
                            <input v-model="form.first_name" type="text" class="input-shell" />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Email</label>
                            <input v-model="form.email" type="email" class="input-shell" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Telephone</label>
                            <input v-model="form.phone" type="text" class="input-shell" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Classe</label>
                        <input v-model="form.class_name" type="text" class="input-shell" />
                        <InputError class="mt-2" :message="form.errors.class_name" />
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-700">Clubs souhaites</p>
                        <div class="mt-3 grid gap-3">
                            <label
                                v-for="club in clubs"
                                :key="club.id"
                                class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"
                            >
                                <input v-model="form.club_ids" :value="club.id" type="checkbox" class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                <span>
                                    <span class="block font-medium text-slate-900">{{ club.name }}</span>
                                    <span class="text-sm text-slate-500">{{ club.summary }}</span>
                                </span>
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.club_ids" />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Message complementaire</label>
                        <textarea v-model="form.notes" rows="4" class="input-shell" />
                    </div>

                    <button class="btn-primary w-full" :disabled="form.processing">Envoyer ma demande</button>
                </form>
            </div>
        </div>
    </SiteLayout>
</template>
