<script setup>
import OfficerLayout from '@/Layouts/OfficerLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination  from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount} from 'vue';

const props = defineProps({
    assignedTasks: {
        type: Object,
        required: true
    },

    assignedTaskCount: {
        type: Object,
        required: true
    },

    requests: {
        type: Object,
        required: true
    }
})

const requests = computed(() => props.requests.data);

// counters
const completedTasks = computed(() => {
    return props.assignedTaskCount.data.filter(task => task.is_done).length;
});

const pendingTasks = computed(() => {
    return props.assignedTaskCount.data.filter(task => !task.is_done).length;
});

const requestCount = computed(() => {
    return requests.value.length;
});

// page reload instead of polling
// const refreshData = () => {
//     router.reload({
//         preserveScroll: true,
//         preserveState: true,
//     });
// };

// let refreshInterval = null;

// onMounted(() => {
//     refreshInterval = setInterval(() => {
//         refreshData();
//     }, 10000);
// });

// onBeforeUnmount(() => {
//   clearInterval(refreshInterval);
// });

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? ""),
    status_filter = ref(usePage().props.status_filter ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let assignedTasksUrl = computed(() => {
    let url = new URL(route('officer.dashboard'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }

    if (status_filter) {
        url.searchParams.append('status', status_filter.value);
    }

    return url
});

watch(
    () => assignedTasksUrl.value,
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
    () => status_filter.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
)

// Modal
const notifyForm = useForm({
    id: null,
    officer_id: null,
    task_id: null,
    officer_name: '',
    task_name: '',
    is_done: '',
    assigned_at: '',
    odts_code: '',
});

const showModalNotify = ref(false);
const openModalNotify = (notify) => {
    notifyForm.id = notify.id
    notifyForm.officer_id = notify.officer.id;
    notifyForm.task_id = notify.task.id;
    notifyForm.officer_name = notify.officer.name;
    notifyForm.task_name = notify.task.name;
    notifyForm.is_done = notify.is_done;
    notifyForm.assigned_at = notify.assigned_at;
    notifyForm.odts_code = notify.odts_code;
    showModalNotify.value = true;
}

const saveNotify = (notifyForm) => {
    notifyForm.put(route('officer.notification.update', { notification: notifyForm.id }), {
        onSuccess: () => {
            notifyForm.reset();
            showModalNotify.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
}

// Routes
const notificationRoute = () => {
    router.visit(route('officer.notification.index'))
}


</script>

<template>
    <Head title="Dashboard" />

    <OfficerLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-green-500"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            Assigned Task Status
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-300">
                            This section displays all relevant counts related to assigned tasks.
                        </p>
                    </div>
                </div>
                <div class="w-full mb-8 grid grid-cols-2 gap-4">
                    <div class="bg-green-500 text-white p-4 border-gray-300 rounded shadow flex justify-between">
                        <p>Total Completed Tasks: </p>
                        <p>{{ completedTasks }}</p>
                    </div>
                    <div class="bg-red-500 text-white p-4 border-gray-300 rounded shadow flex justify-between">
                        <p>Total Pending Tasks: </p>
                        <p>{{ pendingTasks }}</p>
                    </div>
                </div>

                <div class="mb-4 sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            Notify Pending
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-300">
                            Here you can see how many notify on pedning you’ve sent to the chief.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-teal-200 via-teal-300 to-teal-400 text-teal-900 font-semibold text-lg p-4 border border-teal-300 rounded shadow flex justify-between items-center space-x-4">
                    <p class="text-sm sm:text-base">
                        You have: <span class="text-red-600">{{ requestCount }}</span> Pending Notify
                    </p>
                    <PrimaryButton
                        @click="notificationRoute"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                    >
                        View Notify Pending
                    </PrimaryButton>
                </div>

                <div class="mt-8 sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            Assigned Tasks List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-300">
                            This is a list of tasks that have been assigned to you by the chief.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col justify-left sm:flex-row mt-6">
                    <div class="relative text-sm text-gray-800 col-span-3">
                        <div
                            class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500 dark:text-green-400"
                        >
                            <MagnifyingGlass />
                        </div>

                        <input
                            v-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search task, odts..."
                            id="search"
                            class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-green-300"
                        />
                    </div>

                    <select
                        v-model="status_filter"
                        class="block rounded-lg border-0 py-2 ml-5 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:text-sm sm:leading-6"
                    >
                        <option value="">Filter by status</option>
                        <option value="1">Done</option>
                        <option value="0">Not Done</option>
                    </select>
                </div>

                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div
                            class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                        >
                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-300"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                            >
                                                Officer
                                            </th>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                            >
                                                Duty
                                            </th>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                            >
                                                Odts Code
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Assigned At
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Status
                                            </th>
                                            <th
                                                scope="col"
                                                class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                            />
                                        </tr>
                                    </thead>
                                    <tbody
                                        v-if="assignedTasks.data.length != 0"
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr v-for="task in assignedTasks.data" :key="task.id">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ task.officer.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ task.task.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ task.odts_code }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900"
                                            >
                                                {{ task.assigned_at }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900"
                                            >
                                                {{ task.is_done ? 'Done' : 'Not Done' }}
                                            </td>

                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium space-x-2 sm:pr-6"
                                            >
                                                <PrimaryButton @click="openModalNotify(task)" class="bg-pink-600 hover:bg-pink-500">
                                                    Notify Chief
                                                </PrimaryButton>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr>
                                            <td colspan="100%" class="py-12">
                                                <div class="flex flex-col items-center justify-center text-green-700 dark:text-green-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                        class="size-16 mb-4">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 
                                                            3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    <p class="text-2xl italic text-gray-600 dark:text-gray-300">No results found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination 
                                :data="assignedTasks" 
                                :updatedPageNumber="updatedPageNumber"
                            /> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OfficerLayout>

    <Modal :show="showModalNotify" @close="showModalNotify = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6 w-[30rem]">
                <div class="flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    

                    <h2 class="text-lg font-semibold">
                        Notify Chief ?
                    </h2>
                </div>

                <p class="mt-4 mb-4 text-md text-gray-900 text-left">
                    <strong>{{ notifyForm.officer_name }}</strong>
                    is
                    <strong v-if="notifyForm.is_done">Undone</strong>
                    <strong v-else>Done</strong>
                    with
                    <strong>{{ notifyForm.task_name }}</strong>
                    assigned on
                    <strong>{{ notifyForm.assigned_at }}.</strong>
                </p>

                <h2 class="text-md font-semibold">Odts Code: {{ notifyForm.odts_code }}</h2>

                <div class="mt-6 flex justify-end gap-4">
                    <PrimaryButton @click="saveNotify(notifyForm)" :disabled="notifyForm.processing">
                        Notify Chief
                    </PrimaryButton>
                    <SecondaryButton @click="showModalNotify = false" class="btn btn-secondary">
                        Cancel
                    </SecondaryButton>
                </div>
            </div>
        </template>
    </Modal>
</template>
