<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="admin-shell flex min-h-screen items-center justify-center px-4 py-8 sm:py-10">
        <div class="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="brand-surface card-pad flex flex-col justify-between overflow-hidden rounded-xl shadow-sm">
                <div>
                    <p class="eyebrow-on-dark">BDE IITG</p>
                    <h1 class="page-title mt-5 text-white">Connexion à l’espace administratif</h1>
                    <p class="lead mt-4 max-w-lg text-slate-300">
                        Accédez à la gestion centralisée des clubs, événements, inscriptions, documents et de la trésorerie du bureau.
                    </p>
                </div>

                <div class="mt-8">
                    <Link :href="route('home')" class="btn-secondary !border-white/20 !bg-white/10 !text-white">
                        Retour au site public
                    </Link>
                </div>
            </div>

            <div class="shell-card card-pad">
                <slot />
            </div>
        </div>
    </div>
</template>
