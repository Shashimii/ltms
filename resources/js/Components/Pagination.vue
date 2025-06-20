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
    <!-- try logging in the template directly -->
    <!-- {{ data }} -->

    <div class="max-w-7xl mx-auto py-6">
        <div class="max-w-none mx-auto">
            <div class="bg-white overflow-hidden shadow dark:bg-gray-800 sm:rounded-lg">
                <div
                    class="bg-white px-4 py-3 flex items-center justify-between border-gray-200 dark:bg-gray-800 sm:px-6"
                >
                    <div class="flex-1 flex justify-between sm:hidden" />
                    <div
                        class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm text-gray-700 dark:text-green-600">
                                Showing
                                <!-- space -->
                                <span class="font-medium">{{ data.meta.from }}</span>
                                <!-- space -->
                                to
                                <!-- space -->
                                <span class="font-medium">{{ data.meta.to }}</span>
                                <!-- space -->
                                of
                                <!-- space -->
                                <span class="font-medium">{{ data.meta.total }}</span>
                                <!-- space -->
                                results
                            </p>
                        </div>
                        <div>
                            <nav
                                class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination"
                            >
                                <button
                                @click.prevent="updatedPageNumber(link)"
                                v-for="(link, index) in data.meta.links"
                                :key="index"
                                :disabled="!link.url"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition duration-150 ease-in-out"
                                :class="{
                                    'z-10 bg-green-600 text-white border-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600':
                                    link.active,
                                    'bg-white text-gray-600 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700':
                                    !link.active,
                                    'cursor-not-allowed opacity-50': !link.url
                                }"
                                >
                                <span v-html="link.label"></span>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>