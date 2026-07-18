<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="shell-card flex flex-col justify-between overflow-hidden bg-slate-950 p-10 text-white">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.32em] text-amber-300">BDE IITG</p>
                    <h1 class="mt-6 text-4xl font-semibold text-white">Connexion a l espace administratif</h1>
                    <p class="mt-4 max-w-lg text-base leading-7 text-slate-300">
                        Accedez a la gestion centralisee des clubs, evenements, inscriptions, documents et de la tresorerie du bureau.
                    </p>
                </div>

                <div class="mt-8">
                    <Link :href="route('home')" class="btn-secondary !border-white/20 !bg-white/10 !text-white">
                        Retour au site public
                    </Link>
                </div>
            </div>

            <div class="shell-card p-8 sm:p-10">
                <slot />
            </div>
        </div>
    </div>
</template>
