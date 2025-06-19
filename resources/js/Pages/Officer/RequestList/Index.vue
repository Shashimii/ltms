<script setup>
import OfficerLayout from '@/Layouts/OfficerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    requests: {
        type: Object,
        required: true
    }
});

const requests = computed(() => props.requests);

// page reload instead of polling
const refreshData = () => {
    router.reload({
        preserveScroll: true,
        preserveState: true,
    });
};

let refreshInterval = null;

onMounted(() => {
    refreshInterval = setInterval(() => {
        refreshData();
    }, 10000);
});

const cancelForm = useForm({
    id: null,
    task_name: '',
    is_done: '',
    odts_code: '',
});

const showCancelModal = ref(false);
const openCancelModal = (request) => {
    cancelForm.id = request.id
    cancelForm.task_name = request.task.name
    cancelForm.odts_code = request.odts_code
    cancelForm.is_done = request.is_done
    showCancelModal.value = true;
}

const cancelNotify = (task) => {
    cancelForm.put(route('officer.notification.cancel', task), {
        onSuccess: () => {
            cancelForm.reset();
            showCancelModal.value = false;
        },

        preserveScroll: true,
        preserveState: true,
    });
}

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let requestUrl = computed(() => {
    let url = new URL(route('officer.notification.index'));
    url.searchParams.set('page', pageNumber.value);

    if (search.value) {
        url.searchParams.append('search', search.value);
    }

    return url
});

watch(
    () => requestUrl.value,
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
    <Head title="Notify Pending" />

    <OfficerLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Notify Pending
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8 sm:flex sm:items-center justify-start">
                    <div>
                        <h3
                            class="text-lg leading-6 font-medium text-gray-900"
                        >
                            Notify Pending
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            All task notifications pending the Chief's approval will appear here.
                        </p>
                    </div>
                </div>

                <div class="mb-4 relative text-sm text-gray-800 col-span-3">
                    <div
                        class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500"
                    >
                        <MagnifyingGlass />
                    </div>

                    <input
                        v-model="search"
                        type="text"
                        autocomplete="off"
                        placeholder="Search officer, odts..."
                        id="search"
                        class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                </div>

                <div v-if="requests.data.length != 0">
                    <div
                        v-for="request in requests.data"
                        :key="request.id"
                        class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Avatar Icon -->
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                                <svg
                                    class="h-6 w-6 text-blue-600 dark:text-blue-300"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                </svg>
                            </div>

                            <!-- Info -->
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ request.officer.name }}
                                        is
                                        <span
                                            class="font-bold"
                                            :class="request.request_status === 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                        >
                                            {{ request.request_status === 1 ? 'Done' : 'Not Done' }}
                                        </span>
                                    </h2>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ request.odts_code }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                    <span class="font-semibold text-blue-700 dark:text-blue-700">{{ request.officer.name }}</span>
                                    is
                                    <span
                                        class="font-semibold"
                                        :class="request.request_status === 1 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                    >
                                        {{ request.request_status === 1 ? 'Done' : 'Not Done' }}
                                    </span>
                                    with
                                    <span class="font-semibold text-violet-800 dark:text-violet-800">{{ request.task.name }}</span>,
                                    assigned on
                                    <span class="font-semibold text-teal-600 dark:teal-teal-200">{{ request.assigned_at }}</span>.
                                </p>

                                <!-- Action -->
                                <div class="mt-2 flex justify-end space-x-4">
                                    <SecondaryButton @click="openCancelModal(request)">
                                        <p>
                                            Cancel
                                        </p>                            
                                    </SecondaryButton>

                                    <PrimaryButton disabled>
                                        <p>
                                            Waiting for Confirmation
                                        </p>                            
                                    </PrimaryButton>
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
                    :data="requests" 
                    :updatedPageNumber="updatedPageNumber"
                /> 
            </div>
        </div>
    </OfficerLayout>

    <Modal :show="showCancelModal" @close="showCancelModal = false" maxWidth="lg" :closeable="true">
        <template #default>
            <div class="p-6 w-[30rem]">
                <div class="flex items-center justify-start space-x-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-lg font-semibold">
                        <div v-if="cancelForm.is_done">
                            Cancel Not Done Notify ?
                        </div>
                        <div v-else>
                            Cancel Done Notify ?
                        </div>
                    </h2>
                </div>

                <p class="mt-4 text-md text-gray-600 text-left">
                    Are you sure you want to cancel notify
                    <strong>{{ cancelForm.task_name }}</strong>
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <DangerButton @click="cancelNotify(cancelForm.id)" :disabled="cancelForm.processing">
                        Cancel Notify
                    </DangerButton>
                    <SecondaryButton @click="showCancelModal = false" class="btn btn-secondary">
                        No Don't Cancel Notify
                    </SecondaryButton>
                </div>
            </div>
        </template>
    </Modal>
</template>



