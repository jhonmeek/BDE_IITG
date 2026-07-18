<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <nav v-if="links && links.length > 3" class="flex flex-wrap gap-1 border-t border-slate-100 px-5 py-4">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-xl px-3 py-1.5 text-sm"
                :class="link.active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                v-html="link.label"
            />
            <span v-else class="rounded-xl px-3 py-1.5 text-sm text-slate-300" v-html="link.label" />
        </template>
    </nav>
</template>
