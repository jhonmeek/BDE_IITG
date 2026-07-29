<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatDateTime } from '@/composables/useFormatters';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    messages: Object,
    statuses: Array,
});

const updateStatus = (message) => {
    router.put(route('admin.contact-messages.update', message.id), { status: message.status }, { preserveScroll: true });
};

const remove = (id) => router.delete(route('admin.contact-messages.destroy', id));
</script>

<template>
    <AdminLayout title="Messages de contact">
        <section class="space-y-4">
            <article v-for="message in messages.data" :key="message.id" class="shell-card p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold sm:text-xl">{{ message.name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ message.email }}</p>
                        <p class="text-accent mt-2 text-sm font-medium">{{ message.subject || 'Sans sujet' }}</p>
                        <p class="mt-4 text-sm leading-7 text-slate-600">{{ message.message }}</p>
                        <p class="mt-4 text-xs uppercase tracking-[0.16em] text-slate-400">{{ formatDateTime(message.created_at) }}</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <select v-model="message.status" class="field-inline" @change="updateStatus(message)">
                            <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                        </select>
                        <button class="action-delete text-sm" @click="remove(message.id)">Supprimer</button>
                    </div>
                </div>
            </article>

            <div class="shell-card">
                <Pagination :links="messages.links" />
            </div>
        </section>
    </AdminLayout>
</template>
