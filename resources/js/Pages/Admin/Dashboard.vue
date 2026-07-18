<script setup>
import MetricCard from '@/Components/MetricCard.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency, formatDateTime } from '@/composables/useFormatters';
import Chart from 'chart.js/auto';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    stats: Object,
    charts: Object,
    recent: Object,
});

const financeCanvas = ref(null);
const eventsCanvas = ref(null);
let financeChart;
let eventsChart;

onMounted(() => {
    financeChart = new Chart(financeCanvas.value, {
        type: 'line',
        data: {
            labels: props.charts.finance.labels,
            datasets: [
                {
                    label: 'Recettes',
                    data: props.charts.finance.income,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.12)',
                    tension: 0.35,
                },
                {
                    label: 'Depenses',
                    data: props.charts.finance.expense,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.10)',
                    tension: 0.35,
                },
            ],
        },
    });

    eventsChart = new Chart(eventsCanvas.value, {
        type: 'bar',
        data: {
            labels: props.charts.events.labels,
            datasets: [
                {
                    label: 'Inscriptions',
                    data: props.charts.events.registrations,
                    backgroundColor: '#c98b13',
                    borderRadius: 12,
                },
            ],
        },
    });
});

onBeforeUnmount(() => {
    financeChart?.destroy();
    eventsChart?.destroy();
});
</script>

<template>
    <AdminLayout title="Tableau de bord">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <MetricCard label="Membres actifs" :value="stats.members" />
            <MetricCard label="Clubs" :value="stats.clubs" />
            <MetricCard label="Inscriptions clubs en attente" :value="stats.pendingClubRegistrations" />
            <MetricCard label="Evenements a venir" :value="stats.upcomingEvents" />
            <MetricCard label="Inscriptions evenements en attente" :value="stats.pendingEventRegistrations" />
            <MetricCard label="Documents" :value="stats.documents" />
            <MetricCard label="Messages a traiter" :value="stats.messages" />
            <MetricCard label="Solde" :value="formatCurrency(stats.balance)" />
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <section class="shell-card p-6">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="badge-soft">Finance</p>
                        <h3 class="mt-3 text-2xl font-semibold">Evolution des flux</h3>
                    </div>
                    <div class="text-sm text-slate-500">
                        Recettes : {{ formatCurrency(stats.income) }}<br />
                        Depenses : {{ formatCurrency(stats.expense) }}
                    </div>
                </div>
                <canvas ref="financeCanvas" class="mt-6" />
            </section>

            <section class="shell-card p-6">
                <p class="badge-soft">Participation</p>
                <h3 class="mt-3 text-2xl font-semibold">Inscriptions par evenement</h3>
                <canvas ref="eventsCanvas" class="mt-6" />
            </section>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-3">
            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Dernieres transactions</h3>
                <div class="mt-5 space-y-4">
                    <article v-for="item in recent.transactions" :key="item.id" class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ item.description }}</p>
                            <span class="text-sm font-semibold" :class="item.type === 'income' ? 'text-emerald-700' : 'text-rose-700'">
                                {{ formatCurrency(item.amount) }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ item.category }} - {{ item.transaction_date }}</p>
                    </article>
                </div>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Prochains evenements</h3>
                <div class="mt-5 space-y-4">
                    <article v-for="item in recent.events" :key="item.id" class="rounded-2xl border border-slate-200 p-4">
                        <p class="font-medium text-slate-900">{{ item.name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ item.location }}</p>
                        <p class="mt-2 text-sm font-medium text-amber-700">{{ formatDateTime(item.starts_at) }}</p>
                    </article>
                </div>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Messages recents</h3>
                <div class="mt-5 space-y-4">
                    <article v-for="item in recent.messages" :key="item.id" class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ item.name }}</p>
                            <span class="badge-soft">{{ item.status }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ item.email }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ formatDateTime(item.created_at) }}</p>
                    </article>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
