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
        <section class="shell-card p-6">
            <h3 class="text-lg font-semibold">Nouveau compte</h3>
            <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <input v-model="form.name" type="text" placeholder="Nom complet" class="rounded-2xl border-slate-200" required />
                <input v-model="form.email" type="email" placeholder="Email" class="rounded-2xl border-slate-200" required />
                <input v-model="form.title" type="text" placeholder="Fonction (optionnel)" class="rounded-2xl border-slate-200" />
                <input v-model="form.password" type="password" placeholder="Mot de passe" class="rounded-2xl border-slate-200" required />
                <select v-model="form.role" class="rounded-2xl border-slate-200">
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-2.5 font-semibold text-white" :disabled="form.processing">
                    Creer le compte
                </button>
            </form>
            <p v-if="Object.keys(form.errors).length" class="mt-3 text-sm text-rose-700">
                {{ Object.values(form.errors)[0] }}
            </p>
        </section>

        <section class="shell-card mt-6 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Nom</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Actif</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ user.name }}</p>
                            <p class="text-slate-500">{{ user.title }}</p>
                        </td>
                        <td class="px-5 py-4">{{ user.email }}</td>
                        <td class="px-5 py-4">
                            <select
                                v-model="user.role"
                                class="rounded-xl border-slate-200 text-sm"
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
                            <button v-if="user.id !== currentUserId" class="text-rose-700" @click="remove(user.id)">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AdminLayout>
</template>
