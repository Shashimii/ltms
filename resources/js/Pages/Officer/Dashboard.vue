<script setup>
import OfficerLayout from '@/Layouts/OfficerLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination  from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';
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
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
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
                <div class="w-full mb-8 grid lg:grid-cols-2 gap-4">
                    <StatCard>
                        <template #icon>
                            <div class="p-4 bg-green-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-green-800">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                </svg>
                            </div>
                        </template>
                        <template #count>
                            {{ completedTasks }}
                        </template>
                        <template #label>
                            Completed Tasks
                        </template>
                    </StatCard>
                    <StatCard>
                        <template #icon>
                            <div class="p-4 bg-red-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-red-800">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                </svg>
                            </div>
                        </template>
                        <template #count>
                            {{ pendingTasks }}
                        </template>
                        <template #label>
                            Pending Tasks
                        </template>
                    </StatCard>
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

                <div class="flex flex-col justify-left sm:flex-row mt-6 gap-2">
                    <div class="relative text-sm text-gray-800">
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
                            class="w-full block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                        />
                    </div>

                    <select
                        v-model="status_filter"
                       class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:ml-5 sm:text-sm sm:leading-6"
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
                            <div class="hidden sm:block">
                                <div
                                    class=" shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative"
                                >
                                    <!-- Table for medium and larger screens -->
                                    <div class="overflow-hidden w-full rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Officer</th>
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Duty</th>
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">ODTS Code</th>
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Assigned At</th>
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-800">
                                                <tr v-for="task in assignedTasks.data" :key="task.id">
                                                    <td class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-teal-200 text-teal-800 font-semibold px-2 py-1 rounded inline-block">
                                                            {{ task.officer.name }}
                                                        </p>
                                                    </td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-orange-200 text-orange-800 font-semibold px-2 py-1 rounded inline-block">
                                                            {{ task.task.name }}
                                                        </p>
                                                    </td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-gray-200 text-gray-800 font-semibold px-2 py-1 rounded inline-block">
                                                            {{ task.odts_code }}
                                                        </p>
                                                    </td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-pink-200 text-pink-800 font-semibold px-2 py-1 rounded inline-block">
                                                            {{ task.assigned_at }}
                                                        </p>
                                                    </td>
                                                    <td v-if="task.is_done" class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-green-200 text-green-800 font-semibold px-2 py-1 rounded inline-block">
                                                            Done
                                                        </p>
                                                    </td>
                                                    <td v-else class="px-3 py-4 text-sm text-gray-900">
                                                        <p class="bg-red-200 text-red-800 font-semibold px-2 py-1 rounded inline-block">
                                                            Not Done
                                                        </p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <Pagination 
                                    :data="assignedTasks" 
                                    :updatedPageNumber="updatedPageNumber"
                                />
                            </div>

                            <!-- Card style for small screens -->
                            <div class="px-4 overflow-hidden block sm:hidden space-y-6">
                                <div
                                    v-for="task in assignedTasks.data"
                                    :key="task.id"
                                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                            {{ task.task.name }}
                                        </h3>
                                        <span
                                            class="text-xs font-medium px-2.5 py-0.5 rounded-full"
                                            :class="task.is_done ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                        >
                                            {{ task.is_done ? 'Done' : 'Not Done' }}
                                        </span>
                                        </div>

                                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium">Officer</span>
                                            <span class="text-right">{{ task.officer.name }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium">ODTS Code</span>
                                            <span class="text-right">{{ task.odts_code }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium">Assigned At</span>
                                            <span class="text-right">{{ task.assigned_at }}</span>
                                        </div>
                                    </div>
                                    <!-- 
                                    <div class="mt-4">
                                        <PrimaryButton @click="openModalNotify(task)"  class="w-full bg-pink-600 hover:bg-pink-500 text-white text-center dark:text-white dark:bg-violet-900 dark:hover:bg-violet-700 flex justify-center items-center">Notify Chief</PrimaryButton>
                                    </div> -->
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
        </div>
    </OfficerLayout>

    <!-- <Modal :show="showModalNotify" @close="showModalNotify = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="w-full max-w-[30rem] sm:w-[30rem] p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center sm:justify-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Notify Chief?</h2>
                </div>

                <p class="mt-4 mb-4 text-sm sm:text-md text-gray-700 dark:text-gray-300">
                    <strong>{{ notifyForm.officer_name }}</strong>
                    is
                    <strong v-if="notifyForm.is_done">Undone</strong>
                    <strong v-else>Done</strong>
                    with
                    <strong>{{ notifyForm.task_name }}</strong>
                    assigned on
                    <strong>{{ notifyForm.assigned_at }}.</strong>
                </p>

                <h2 class="text-sm sm:text-md font-semibold text-gray-800 dark:text-gray-200">ODTS Code: {{ notifyForm.odts_code }}</h2>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 sm:gap-4">
                    <SecondaryButton @click="showModalNotify = false" class="w-full sm:w-auto">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="saveNotify(notifyForm)" :disabled="notifyForm.processing" class="w-full sm:w-auto">
                        Notify Chief
                    </PrimaryButton>
                </div>
            </div>
        </template>
    </Modal> -->

</template>