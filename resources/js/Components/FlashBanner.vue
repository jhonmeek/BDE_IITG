<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const flash = computed(() => page.props.flash ?? {});
const type = computed(() => {
    if (flash.value.success) return 'success';
    if (flash.value.error) return 'error';
    return null;
});
</script>

<template>
    <div
        v-if="type"
        class="page-enter mb-6 rounded-lg border px-4 py-3 text-sm font-medium"
        role="status"
        :class="
            type === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border-rose-200 bg-rose-50 text-rose-800'
        "
    >
        {{ flash.success || flash.error }}
    </div>
</template>
