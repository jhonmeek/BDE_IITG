<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmer le mot de passe" />

        <div class="mb-6">
            <p class="eyebrow">Zone sécurisée</p>
            <h2 class="section-title mt-2">Confirmer le mot de passe</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Cette section est protégée. Confirmez votre mot de passe pour
                continuer.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <PrimaryButton :disabled="form.processing"
                >
                    Confirmer
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
