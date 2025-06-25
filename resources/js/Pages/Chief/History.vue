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
            return 'px-2 font-semibold bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white rounded'; // Assign
        case 'Edited':
            return 'px-2 font-semibold bg-orange-300 text-orange-800 dark:bg-orange-600 dark:text-white rounded'; // Edited
        case 'Deleted':
            return 'px-2 font-semibold bg-red-300 text-red-800 dark:bg-red-600 dark:text-white rounded'; // Deleted
        case 'Done':
            return 'px-2 font-semibold bg-green-100 text-green-800 dark:bg-green-600 dark:text-white rounded'; // Done
        case 'Undone':
            return 'px-2 font-semibold bg-pink-300 text-pink-800 dark:bg-pink-600 dark:text-white rounded'; // Undone
        default:
            return 'px-2 font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white rounded'; // Catch
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
                        class="mt-4 overflow-hidden bg-white shadow-sm rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-4 rounded-lg bg-white shadow dark:bg-gray-800 text-base text-gray-900 dark:text-gray-100 leading-relaxed border border-gray-200 dark:border-gray-700">
                            <div v-if="history.activity === 'Assigned'" class="flex-col space-y-4">
                                <div class="font-semibold text-sm text-gray-600 flex items-center justify-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p>{{ history.created_at }}</p>
                                </div>
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Assigned</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Edited'" class="flex-col space-y-4">
                                <div class="font-semibold text-sm text-gray-600 flex items-center justify-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p>{{ history.created_at }}</p>
                                </div>
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex-col space-y-2">
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <p>Legal Chief Edited</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                                {{ history.task.name }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                            </svg>
                                            <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                                {{ history.officer.name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <p>From:</p>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>
                                            <p>ODTS Code:</p>
                                            <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                                {{ history.old_odts_code }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                            </svg>
                                            <p>Assignment Date:</p>
                                            <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                                {{ history.old_assigned_at }}
                                            </span>
                                        </div>  
                                    </div>
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <p>To:</p>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>
                                            <p>ODTS Code:</p>
                                            <span class="px-2 font-semibold bg-emerald-200 text-emerald-800 rounded">
                                                {{ history.odts_code }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                            </svg>
                                            <p>Assignment Date:</p>
                                            <span class="px-2 font-semibold bg-emerald-200 text-emerald-800 rounded">
                                                {{ history.assigned_at }}
                                            </span> 
                                        </div> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Deleted'" class="flex-col space-y-4">
                                <div class="font-semibold text-sm text-gray-600 flex items-center justify-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p>{{ history.created_at }}</p>
                                </div>
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Deleted</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Done'" class="flex-col space-y-4">
                                <div class="font-semibold text-sm text-gray-600 flex items-center justify-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p>{{ history.created_at }}</p>
                                </div>
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Marked as Done</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span>  
                                    </div>                                  
                                </div>
                            </div>
                            <div v-if="history.activity === 'Undone'" class="flex-col space-y-4">
                                <div class="font-semibold text-sm text-gray-600 flex items-center justify-start space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p>{{ history.created_at }}</p>
                                </div>
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Marked as Undone</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
                                </div>
                            </div>
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