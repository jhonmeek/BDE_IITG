<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-danger text-sm font-medium">
            {{ message }}
        </p>
    </div>
</template>
