<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import ModalFormRegistration from '@/Components/ModalFormRegistration.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination  from '@/Components/Pagination.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        required: true
    }
});

const users = computed(() => props.users.data);
const paginationData = computed(() => props.users);

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? ""),
    account_type = ref(usePage().props.account_type ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let usersUrl = computed(() => {
    let url = new URL(route('admin.users'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }

    if (account_type) {
        url.searchParams.append('account_type', account_type.value);
    }

    return url
});

watch(
    () => usersUrl.value,
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
    () => account_type.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
)


const showModalFormCreate = ref(false);
const showModalFormEdit = ref(false);
const showModalDelete = ref(false);

const createForm = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: null
});


const editForm = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: null
});


const deleteForm = useForm({
    id: null,
    name: ''
});

const openModalFormCreate = () => {
    createForm.name = '';
    createForm.email = '';
    createForm.password = '';
    createForm.password_confirmation = '';
    createForm.role = '';
    
    showModalFormCreate.value = true;
}

const openModalFormEdit = (user) => {
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    editForm.password_confirmation = '';
    editForm.role = user.role;

    showModalFormEdit.value = true;
}

const openDeleteModal = (task) => {
    deleteForm.id = task.id;
    deleteForm.name = task.name;

    showModalDelete.value = true;
}

