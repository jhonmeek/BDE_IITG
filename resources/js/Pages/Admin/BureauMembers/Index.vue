<script setup>
import InputError from '@/Components/InputError.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

defineProps({ members: Array });

const form = useForm({
    name: '',
    role_title: '',
    mandate_label: '',
    email: '',
    phone: '',
    bio: '',
    sort_order: 0,
    is_active: true,
    photo: null,
});

const submit = () => form.post(route('admin.bureau-members.index'));
const remove = (id) => router.delete(route('admin.bureau-members.destroy', id));
</script>

<template>
    <AdminLayout title="Membres du bureau">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card table-scroll">
                <table class="min-w-full text-sm">
                    <thead class="table-head">
                        <tr>
                            <th class="px-5 py-4">Nom</th>
                            <th class="px-5 py-4">Poste</th>
                            <th class="px-5 py-4">Mandat</th>
                            <th class="px-5 py-4">Statut</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="member in members" :key="member.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ member.name }}</td>
                            <td class="px-5 py-4">{{ member.role_title }}</td>
                            <td class="px-5 py-4">{{ member.mandate_label }}</td>
                            <td class="px-5 py-4">{{ member.is_active ? 'Actif' : 'Inactif' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.bureau-members.edit', member.id)" class="action-edit">Modifier</a>
                                <button class="action-delete ml-3" @click="remove(member.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-5 sm:p-6">
                <h3 class="text-lg font-semibold sm:text-xl">Ajouter un membre</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.name" type="text" class="input-shell" placeholder="Nom complet" />
                    <InputError :message="form.errors.name" />
                    <input v-model="form.role_title" type="text" class="input-shell" placeholder="Poste" />
                    <input v-model="form.mandate_label" type="text" class="input-shell" placeholder="Mandat" />
                    <input v-model="form.email" type="email" class="input-shell" placeholder="E-mail" />
                    <input v-model="form.phone" type="text" class="input-shell" placeholder="Téléphone" />
                    <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre d’affichage" />
                    <textarea v-model="form.bio" rows="4" class="input-shell" placeholder="Présentation" />
                    <input type="file" class="input-shell" @input="form.photo = $event.target.files[0]" />
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.is_active" type="checkbox" class="field-check" />
                        Membre actif
                    </label>
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
