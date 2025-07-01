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
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

defineProps({
    tasks: {
        type: Object,
        required: true
    }
});

const showModalFormCreate = ref(false);
const showModalFormEdit = ref(false);
const showModalDelete = ref(false);

const createForm = useForm({
    id: null,
    name: ''
});


const editForm = useForm({
    id: null,
    name: ''
});


const deleteForm = useForm({
    id: null,
    name: ''
});

const openModalFormCreate = () => {
    createForm.name = '';
    showModalFormCreate.value = true;
}

const openModalFormEdit = (task) => {
    editForm.id = task.id;
    editForm.name = task.name;

    showModalFormEdit.value = true;
}

const openDeleteModal = (task) => {
    deleteForm.id = task.id;
    deleteForm.name = task.name;

    showModalDelete.value = true;
}

const saveTask = () => {
    createForm.post(route('chief.task.store'), {
        onSuccess: () => {
            createForm.reset();
            showModalFormCreate.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
}

const updateTask = (task) => {
    if (editForm.processing || !task) return;

    editForm.put(route('chief.task.update', {task: task}), {
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

    deleteForm.delete(route('chief.task.destroy', task), {
        onSuccess: () => {
            deleteForm.reset();
            showModalDelete.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    })
}

//searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let url = computed(() => {
    let url = new URL(route('chief.task.index'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }


    return url
});

watch(
    () => url.value,
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


</script>

<template>
    <Head title="Task List" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Task List
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-8">
                <div class="flex items-center">
                    <div class="flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            Tasks List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-700">
                            A list of all Registered Tasks.
                        </p>
                    </div>

                    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <PrimaryButton
                            @click="openModalFormCreate(task)"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Add Task
                        </PrimaryButton>
                    </div>
                </div>

                <div class="flex flex-col justify-left sm:flex-row mt-6">
                    <div class="relative text-sm text-gray-800 col-span-3">
                        <div
                            class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500 dark:text-green-100"
                        >
                            <MagnifyingGlass />
                        </div>

                        <input
                            v-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search..."
                            id="search"
                            class="w-full block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white dark:placeholder-white sm:text-sm sm:leading-6"
                        />
                    </div>
                </div>

                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div
                            class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                        >
                            <div class="hidden sm:block">
                                <div
                                    class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative"
                                >
                                    <table
                                        class="min-w-full divide-y divide-gray-300"
                                    >
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    scope="col"
                                                    class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                                                >
                                                    Task Name
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                                />
                                            </tr>
                                        </thead>
                                        <tbody
                                            v-if="tasks.data.length != 0"
                                            class="divide-y divide-gray-200 bg-white dark:bg-gray-800"
                                        >
                                            <tr v-for="task in tasks.data" :key="task.id" class="transition duration-300 hover:bg-gray-100 dark:hover:bg-black">
                                                <td
                                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 dark:text-white"
                                                >
                                                    {{ task.name }}
                                                </td>

                                                <td
                                                    class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium space-x-2 sm:pr-6"
                                                >
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
                                    :data="tasks" 
                                    :updatedPageNumber="updatedPageNumber"
                                /> 
                            </div>
                            <div class="block sm:hidden space-y-4 px-4">
                                <div
                                    v-for="task in tasks.data"
                                    :key="task.id"
                                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4"
                                >
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ task.name }}
                                    </h3>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <PrimaryButton
                                        class="flex-1 justify-center bg-blue-600 hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400"
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
                                :data="tasks" 
                                :updatedPageNumber="updatedPageNumber"
                                /> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ChiefLayout>

    <ModalForm :show="showModalFormCreate" @close="showModalFormCreate = false">
        <template #main>
            <form @submit.prevent="saveTask">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 dark:text-green-500"
                    >
                        Task Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                        Use this form to add new task.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-6 gap-6">

                    <div class="col-span-6">
                        <InputLabel for="task_name" value="Task Name" />
                        <TextInput 
                            id="task_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createForm.name"
                            placeholder="Enter Task Name"
                        />
                        <InputError class="mt-2" :message="createForm.errors.name" />
                    </div>

                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="saveTask" :disabled="createForm.processing" class="btn btn-secondary">
                Register Task
            </PrimaryButton>
            <SecondaryButton @click="showModalFormCreate= false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalForm>

    <ModalForm :show="showModalFormEdit" @close="showModalFormEdit = false">
        <template #main>
            <form @submit.prevent="updateTask(editForm.id)">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 dark:text-green-500"
                    >
                        Task Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                        Use this form to edit existing task.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-6 gap-6">

                    <div class="col-span-6">
                        <InputLabel for="task_name" value="Task Name" />
                        <p class="text-sm text-gray-600 text-left dark:text-green-700">
                            modifying the task name will not affect the logs.
                        </p>
                        <TextInput 
                            id="task_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.name"
                            placeholder="Enter Task Name"
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="updateTask(editForm.id)" :disabled="editForm.processing" class="btn btn-secondary">
                Apply Changes
            </PrimaryButton>
            <SecondaryButton @click="showModalFormEdit= false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalForm>

    <Modal :show="showModalDelete" @close="showModalDelete = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6">
                <div class="flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold dark:text-green-500">Delete Task ?</h2>
                </div>

                <p class="mt-4 text-md text-gray-600 text-left dark:text-white">
                    Are you sure you want to delete the task 
                    <strong class="text-black dark:text-green-500">{{ deleteForm.name }}</strong>
                    ? This will also remove all related records and assigned tasks.
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <DangerButton @click="saveDelete(deleteForm.id)" :disabled="deleteForm.processing">
                        Delete Task
                    </DangerButton>
                    <SecondaryButton @click="showModalDelete = false" class="btn btn-secondary">
                        Don't Delete
                    </SecondaryButton>
                </div>
            </div>
        </template>
    </Modal>
</template>
