<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/composables/useFormatters';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Array,
    categories: Array,
});

const form = useForm({
    type: 'income',
    category: props.categories[0],
    amount: 0,
    description: '',
    transaction_date: '',
    notes: '',
    attachment: null,
});

const submit = () => form.post(route('admin.transactions.index'));
const remove = (id) => router.delete(route('admin.transactions.destroy', id));
</script>

<template>
    <AdminLayout title="Tresorerie">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Description</th>
                            <th class="px-5 py-4">Categorie</th>
                            <th class="px-5 py-4">Montant</th>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="transaction in transactions" :key="transaction.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ transaction.description }}</td>
                            <td class="px-5 py-4">{{ transaction.category }}</td>
                            <td class="px-5 py-4" :class="transaction.type === 'income' ? 'text-emerald-700' : 'text-rose-700'">
                                {{ formatCurrency(transaction.amount) }}
                            </td>
                            <td class="px-5 py-4">{{ transaction.transaction_date }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.transactions.edit', transaction.id)" class="text-amber-700">Modifier</a>
                                <button class="ml-3 text-rose-700" @click="remove(transaction.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Nouvelle transaction</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
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
                    <textarea v-model="form.notes" rows="4" class="input-shell" placeholder="Notes" />
                    <input type="file" class="input-shell" @input="form.attachment = $event.target.files[0]" />
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
