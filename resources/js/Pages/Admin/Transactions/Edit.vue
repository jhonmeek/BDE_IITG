<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
    categories: Array,
});

const form = useForm({
    type: props.transaction.type,
    category: props.transaction.category,
    amount: props.transaction.amount,
    description: props.transaction.description,
    transaction_date: props.transaction.transaction_date,
    notes: props.transaction.notes ?? '',
    attachment: null,
});

const submit = () => form.post(route('admin.transactions.update', props.transaction.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier une transaction">
        <section class="shell-card max-w-3xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <select v-model="form.type" class="input-shell">
                    <option value="income">Recette</option>
                    <option value="expense">Depense</option>
                </select>
                <select v-model="form.category" class="input-shell">
                    <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                </select>
                <input v-model="form.amount" type="number" class="input-shell" placeholder="Montant" />
                <input v-model="form.description" type="text" class="input-shell" placeholder="Description" />
                <input v-model="form.transaction_date" type="date" class="input-shell" />
                <textarea v-model="form.notes" rows="5" class="input-shell" placeholder="Notes" />
                <input type="file" class="input-shell" @input="form.attachment = $event.target.files[0]" />
                <button class="btn-primary" :disabled="form.processing">Mettre a jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
