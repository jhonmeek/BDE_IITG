<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatDateTime } from '@/composables/useFormatters';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    registrations: Object,
    statuses: Array,
});

const updateStatus = (registration) => {
    router.put(route('admin.club-registrations.update', registration.id), { status: registration.status }, { preserveScroll: true });
};

const remove = (id) => router.delete(route('admin.club-registrations.destroy', id));
</script>

<template>
    <AdminLayout title="Inscriptions clubs">
        <section class="shell-card table-scroll">
            <table class="min-w-full text-sm">
                <thead class="table-head">
                    <tr>
                        <th class="px-5 py-4">Étudiant</th>
                        <th class="px-5 py-4">Club</th>
                        <th class="px-5 py-4">Classe</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="registration in registrations.data" :key="registration.id">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">{{ registration.first_name }} {{ registration.last_name }}</p>
                            <p class="text-slate-500">{{ registration.email }}</p>
                        </td>
                        <td class="px-5 py-4">{{ registration.club_name }}</td>
                        <td class="px-5 py-4">{{ registration.class_name }}</td>
                        <td class="px-5 py-4">
                            <select v-model="registration.status" class="field-inline" @change="updateStatus(registration)">
                                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                            </select>
                        </td>
                        <td class="px-5 py-4">{{ formatDateTime(registration.created_at) }}</td>
                        <td class="px-5 py-4 text-right">
                            <button class="action-delete" @click="remove(registration.id)">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :links="registrations.links" />
        </section>
    </AdminLayout>
</template>
