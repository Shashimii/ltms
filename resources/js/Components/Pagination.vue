<script setup>
import { usePage, router } from "@inertiajs/vue3";

defineProps({
    data: {
        type: Object,
        required: true // makes it required
    },

    updatedPageNumber: {
        type: Function,
        required: true
    }
});

</script>

<template>
    <div class="max-w-7xl mx-auto sm:px-0 py-6 rounded-lg">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <!-- Pagination container -->
            <div class="px-4 py-4 border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Info text -->
                <div class="text-sm text-gray-700 dark:text-gray-300 text-center sm:text-left">
                    Showing
                    <span class="font-medium">{{ data.meta.from }}</span>
                    to
                    <span class="font-medium">{{ data.meta.to }}</span>
                    of
                    <span class="font-medium">{{ data.meta.total }}</span>
                    results
                </div>

                <!-- Pagination buttons -->
                <nav
                    class="flex flex-wrap justify-center sm:justify-end gap-2"
                    aria-label="Pagination"
                >
                    <button
                        v-for="(link, index) in data.meta.links"
                        :key="index"
                        @click.prevent="updatedPageNumber(link)"
                        :disabled="!link.url"
                        class="px-3 py-2 text-sm font-medium rounded-md border transition-all duration-150 ease-in-out"
                        :class="{
                            'bg-green-600 text-white border-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600': link.active,
                            'bg-white text-gray-600 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700': !link.active,
                            'cursor-not-allowed opacity-50': !link.url
                        }"
                    >
                        <span v-html="link.label"></span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

