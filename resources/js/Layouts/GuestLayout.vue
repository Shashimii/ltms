<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="relative flex w-full min-h-screen flex-col items-center justify-center sm:min-h-[500px]">
            <!-- Logo -->
            <div class="absolute top-44 sm:top-14">
                <img src="/images/denrLogo.png" alt="Logo" class="w-36 h-auto object-cover rounded-full shadow-lg dark:drop-shadow-[0_4px_6px_rgba(144,238,144,0.5)]" />
            </div>

            <!-- Form Box -->
            <div
                class="sm:mt-32 w-full px-6 py-8 overflow-hidden sm:bg-gradient-to-tr from-green-300 to-green-500 sm:shadow-md sm:pt-16 sm:w-[30rem] sm:rounded-lg dark:bg-gray-800"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
