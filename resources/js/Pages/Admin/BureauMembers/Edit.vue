<script setup>
import InputError from '@/Components/InputError.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ member: Object });

const form = useForm({
    name: props.member.name,
    role_title: props.member.role_title,
    mandate_label: props.member.mandate_label ?? '',
    email: props.member.email ?? '',
    phone: props.member.phone ?? '',
    bio: props.member.bio ?? '',
    sort_order: props.member.sort_order ?? 0,
    is_active: props.member.is_active,
    photo: null,
});

const submit = () => form.post(route('admin.bureau-members.update', props.member.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier un membre">
        <section class="shell-card max-w-3xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.name" type="text" class="input-shell" placeholder="Nom complet" />
                <InputError :message="form.errors.name" />
                <input v-model="form.role_title" type="text" class="input-shell" placeholder="Poste" />
                <input v-model="form.mandate_label" type="text" class="input-shell" placeholder="Mandat" />
                <input v-model="form.email" type="email" class="input-shell" placeholder="Email" />
                <input v-model="form.phone" type="text" class="input-shell" placeholder="Telephone" />
                <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre d affichage" />
                <textarea v-model="form.bio" rows="5" class="input-shell" placeholder="Presentation" />
                <input type="file" class="input-shell" @input="form.photo = $event.target.files[0]" />
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                    Membre actif
                </label>
                <button class="btn-primary" :disabled="form.processing">Mettre a jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
