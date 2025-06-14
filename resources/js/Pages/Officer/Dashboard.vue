<script setup>
import OfficerLayout from '@/Layouts/OfficerLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination  from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
  assignedTasks: {
    type: Object,
    required: true
  },

  assignedTaskCount: {
    type: Object,
    required: true
  }
})


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

    console.log(notifyForm.data());
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


</script>

<template>
    <Head title="Dashboard" />

    <OfficerLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="w-full mb-8 grid grid-cols-3 gap-4">
                        <div class="bg-green-500 text-white p-4 border-gray-300 rounded shadow flex justify-between">
                            <p>Total Completed Tasks: </p>
                            <p>{{ completedTasks }}</p>
                        </div>
                        <div class="bg-red-500 text-white p-4 border-gray-300 rounded shadow flex justify-between">
                            <p>Total Pending Tasks: </p>
                            <p>{{ pendingTasks }}</p>
                        </div>
                    </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Assigned Tasks List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700">
                            A list of all Assigned Task.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col justify-left sm:flex-row mt-6">
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
                            placeholder="Search task, odts..."
                            id="search"
                            class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
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
                                                <PrimaryButton @click="openModalNotify(task)">
                                                    Notify Chief
                                                </PrimaryButton>
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
