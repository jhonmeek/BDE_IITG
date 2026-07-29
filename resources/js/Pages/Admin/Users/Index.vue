<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    roles: Array,
    currentUserId: Number,
});

const form = useForm({ name: '', email: '', title: '', password: '', role: 'membre_bde' });

const submit = () => form.post(route('admin.users.store'), { preserveScroll: true, onSuccess: () => form.reset() });

const updateUser = (user) => {
    router.put(
        route('admin.users.update', user.id),
        { name: user.name, email: user.email, title: user.title, role: user.role, is_active: user.is_active },
        { preserveScroll: true },
    );
};

const remove = (id) => router.delete(route('admin.users.destroy', id), { preserveScroll: true });
</script>

<template>
    <AdminLayout title="Comptes BDE">
        <section class="shell-card p-5 sm:p-6">
            <h3 class="text-lg font-semibold">Nouveau compte</h3>
            <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <input v-model="form.name" type="text" placeholder="Nom complet" class="field-inline w-full" required />
                <input v-model="form.email" type="email" placeholder="E-mail" class="field-inline w-full" required />
                <input v-model="form.title" type="text" placeholder="Fonction (optionnel)" class="field-inline w-full" />
                <input v-model="form.password" type="password" placeholder="Mot de passe" class="field-inline w-full" required />
                <select v-model="form.role" class="field-inline w-full">
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing">
                    Créer le compte
                </button>
            </form>
            <p v-if="Object.keys(form.errors).length" class="text-danger mt-3 text-sm font-medium">
                {{ Object.values(form.errors)[0] }}
            </p>
        </section>

        <section class="shell-card table-scroll mt-6">
            <table class="min-w-full text-sm">
                <thead class="table-head">
                    <tr>
                        <th class="px-5 py-4">Nom</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Rôle</th>
                        <th class="px-5 py-4">Actif</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ user.name }}</p>
                            <p class="text-slate-500">{{ user.title }}</p>
                        </td>
                        <td class="px-5 py-4">{{ user.email }}</td>
                        <td class="px-5 py-4">
                            <select
                                v-model="user.role"
                                class="field-inline"
                                :disabled="user.id === currentUserId"
                                @change="updateUser(user)"
                            >
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </td>
                        <td class="px-5 py-4">
                            <input
                                v-model="user.is_active"
                                type="checkbox"
                                :disabled="user.id === currentUserId"
                                @change="updateUser(user)"
                            />
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button v-if="user.id !== currentUserId" class="action-delete" @click="remove(user.id)">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AdminLayout>
</template>
