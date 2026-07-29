<script setup>
defineProps({
    label: String,
    value: [String, Number],
    hint: String,
});
</script>

<template>
    <div class="shell-card p-5">
        <p class="text-sm font-medium text-slate-500">{{ label }}</p>
        <p class="text-brand mt-3 text-2xl font-semibold sm:text-3xl">{{ value }}</p>
        <p v-if="hint" class="mt-2 text-sm text-slate-500">{{ hint }}</p>
    </div>
</template>
