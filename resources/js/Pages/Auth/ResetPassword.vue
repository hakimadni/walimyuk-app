<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Kata Sandi - WalimYuk" />

        <div class="mb-8">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                <span>🔒</span> Perbarui Keamanan
            </div>
            <h2 class="mt-2 font-serif text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl dark:text-white">
                Buat Kata Sandi Baru
            </h2>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">
                Masukkan kata sandi baru yang aman untuk akun WalimYuk Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
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
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi Baru" class="text-xs font-semibold text-stone-700 dark:text-stone-300" />
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
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Ulangi Kata Sandi Baru" class="text-xs font-semibold text-stone-700 dark:text-stone-300" />
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full rounded-xl border-stone-200 pl-10 text-sm shadow-sm transition focus:border-emerald-600 focus:ring-emerald-600 dark:border-stone-700 dark:bg-stone-900"
                        v-model="form.password_confirmation"
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
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
                        Menyimpan Kata Sandi...
                    </span>
                    <span v-else>Simpan Kata Sandi Baru &rarr;</span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
