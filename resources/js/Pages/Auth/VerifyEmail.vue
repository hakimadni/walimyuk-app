<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email - WalimYuk" />

        <div class="mb-8">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                <span>✉️</span> Verifikasi Akun
            </div>
            <h2 class="mt-2 font-serif text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl dark:text-white">
                Verifikasi Alamat Email Anda
            </h2>
            <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-400">
                Terima kasih telah bergabung dengan WalimYuk! Silakan periksa inbox email Anda dan klik tautan verifikasi yang kami kirimkan.
            </p>
        </div>

        <div
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
            v-if="verificationLinkSent"
        >
            Tautan verifikasi baru telah berhasil dikirimkan ke alamat email yang Anda daftarkan.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <PrimaryButton
                class="w-full justify-center rounded-xl bg-gradient-to-r from-emerald-800 to-emerald-700 py-3 text-sm font-semibold tracking-wide text-white shadow-md shadow-emerald-900/10 transition hover:from-emerald-750 hover:to-emerald-650 focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Mengirim Email...
                </span>
                <span v-else>Kirim Ulang Email Verifikasi &rarr;</span>
            </PrimaryButton>

            <div class="flex items-center justify-center pt-2">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs font-semibold text-stone-500 underline transition hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200"
                >
                    Keluar dari Akun (Log Out)
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
