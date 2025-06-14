<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  logs: {
    type: Object,
    required: true
  }
})

const logs = computed(() => props.logs);
const selectedFilter = ref('today');

const filters = [
    { label: 'Today', value: 'today' },
    { label: 'This Week', value: 'week' },
    { label: 'This Month', value: 'month' }
];

const applyFilter = (filter) => {
    selectedFilter.value = filter;

    router.get(route('chief.log'), { filter }, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Logs" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Logs
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Log List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700">
                            Use the buttons below to select date range.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-4 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <li v-for="log in logs">
                            {{ log.activity }} -
                            {{ log.description }}
                        </li>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
            <div class="flex space-x-2 bg-white shadow-lg rounded-full px-4 py-2">
                <button
                    v-for="filter in filters"
                    :key="filter.value"
                    @click="applyFilter(filter.value)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium',
                        selectedFilter === filter.value
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-200 text-gray-800'
                    ]"
                >
                    {{ filter.label }}
                </button>
            </div>
        </div>
    </ChiefLayout>
</template>
