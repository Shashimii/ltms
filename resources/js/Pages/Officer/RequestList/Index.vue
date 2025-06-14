<script setup>
import OfficerLayout from '@/Layouts/OfficerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

let props = defineProps({
    requests: {
        type: Object,
        required: true
    }
});

const requests = computed(() => props.requests.data);

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
                        <p class="mt-1 text-sm text-gray-500">
                            All task notifications pending the Chief's approval will appear here.
                        </p>
                    </div>
                </div>
                <div v-for="request in requests" class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-start space-x-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                            <div class="flex justify-between w-full">
                                <h2 class="text-lg font-semibold">
                                    {{ request.officer.name }}
                                    Notified the chief
                                    <span v-if="request.request_status === 1">Done</span>
                                    <span v-else-if="request.request_status === 2">Undone</span>
                                </h2>

                                <p class="text-lg font-semibold">
                                    {{ request.odts_code }}
                                </p>
                            </div>
                        </div>
                        <div class="ms-16">
                            <p class="mt-4 text-md text-gray-600 text-justify">
                                <strong class="text-gray-900">{{ request.officer.name }}</strong>
                                has notified the Chief that the
                                <strong class="text-gray-900">{{ request.task.name }}</strong>
                                task assigned on
                                <strong class="text-gray-900">{{ request.assigned_at }}</strong>
                                is
                                <strong class="text-gray-900">
                                    <span v-if="request.request_status === 1">Done.</span>
                                    <span v-else-if="request.request_status === 2">Undone.</span>
                                </strong>
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end space-x-6">
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



