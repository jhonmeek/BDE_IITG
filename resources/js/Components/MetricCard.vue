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
        <p class="mt-3 text-3xl font-semibold" style="color: var(--bde-blue);">{{ value }}</p>
        <p v-if="hint" class="mt-2 text-sm text-slate-500">{{ hint }}</p>
    </div>
</template>
