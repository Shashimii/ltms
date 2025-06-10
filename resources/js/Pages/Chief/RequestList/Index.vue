<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

let props = defineProps({
    requests: {
        type: Object,
        required: true
    }
});

let requests = props.requests.data;

console.log(requests)

</script>

<template>
    <Head title="Requests" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Requests
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
                                    Request to Mark as                        
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
                                has requested you to mark this task named
                                <strong class="text-gray-900">{{ request.task.name }}</strong>
                                assigned on
                                <strong class="text-gray-900">{{ request.assigned_at }}</strong>
                                to be 
                                <strong class="text-gray-900">
                                    <span v-if="request.request_status === 1">Done.</span>
                                    <span v-else-if="request.request_status === 2">Undone.</span>
                                </strong>
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <PrimaryButton>
                                <p>
                                    Mark as
                                    <span v-if="request.request_status === 1">Done</span>
                                    <span v-else-if="request.request_status === 2">Undone</span>
                                </p>                            
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ChiefLayout>
</template>



