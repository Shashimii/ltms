<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import ModalForm from '@/Components/ModalForm.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Datepicker from '@/Components/Datepicker.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Pagination  from '@/Components/Pagination.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
  assignedTasks: {
    type: Object,
    required: true
  },
  officers: {
    type: Object,
    required: true
  },
  tasks: {
    type: Object,
    required: true
  }
})

const showModalFormCreate = ref(false);
const showModalFormEdit = ref(false);
const showModalDelete = ref(false);
const showModalDone = ref(false);
const showModalUndone = ref(false);

const createForm = useForm({
    id: null,
    officer_id: '',
    task_id: '',
    odts_code: '',
    assigned_at: '',
    is_done: false,
})


const editForm = useForm({
    id: null,
    officer_id: '',
    task_id: '',
    odts_code: '',
    assigned_at: '',
    is_done: false,
})

const deleteForm = useForm({
    id: null,
    officer_name: '',
    task_name: '',
    odts_code: '',
    assigned_at: '',
    is_done: false,
})

const doneForm = useForm({
    id: null,
    officer_name: '',
    task_name: '',
    odts_code: '',
    assigned_at: '',
    is_done: true,
})


const undoneForm = useForm({
    id: null,
    officer_name: '',
    task_name: '',
    odts_code: '',
    assigned_at: '',
    is_done: false,
})


// format the assigned_at date
const formatDate = (date) => {
    const [month, day, year] = date.split("-");
    const formattedDate = `${year}-${month}-${day}`;
    return formattedDate;
}

const openModalFormCreate = () => {
    editForm.id = '';
    editForm.officer = '';
    editForm.task = '';
    editForm.odts_code = '';
    editForm.assigned_at = '';
    editForm.is_done = '';

    showModalFormCreate.value = true;
}

const openModalFormEdit = (assignedTask) => {

    editForm.id = assignedTask.id;
    editForm.officer_id = assignedTask.officer.id;
    editForm.task_id = assignedTask.task.id;
    editForm.odts_code = assignedTask.odts_code;
    editForm.assigned_at = formatDate(assignedTask.assigned_at);
    editForm.is_done = assignedTask.is_done;

    showModalFormEdit.value = true;
}

const openDeleteModal = (assignedTask) => {
    deleteForm.id = assignedTask.id;
    deleteForm.officer = assignedTask.officer.name;
    deleteForm.task = assignedTask.task.name;
    deleteForm.odts_code = assignedTask.odts_code;
    deleteForm.assigned_at = assignedTask.assigned_at;
    deleteForm.is_done = assignedTask.is_done;

    showModalDelete.value = true;
}

const openDoneModal = (assignedTask) => {
    doneForm.id = assignedTask.id;
    doneForm.officer = assignedTask.officer.name;
    doneForm.task = assignedTask.task.name;
    doneForm.odts_code = assignedTask.odts_code;
    doneForm.assigned_at = assignedTask.assigned_at;
    
    showModalDone.value = true;
}

const openUndoneModal = (assignedTask) => {
    undoneForm.id = assignedTask.id;
    undoneForm.officer = assignedTask.officer.name;
    undoneForm.task = assignedTask.task.name;
    undoneForm.odts_code = assignedTask.odts_code;
    undoneForm.assigned_at = assignedTask.assigned_at;

    showModalUndone.value = true;
}

