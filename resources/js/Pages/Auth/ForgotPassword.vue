<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi - WalimYuk" />

        <div class="mb-8">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                <span>🔑</span> Pemulihan Akun
            </div>
            <h2 class="mt-2 font-serif text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl dark:text-white">
                Lupa Kata Sandi?
            </h2>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">
                Masukkan alamat email yang terdaftar pada akun WalimYuk Anda. Kami akan mengirimkan tautan reset kata sandi secara instan.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Alamat Email Terdaftar" class="text-xs font-semibold text-stone-700 dark:text-stone-300" />
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full rounded-xl border-stone-200 pl-10 text-sm shadow-sm transition focus:border-emerald-600 focus:ring-emerald-600 dark:border-stone-700 dark:bg-stone-900"
                        v-model="form.email"
                        placeholder="contoh: fulan@walimyuk.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div class="pt-2">
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
                        Mengirim Tautan...
                    </span>
                    <span v-else>Kirim Tautan Reset Kata Sandi &rarr;</span>
                </PrimaryButton>
            </div>

            <div class="mt-8 rounded-xl border border-stone-200/80 bg-stone-50/60 p-4 text-center text-xs text-stone-600 dark:border-stone-800 dark:bg-stone-900/50 dark:text-stone-400">
                Ingat kata sandi Anda?
                <Link
                    :href="route('login')"
                    class="font-semibold text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400"
                >
                    Kembali ke Halaman Masuk
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