const saveUser = () => {
    createForm.post(route('admin.register.store'), {
        onSuccess: () => {
            createForm.reset();
            showModalFormCreate.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
}

const updateUser = (id) => {
    if (editForm.processing || !id) return;

    editForm.put(route('admin.register.update', id), {
        onSuccess: () => {
            editForm.reset();
            showModalFormEdit.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
};

const deleteUser = (id) => {
    if (editForm.processing || !id) return;

    deleteForm.delete(route('admin.register.destroy', id), {
        onSuccess: () => {
            deleteForm.reset();
            showModalDelete.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    })
}


</script>

<template>
    <Head title="Users" />

    <AdminLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Users
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-8">
                <div class="flex items-center">
                    <div class="flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-green-500">
                            System Users List
                        </h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-green-700">
                            A list of all Users of the System.
                        </p>
                    </div>

                    <!-- <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <PrimaryButton
                            @click="openModalFormCreate(task)"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Add New User
                        </PrimaryButton>
                    </div> -->
                </div>

                <div class="flex flex-col justify-left gap-2 sm:flex-row mt-6">
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
                        v-model="account_type"
                       class="block rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 dark:ring-green-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:ml-5 sm:text-sm sm:leading-6"
                    >
                        <option value="">Filter by type</option>
                        <option value="2">System Admin</option>
                        <option value="1">Chief</option>
                        <option value="0">Officer</option>
                    </select>
                </div>

                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div
                            class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                        >

                        <div class="hidden sm:block">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative">
                                <table
                                    class="min-w-full divide-y divide-gray-300"
                                >
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                Name
                                            </th>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                Email
                                            </th>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                Account Type
                                            </th>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                Added At
                                            </th>
                                            <th
                                                scope="col"
                                                class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                            />
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white dark:bg-gray-800"
                                    >
                                        <tr v-for="user in users" :key="user.id" class="transition duration-300 hover:bg-gray-100 dark:hover:bg-black">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                {{ user.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                {{ user.email }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                <span v-if="user.role === 2">System Admin</span>
                                                <span v-if="user.role === 1">Chief</span>
                                                <span v-if="user.role === 0">Officer</span>
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 dark:text-white"
                                            >
                                                {{ user.created_at }}
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium space-x-2 sm:pr-6"
                                            >
                                                <button @click="openModalFormEdit(user)" :disabled="editForm.processing" class="text-blue-700 hover:text-blue-400" title="Edit Task">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="openDeleteModal(user)" :disabled="deleteForm.processing" class="text-red-700 hover:text-red-400" title="Delete Task">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>                                    
                            </div>
                            <Pagination 
                                :data="paginationData" 
                                :updatedPageNumber="updatedPageNumber"
                            /> 
                        </div>

                        <div class="block sm:hidden space-y-4 mt-4 px-4">
                            <div
                                v-for="user in users"
                                :key="user.id"
                                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4"
                            >
                                <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ user.name }}
                                </h3>
                                <span class="text-xs text-gray-500">{{ user.created_at }}</span>
                                </div>

                                <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span class="font-medium">Email:</span>
                                    <span class="text-right">{{ user.email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Account Type:</span>
                                    <span class="text-right">
                                    <span v-if="user.role === 2">System Admin</span>
                                    <span v-else-if="user.role === 1">Chief</span>
                                    <span v-else>Officer</span>
                                    </span>
                                </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <PrimaryButton
                                        class="flex-1 justify-center bg-blue-600 hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400"
                                        @click="openModalFormEdit(user)"
                                    >
                                        Edit
                                    </PrimaryButton>
                                    <DangerButton class="flex-1 justify-center" @click="openDeleteModal(user)">
                                        Delete
                                    </DangerButton>
                                </div>
                            </div>
                            <Pagination 
                                :data="paginationData" 
                                :updatedPageNumber="updatedPageNumber"
                                /> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <ModalFormRegistration :show="showModalFormCreate" @close="showModalFormCreate = false">
        <template #main>
            <form @submit.prevent="saveUser">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 dark:text-green-500"
                    >
                        New User Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                        Use this form to add new user.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-6 md:grid-cols-6">

                    <div class="col-span-3">
                        <InputLabel for="name" value="Name" />
                        <TextInput 
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createForm.name"
                            placeholder="Enter name"
                        />
                        <InputError class="mt-2" :message="createForm.errors.name" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="email" value="Email" />
                        <TextInput 
                            id="email"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createForm.email"
                            placeholder="Enter email"
                        />
                        <InputError class="mt-2" :message="createForm.errors.email" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="password" value="Password" />
                        <TextInput 
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="createForm.password"
                            placeholder="Enter password"
                        />
                        <InputError class="mt-2" :message="createForm.errors.password" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput 
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="createForm.password_confirmation"
                            placeholder="Enter confirm password"
                        />
                        <InputError class="mt-2" :message="createForm.errors.password_confirmation" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="role" value="Account Type" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                            The account type cannot be modified after registration.
                        </p>
                        <select
                            v-model="createForm.role"
                            id="role"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:text-sm"
                        >
                            <option value="">Select Account Type</option>
                            <option value="2">System Admin</option>
                            <option value="1">Chief</option>
                            <option value="0">Officer</option>                            
                        </select>
                        <InputError :message="createForm.errors.role"/> 
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="saveUser" :disabled="createForm.processing" class="btn btn-secondary">
                Add New User
            </PrimaryButton>
            <SecondaryButton @click="showModalFormCreate = false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalFormRegistration>

    <ModalFormRegistration :show="showModalFormEdit" @close="showModalFormEdit = false">
        <template #main>
            <form @submit.prevent="updateUser(editForm.id)">
                <div>
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 dark:text-green-500"
                    >
                        Update User Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-green-700">
                        Use this form to update existing user.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-6 md:grid-cols-6">

                    <div class="col-span-3">
                        <InputLabel for="update_name" value="Name" />
                        <TextInput 
                            id="update_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.name"
                            placeholder="Enter name"
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="update_email" value="Email" />
                        <TextInput 
                            id="update_email"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.email"
                            placeholder="Enter email"
                        />
                        <InputError class="mt-2" :message="editForm.errors.email" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="update_password" value="Password" />
                        <p class="text-sm text-gray-600 text-left dark:text-green-700">
                            leave blank to keep it unchanged.
                        </p>
                        <TextInput 
                            id="update_password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="editForm.password"
                            placeholder="Enter password"
                        />
                        <InputError class="mt-2" :message="editForm.errors.password" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="update_password_confirmation" value="Confirm Password" />
                        <p class="text-sm text-gray-600 text-left dark:text-green-700">
                            if the password is blank, keep this blank.
                        </p>
                        <TextInput 
                            id="update_password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="editForm.password_confirmation"
                            placeholder="Enter confirm password"
                        />
                        <InputError class="mt-2" :message="editForm.errors.password_confirmation" />
                    </div>
                    <div class="col-span-3">
                        <InputLabel for="update_role" value="Account Type" />
                        <select

                            v-model="editForm.role"
                            id="update_role"
                            class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-green-500 dark:bg-green-800 dark:text-white sm:text-sm"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
                            :class="{'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300': editForm.errors.role}"
                        >
                            <option value="">Select Account Type</option>
                            <option value="2">System Admin</option>
                            <option value="1">Chief</option>
                            <option value="0">Officer</option>                            
                        </select>
                        <InputError :message="editForm.errors.role"/> 
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="updateUser(editForm.id)" :disabled="editForm.processing" class="btn btn-secondary">
                Update Existing User
            </PrimaryButton>
            <SecondaryButton @click="showModalFormEdit = false" class="btn btn-secondary">
                Cancel
            </SecondaryButton>
        </template>
    </ModalFormRegistration>

    <Modal :show="showModalDelete" @close="showModalDelete = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6 w-[30rem]">
                <div class="flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold dark:text-gray-200">Delete User ?</h2>
                </div>

                <p class="mt-4 text-md text-gray-900 text-left dark:text-red-500">
                    Are you sure you want to delete user
                    <strong>{{ deleteForm.name }}</strong>
                </p>


                <p class="mt-4 text-sm text-gray-600 text-left dark:text-gray-300">
                    {{ deleteForm.name }} will be logged out.
                </p>
                <p class="text-sm text-gray-600 text-left dark:text-gray-300">
                    All the records and data related to {{ deleteForm.name }} will be also deleted.
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <DangerButton @click="deleteUser(deleteForm.id)" :disabled="deleteForm.processing">
                        Delete User
                    </DangerButton>
                    <SecondaryButton @click="showModalDelete = false" class="btn btn-secondary">
                        Don't Delete
                    </SecondaryButton>
                </div>
            </div>
        </template>
    </Modal>
</template>
