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
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Assigned Tasks List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700">
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
                            class="w-full block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                        />
                    </div>

                    <select
                        v-model="officer_id"
                        class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 sm:ml-5 sm:text-sm sm:leading-6"
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
                                                    <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-800">
                                                <tr v-for="task in assignedTasks.data" :key="task.id">
                                                    <td class="px-3 py-4 text-sm text-gray-900">{{ task.officer.name }}</td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">{{ task.task.name }}</td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">{{ task.odts_code }}</td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">{{ task.assigned_at }}</td>
                                                    <td class="px-3 py-4 text-sm text-gray-900">{{ task.is_done ? 'Done' : 'Not Done' }}</td>
                                                    <td class="px-3 py-4 space-x-2">
                                                        <PrimaryButton @click="openModalFormEdit(task)">Edit</PrimaryButton>
                                                        <DangerButton @click="openDeleteModal(task)">Delete</DangerButton>
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
                                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4 group transition hover:ring-2 hover:ring-green-500"
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

                                    <div class="mt-4 flex gap-2">
                                        <PrimaryButton
                                            class="flex-1 justify-center bg-indigo-600 hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                                            @click="openModalFormEdit(task)"
                                        >
                                            Edit
                                        </PrimaryButton>
                                        <DangerButton class="flex-1 justify-center" @click="openDeleteModal(task)">
                                            Delete
                                        </DangerButton>
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
                        class="text-lg leading-6 font-medium text-gray-900"
                    >
                        Task Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Use this form to assign new task.
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
                        <select
                            v-model="createForm.officer_id"
                            id="officer_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
                        <label
                        for="task_id"
                        class="block text-sm font-medium text-gray-700"
                        >
                            Task
                        </label>
                        <select
                            v-model="createForm.task_id"
                            id="task_id"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
                        <label
                            class="block text-sm font-medium text-gray-700"
                            >Assign Date</label
                        >
                        <Datepicker v-model="createForm.assigned_at" />
                        <InputError class="mt-2" :message="createForm.errors.assigned_at" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="is_done" class="flex items-center">
                            <Checkbox id="is_done" name="is_done" v-model:checked="createForm.is_done" />
                            <span class="ms-2 text-sm text-gray-600">
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
                        <label for="is_done" class="flex items-center">
                            <Checkbox id="is_done" name="is_done" v-model:checked="editForm.is_done" />
                            <span class="ms-2 text-sm text-gray-600">
                                Task is Already Done.
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-yellow-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm text-yellow-600 text-left">
                    All existing notifies related to the task will be removed
                </p>
            </div>
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
            <div class="p-6 w-[30rem]">
                <div class="flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold">Delete Assigned Task ?</h2>
                </div>

                <p class="mt-4 text-md text-gray-600 text-justify">
                    Are you sure you want to delete
                    <strong>{{ deleteForm.task }}</strong> assigned to
                    <strong>{{ deleteForm.officer }}</strong> with ODTS code
                    <strong>{{ deleteForm.odts_code }}</strong> assigned on
                    <strong>{{ deleteForm.assigned_at }}</strong> ?
                    Status: <strong>{{ deleteForm.is_done ? 'Done' : 'Not Done' }}</strong>
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <DangerButton @click="saveDelete(deleteForm.id)" :disabled="deleteForm.processing">
                        Delete Assigned Task
                    </DangerButton>
                    <SecondaryButton @click="showModalDelete = false" class="btn btn-secondary">
                        Don't Delete
                    </SecondaryButton>
                </div>
            </div>
        </template>
    </Modal>
</template>