const saveAssignedTask = () => {
    createForm.post(route('chief.assigned-task.store'), {
        onSuccess: () => {
            createForm.reset();
            showModalFormCreate.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
}

const updateAssignedTask = (task) => {
    if (editForm.processing || !task) return;

    editForm.put(route('chief.assigned-task.update', {assigned_task: task}), {
        onSuccess: () => {
            editForm.reset();
            showModalFormEdit.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
};


const saveDelete = (task) => {
    if (editForm.processing || !task) return;

    deleteForm.delete(route('chief.assigned-task.destroy', task), {
        onSuccess: () => {
            deleteForm.reset();
            showModalDelete.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    })
}

const saveDone = (task) => {
    if (doneForm.processing || !task) return;

    doneForm.put(route('chief.assigned-task.done', task), {
        onSuccess: () => {
            doneForm.reset();
            showModalDone.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    })
}

const saveUndone = (task) => {
    if (undoneForm.processing || !task) return;

    undoneForm.put(route('chief.assigned-task.undone', task), {
        onSuccess: () => {
            undoneForm.reset();
            showModalUndone.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    })
}

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? ""),
    officer_id = ref(usePage().props.officer_id ?? ""),
    status_filter = ref(usePage().props.status_filter ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let assignedDutiesUrl = computed(() => {
    let url = new URL(route('chief.assigned-task.index'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }

    if (officer_id) {
        url.searchParams.append('officer_id', officer_id.value);
    }

    if (status_filter) {
        url.searchParams.append('status', status_filter.value);
    }

    return url
});

watch(
    () => assignedDutiesUrl.value,
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
    () => officer_id.value,
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


</script>

<template>
    <Head title="Assigned Tasks" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Assigned Tasks
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="sm:flex sm:items-center ">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            Assigned Tasks List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-700">
                            A list of all Assigned Task.
                        </p>
                    </div>

                    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <PrimaryButton
                            @click="openModalFormCreate(task)"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Assign Task
                        </PrimaryButton>
                    </div>
                </div>

                <div class="flex flex-col justify-left sm:flex-row mt-6 gap-2">
                    <div class="relative text-sm text-gray-800 w-full sm:max-w-xs">
                        <div
                            class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500 dark:text-green-100"
                        >
                            <MagnifyingGlass />
                        </div>

                        <input
                            v-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search task, odts..."
                            id="search"
                            class="w-full block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white dark:placeholder-white sm:text-sm sm:leading-6"
                        />
                    </div>

                    <select
                        v-model="officer_id"
                        class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:ml-5 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:text-sm sm:leading-6"
                    >
                        <option value="">Filter by officer</option>
                        <option
                            :value="item.id"
                            :key="item.id"
                            v-for="item in officers.data"
                        >
                            {{ item.name }}
                        </option>
                    </select>

                    <select
                        v-model="status_filter"
                        class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:ml-5 sm:text-sm sm:leading-6"
                    >
                        <option value="">Filter by status</option>
                        <option value="1">Done</option>
                        <option value="0">Not Done</option>
                    </select>
                </div>

                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">   
                            <div class="hidden sm:block">
                                <div v-if="assignedTasks.data.length != 0">
                                    <div class=" shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative">
                                        <!-- Table for medium and larger screens -->
                                        <div class="overflow-hidden w-full rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-300">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Officer Name</th>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white max-w-xs">Task Name</th>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">ODTS Code</th>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Assignment Date</th>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-800">
                                                    <tr v-for="task in assignedTasks.data" :key="task.id" class="transition duration-300 hover:bg-gray-100 dark:hover:bg-black">
                                                        <td class="px-3 py-4 text-sm text-gray-900">
                                                            <p class="bg-teal-200 text-teal-800 font-semibold px-2 py-1 rounded inline-block">
                                                                {{ task.officer.name }}
                                                            </p>
                                                        </td>
                                                        <td class="px-3 py-4 text-sm text-gray-900 max-w-xs">
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
                                                        <td class="px-3 py-4 flex items-center space-x-2">
                                                            <div class="flex items-center" v-if="!task.is_done">
                                                                <button @click="openDoneModal(task)" :disabled="doneForm.processing" class="text-green-700 hover:text-green-400" title="Mark as Done">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            <div class="flex items-center" v-if="task.is_done">
                                                                <button @click="openUndoneModal(task)" :disabled="undoneForm.processing" class="text-violet-700 hover:text-violet-400" title="Mark as Undone">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            <button @click="openModalFormEdit(task)" :disabled="editForm.processing" class="text-blue-700 hover:text-blue-400" title="Edit Task">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                                </svg>
                                                            </button>
                                                            <button @click="openDeleteModal(task)" :disabled="deleteForm.processing" class="text-red-700 hover:text-red-400" title="Delete Task">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
                                    :data="assignedTasks" 
                                    :updatedPageNumber="updatedPageNumber"
                                />
                            </div>

                            <!-- Card style for small screens -->
                            <div class="px-4 overflow-hidden block sm:hidden">
                                <div v-if="assignedTasks.data != 0" class="space-y-6">
                                    <div
                                        v-for="task in assignedTasks.data"
                                        :key="task.id"
                                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4"
                                    >
                                        <div class="mb-8 flex-col flex-start space-y-2">
                                            <span
                                                class="text-xs font-medium px-2.5 py-0.5 rounded-full"
                                                :class="task.is_done ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                            >
                                                {{ task.is_done ? 'Done' : 'Not Done' }}
                                            </span>
                                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                {{ task.task.name }}
                                            </h3>
                                        </div>

                                        <div class="space-y-2 text-sm dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-700">Officer Name</span>
                                                <span class="font-semibold text-lg">{{ task.officer.name }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-700">ODTS Code</span>
                                                <span class="font-semibold text-lg">{{ task.odts_code }}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-700">Assigned At</span>
                                                    <span class="font-semibold text-lg">{{ task.assigned_at }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid-cols-3 space-y-2">
                                            <div class="flex items-center" v-if="!task.is_done">
                                                <button @click="openDoneModal(task)" :disabled="doneForm.processing" class="w-full p-2 bg-green-600 hover:bg-green-700 text-white flex justify-center items-center gap-2 rounded" title="Mark as Done">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                                    </svg>
                                                    Mark as Done
                                                </button>
                                            </div>
                                            <div class="flex items-center" v-if="task.is_done">
                                                <button @click="openUndoneModal(task)" :disabled="undoneForm.processing" class="w-full p-2 bg-violet-600 hover:bg-violet-700 text-white flex justify-center items-center gap-2 rounded" title="Mark as Undone">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                    Mark as Undone
                                                </button>
                                            </div>
                                            <div>
                                                <button @click="openModalFormEdit(task)" :disabled="editForm.processing" class="w-full p-2 bg-blue-600 hover:bg-blue-700 text-white flex justify-center items-center gap-2 rounded" title="Edit Task">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                    Edit Task
                                                </button>
                                            </div>
                                            <div>
                                                <button @click="openDeleteModal(task)" :disabled="deleteForm.processing" class="w-full p-2 bg-red-600 hover:bg-red-700 text-white flex justify-center items-center gap-2 rounded">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    Delete Task
                                                </button>
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
                                    :data="assignedTasks" 
                                    :updatedPageNumber="updatedPageNumber"
                                /> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ChiefLayout>

    <ModalForm :show="showModalFormCreate" @close="showModalFormCreate = false" :closeable="true">
        <template #main>
            <form @submit.prevent="saveAssignedTask">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 dark:text-green-500"
                    >
                        Task Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                        Use this form to assign new task.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-6 gap-6">

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="officer_id" value="Officer" />
                        <select
                            v-model="createForm.officer_id"
                            id="officer_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:text-sm"
                            :class="{'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300': createForm.errors.officer_id}"
                        >
                            <option value="">
                                Select a Officer
                            </option>
                            <option 
                                v-for="item in officers.data" 
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="createForm.errors.officer_id"/> 
                    </div>

                    
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="task_id" value="Task" />
                        <select
                            v-model="createForm.task_id"
                            id="task_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:text-sm"
                            :class="{'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300': createForm.errors.task_id}"
                        >
                            <option value=""> 
                                Select a Task
                            </option>
                            <option 
                                v-for="item in tasks.data" 
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="createForm.errors.task_id"/> 
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="odts_code" value="Odts Code" />
                        <TextInput 
                            id="odts_code"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createForm.odts_code"
                            placeholder="Enter Odts Code"
                        />
                        <InputError class="mt-2" :message="createForm.errors.odts_code" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-green-500">
                            Assign Date
                        </label>
                        <Datepicker v-model="createForm.assigned_at" />
                        <InputError class="mt-2" :message="createForm.errors.assigned_at" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="is_done" class="flex items-center">
                            <Checkbox id="is_done" name="is_done" v-model:checked="createForm.is_done" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-green-500">
                                Task is Already Done.
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="saveAssignedTask" :disabled="createForm.processing" class="btn btn-secondary">
                Assign Task
            </PrimaryButton>
            <SecondaryButton @click="showModalFormCreate= false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalForm>

    <ModalForm :show="showModalFormEdit" @close="showModalFormEdit = false" :closeable="true">
        <template #main>
            <form @submit.prevent="updateAssignedTask(editForm.id)">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900"
                    >
                        Task Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Use this form to edit existing task.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-6 gap-6">

                    <div class="col-span-6 sm:col-span-3">
                        <label
                        for="officer_id"
                        class="block text-sm font-medium text-gray-700"
                        >
                            Officer
                        </label>
                        <p class="text-sm text-gray-600 text-left">
                            Assigning officer can't be changed after assignment.
                        </p>
                        <select
                            disabled
                            v-model="editForm.officer_id"
                            id="officer_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300': editForm.errors.officer_id}"
                        >
                            <option value="">
                                Select a Officer
                            </option>
                            <option 
                                v-for="item in officers.data" 
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="editForm.errors.officer_id"/> 
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label
                        for="task_id"
                        class="block text-sm font-medium text-gray-700"
                        >
                            Task
                        </label>
                        <p class="text-sm text-gray-600 text-left">
                            Task can't be changed once assigned.
                        </p>
                        <select
                            disabled
                            v-model="editForm.task_id"
                            id="task_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300': editForm.errors.task_id}"
                        >
                            <option value=""> 
                                Select a Task
                            </option>
                            <option 
                                v-for="item in tasks.data" 
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="editForm.errors.task_id"/> 
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="odts_code" value="Odts Code" />
                        <TextInput 
                            id="odts_code"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.odts_code"
                            placeholder="Enter Odts Code"
                        />
                        <InputError class="mt-2" :message="editForm.errors.odts_code" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Assign Date
                        </label>
                        <Datepicker v-model="editForm.assigned_at" />
                        <InputError class="mt-2" :message="editForm.errors.assigned_at" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <p class="text-sm text-gray-600 text-left">
                            Task status can't be changed after assignment.
                        </p>
                        <label for="is_done" class="flex items-center">
                            <Checkbox id="is_done" name="is_done" v-model:checked="editForm.is_done" disabled />
                            <span class="ms-2 text-sm text-gray-600">
                                Task is Already Done.
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="updateAssignedTask(editForm.id)" :disabled="editForm.processing" class="btn btn-secondary">
                Edit Existing Task
            </PrimaryButton>
            <SecondaryButton @click="showModalFormEdit= false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalForm>

    <Modal :show="showModalDelete" @close="showModalDelete = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6">
                <div class="mb-4 flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold">Delete Assigned Task ?</h2>
                </div>

                <div class="flex-col space-y-2">
                    <p class="text-sm font-semibold text-orange-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>

                        <p>Task Name</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-orange-400">
                        {{ deleteForm.task }}
                    </p>

                    <p class="text-sm font-semibold text-teal-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>

                        <p>Assigned to Officer</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-teal-400">
                        {{ deleteForm.officer }}
                    </p>

                    <p class="text-sm font-semibold text-gray-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                        </svg>

                        <p>ODTS Code</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-gray-400">
                        {{ deleteForm.odts_code }}
                    </p>

                    <p class="text-sm font-semibold text-pink-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>

                        <p>Assignment Date</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-pink-400">
                        {{ deleteForm.assigned_at }}
                    </p>

                    <p class="text-sm font-semibold text-blue-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>

                        <p>Task Status</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-blue-400">
                        {{ deleteForm.is_done ? 'Done' : 'Not Done' }}
                    </p>
                </div>

                <div class="mt-6 flex-col space-y-2">
                    <div class="flex space-x-2 font-semibold text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <p>
                            Are you sure you want to delete this task ?
                        </p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <DangerButton @click="saveDelete(deleteForm.id)" :disabled="deleteForm.processing">
                            Delete Assigned Task
                        </DangerButton>
                        <SecondaryButton @click="showModalDelete = false" class="btn btn-secondary">
                            Don't Delete
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </template>
    </Modal>

    <Modal :show="showModalDone" @close="showModalDone = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6">
                <div class="mb-4 flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold">Mark as Done ?</h2>
                </div>

                <div class="flex-col space-y-2">
                    <p class="text-sm font-semibold text-orange-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>

                        <p>Task Name</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-orange-400">
                        {{ doneForm.task }}
                    </p>

                    <p class="text-sm font-semibold text-teal-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>

                        <p>Assigned to Officer</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-teal-400">
                        {{ doneForm.officer }}
                    </p>

                    <p class="text-sm font-semibold text-gray-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                        </svg>

                        <p>ODTS Code</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-gray-400">
                        {{ doneForm.odts_code }}
                    </p>

                    <p class="text-sm font-semibold text-pink-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>

                        <p>Assignment Date</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-pink-400">
                        {{ doneForm.assigned_at }}
                    </p>
                </div>

                <div class="mt-6 flex-col space-y-2">
                    <div class="flex space-x-2 font-semibold text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p>
                            Are you sure you want to mark this task as done ?
                        </p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <PrimaryButton @click="saveDone(doneForm.id)" :disabled="doneForm.processing">
                            Mark as Done
                        </PrimaryButton>
                        <SecondaryButton @click="showModalDone = false" class="btn btn-secondary">
                            Cancel
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </template>
    </Modal>

    <Modal :show="showModalUndone" @close="showModalUndone = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6">
                <div class="mb-4 flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold">Mark as Undone ?</h2>
                </div>

                <div class="flex-col space-y-2">
                    <p class="text-sm font-semibold text-orange-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>

                        <p>Task Name</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-orange-400">
                        {{ undoneForm.task }}
                    </p>

                    <p class="text-sm font-semibold text-teal-600 flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>

                        <p>Assigned to Officer</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-teal-400">
                        {{ undoneForm.officer }}
                    </p>

                    <p class="text-sm font-semibold text-gray-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                        </svg>

                        <p>ODTS Code</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-gray-400">
                        {{ undoneForm.odts_code }}
                    </p>

                    <p class="text-sm font-semibold text-pink-600 flex items-center space-x-1"> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>

                        <p>Assignment Date</p>
                    </p>
                    <p class="text-xl font-semibold ps-4 ms-3 border-l-2 border-pink-400">
                        {{ undoneForm.assigned_at }}
                    </p>
                </div>
                <div class="mt-6 flex-col space-y-2">
                    <div class="flex space-x-2 font-semibold text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p>
                            Are you sure you want to mark this task as undone ?
                        </p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <PrimaryButton @click="saveUndone(undoneForm.id)" :disabled="undoneForm.processing">
                            Mark as Undone
                        </PrimaryButton>
                        <SecondaryButton @click="showModalUndone = false" class="btn btn-secondary">
                            Cancel
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </template>
    </Modal>
</template>
