<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const props = defineProps({
    histories: {
        type: Object,
        required: true,
    }
})

const activityColor = (activity) => {
    switch (activity) {
        case 'Assigned':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white'; // Info/Action
        case 'Edited':
            return 'bg-orange-300 text-orange-800 dark:bg-orange-600 dark:text-white';
        case 'Deleted':
            return 'bg-red-300 text-red-800 dark:bg-red-600 dark:text-white'; 
        case 'Done_Notify':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-white'; // Notification of done
        case 'Done_Confirmation':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-600 dark:text-white'; // Final confirmation
        case 'Not_Done_Notify':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-white'; // Warning
        case 'Not_Done_Confirmation':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-600 dark:text-white'; // Still not done
        case 'Done_Notify_Cancel':
            return 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-white'; // Neutral
        case 'Not_Done_Notify_Cancel':
            return 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-white'; // Error/Cancel
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white'; // Fallback
    }
}

const formattedHistoryMessage = (msg) => {
    const officer = `<span class="font-semibold text-green-600 dark:text-green-300 bg-green-100 dark:bg-green-900 px-1 rounded">${msg.officer.name}</span>`; 
    const task = `<span class="font-semibold text-violet-700 dark:text-violet-200 bg-violet-100 dark:bg-violet-900 px-1 rounded">${msg.task.name}</span>`;    

    switch (msg.activity) {
        case 'Assigned':
            return `Legal Chief assigned the task ${task} to Officer ${officer}.`;

        case 'Done':
            return `Officer ${officer} is done with ${task}.`;

        default:
            return `Activity logged by Legal Chief regarding task ${task}.`;
    }
}

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? ""),
    rangeFilter = ref(usePage().props.rangeFilter ?? ""),
    activity_filter = ref(usePage().props.status_filter ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let logsUrl = computed(() => {
    let url = new URL(route('chief.history'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }

    if (rangeFilter) {
        url.searchParams.append('range', rangeFilter.value);
    }

    if (activity_filter) {
        url.searchParams.append('activity', activity_filter.value);
    }

    return url
});

watch(
    () => logsUrl.value,
    (updatedUrl) => {
        router.visit(updatedUrl, {
            preserveScroll: true,
            preserveState: true,
            replace: true
        })
    }
)

watch(
    () => search.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
)

watch(
    () => activity_filter.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
)

watch(
    () => rangeFilter.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
)

</script>

<template>
    <Head title="History" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                History
            </h2>
        </template>
        
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto ">
                        <h1 class="text-xl font-semibold text-gray-900">
                            History List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700">
                            List of recent happenings.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col justify-left sm:flex-row mt-6 gap-2">
                    <div class="relative text-sm text-gray-800 col-span-3">
                        <div
                            class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500"
                        >
                            <MagnifyingGlass />
                        </div>

                        <input
                            v-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search odts code..."
                            id="search"
                            class="w-full block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                        />
                    </div>

                    <select
                        v-model="activity_filter"
                        class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:ml-5 sm:text-sm sm:leading-6"
                    >
                        <option value="">Filter by activity</option>
                        <option value="Assigned">Assigned</option>
                        <option value="Done">Done</option>
                        <option value="Undone">Undone</option>
                        <option value="Edited">Edited</option>
                        <option value="Deleted">Deleted</option>
                    </select>

                    <select
                        v-model="rangeFilter"
                        class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:ml-5 sm:text-sm sm:leading-6"
                    >
                        <option value="">All</option>
                        <option value="Today">Today</option>
                        <option value="Month">This Month</option>
                    </select>
                </div>

                <div v-if="histories.data.length != 0">
                    <div
                        v-for="history in histories.data"
                        class="mt-4 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-4 rounded-lg bg-white shadow dark:bg-gray-800 text-base text-gray-900 dark:text-gray-100 leading-relaxed border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between align-center">
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ history.created_at }}
                                </p>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    Odts Code: {{ history.odts_code }} 
                                </p>
                            </div>

                            <p>
                                <span :class="`${activityColor(history.activity)} px-2 py-1 rounded-md font-semibold text-sm inline-block mb-2`">
                                    {{ history.activity }}
                                </span>
                                <br />
                                <span v-html="formattedHistoryMessage(history)"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="mt-4 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-green-800">
                        <div class="p-4 rounded-lg bg-white shadow dark:bg-green-800 text-base text-green-900 dark:text-green-100 leading-relaxed border border-green-200 dark:border-green-700">
                            <div class="mt-6 flex flex-col items-center justify-center text-green-700 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-16">
                                <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 
                                        3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                <p class="text-2xl italic">No results found.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination 
                    :data="histories" 
                    :updatedPageNumber="updatedPageNumber"
                /> 
            </div>
        </div>
    </ChiefLayout>
</template>