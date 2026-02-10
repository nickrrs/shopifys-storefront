<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Store, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';
import ToastContainer from '@/components/ToastContainer.vue';
import { useAuthRegister } from '@/composables/useAuthRegister';
import { useToast } from '@/composables/useToast';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const { submitting, register } = useAuthRegister();
const { toasts, showError, showSuccess } = useToast();

const submit = async () => {
    try {
        await register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        showSuccess('Conta criada com sucesso. Redirecionando para o dashboard...');
        router.visit('/dashboard');
    } catch (error) {
        const message =
            error instanceof Error
                ? error.message
                : 'Erro inesperado ao tentar criar conta.';

        showError(message);
    } finally {
        form.reset('password', 'password_confirmation');
    }
};
</script>

<template>
    <Head title="register" />

    <div
        class="min-h-screen bg-[#f1f2f3] flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans text-gray-900"
    >
        <ToastContainer :toasts="toasts" />

        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
            <div class="flex items-center justify-center gap-2">
                <div
                    class="inline-flex items-center justify-center p-2 bg-[#008060] rounded-xl shadow-lg"
                >
                    <Store class="h-4 w-4 text-white" />
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    shopify storefront
                </h2>
            </div>

            <p class="mt-2 text-sm text-gray-600">
                create your account to manage stores and products.
            </p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div
                class="bg-white py-10 px-6 shadow-sm sm:rounded-xl sm:px-12 border border-gray-200/60"
            >
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            name
                        </label>
                        <div class="mt-1">
                            <input
                                id="name"
                                v-model="form.name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                                class="block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-[#008060] focus:outline-none focus:ring-1 focus:ring-[#008060] sm:text-sm transition-colors"
                                placeholder="your name"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            e-mail
                        </label>
                        <div class="mt-1">
                            <input
                                id="email"
                                v-model="form.email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-[#008060] focus:outline-none focus:ring-1 focus:ring-[#008060] sm:text-sm transition-colors"
                                placeholder="you@email.com"
                            />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label
                                for="password"
                                class="block text-sm font-medium text-gray-700"
                            >
                                password
                            </label>
                        </div>
                        <div class="relative mt-1">
                            <input
                                id="password"
                                v-model="form.password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                class="block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2.5 pr-10 shadow-sm focus:border-[#008060] focus:outline-none focus:ring-1 focus:ring-[#008060] sm:text-sm transition-colors"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff
                                    v-if="showPassword"
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                                <Eye
                                    v-else
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            confirm password
                        </label>
                        <div class="mt-1">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                name="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                class="block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2.5 shadow-sm focus:border-[#008060] focus:outline-none focus:ring-1 focus:ring-[#008060] sm:text-sm transition-colors"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="flex w-full justify-center items-center cursor-pointer rounded-lg bg-[#008060] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#006e52] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#008060] disabled:opacity-70 disabled:cursor-not-allowed transition-all duration-200"
                        >
                            <svg
                                v-if="submitting"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            <template v-else>
                                create account
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </template>
                        </button>
                    </div>
                </form>

                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200" />
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white px-2 text-gray-500">
                                already have an account?
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <Link
                            href="/"
                            class="font-medium text-[#008060] hover:text-[#006e52] hover:underline transition-all"
                        >
                            go back to login
                        </Link>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center text-xs text-gray-500">
                <p>&copy; 2026 - personal project</p>
            </div>
        </div>
    </div>
</template>
