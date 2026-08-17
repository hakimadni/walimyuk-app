<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Akun - WalimYuk" />

        <div class="mb-8">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                <span>🔐</span> Akses Dashboard Pengantin
            </div>
            <h2 class="mt-2 font-serif text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl dark:text-white">
                Selamat Datang Kembali
            </h2>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">
                Masuk untuk mengelola undangan, pantau jatah tamu RSVP, dan kalkulasi katering walimah Anda.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Alamat Email" class="text-xs font-semibold text-stone-700 dark:text-stone-300" />
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

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Kata Sandi" class="text-xs font-semibold text-stone-700 dark:text-stone-300" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400"
                    >
                        Lupa kata sandi?
                    </Link>
                </div>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full rounded-xl border-stone-200 pl-10 text-sm shadow-sm transition focus:border-emerald-600 focus:ring-emerald-600 dark:border-stone-700 dark:bg-stone-900"
                        v-model="form.password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex cursor-pointer items-center select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-emerald-700 focus:ring-emerald-600" />
                    <span class="ms-2.5 text-xs text-stone-600 dark:text-stone-400">Ingat sesi saya</span>
                </label>
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
                        Memproses...
                    </span>
                    <span v-else>Masuk Sekarang &rarr;</span>
                </PrimaryButton>
            </div>

            <div class="mt-8 rounded-xl border border-stone-200/80 bg-stone-50/60 p-4 text-center text-xs text-stone-600 dark:border-stone-800 dark:bg-stone-900/50 dark:text-stone-400">
                Belum memiliki akun pernikahan?
                <Link
                    :href="route('register')"
                    class="font-semibold text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400"
                >
                    Daftar WalimYuk Gratis
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
