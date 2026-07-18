<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDateTime } from '@/composables/useFormatters';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    registrations: Array,
    statuses: Array,
});

const updateStatus = (registration) => {
    router.put(route('admin.event-registrations.update', registration.id), { status: registration.status }, { preserveScroll: true });
};

const remove = (id) => router.delete(route('admin.event-registrations.destroy', id));
</script>

<template>
    <AdminLayout title="Inscriptions evenements">
        <section class="shell-card overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Participant</th>
                        <th class="px-5 py-4">Evenement</th>
                        <th class="px-5 py-4">Classe</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="registration in registrations" :key="registration.id">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ registration.full_name }}</p>
                            <p class="text-slate-500">{{ registration.email }}</p>
                        </td>
                        <td class="px-5 py-4">{{ registration.event_name }}</td>
                        <td class="px-5 py-4">{{ registration.class_name }}</td>
                        <td class="px-5 py-4">
                            <select v-model="registration.status" class="rounded-xl border-slate-200 text-sm" @change="updateStatus(registration)">
                                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                            </select>
                        </td>
                        <td class="px-5 py-4">{{ formatDateTime(registration.created_at) }}</td>
                        <td class="px-5 py-4 text-right">
                            <button class="text-rose-700" @click="remove(registration.id)">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </AdminLayout>
</template>
