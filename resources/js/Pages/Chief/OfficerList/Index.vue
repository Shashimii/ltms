<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    officers: {
        type: Object,
        required: true,
    }
});

const routeOfficerTask = (officer) => {
    router.visit(`/chief/assigned-task`, {
        method: 'get',
        data: {
            officer_id: officer,
            page: 1,
            status: '',
        }
    })
}

//searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let url = computed(() => {
    let url = new URL(route('chief.officer.index'));
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
    <Head title="Officer List" />

    <ChiefLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Officer List
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-8">
                <div class="flex flex-col justify-left sm:flex-row mb-4">
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
                <div
                    v-if="props.officers.data.length != 0"
                    v-for="officer in props.officers.data"
                    :key="officer.id"
                    class="flex flex-col space-y-2 lg:flex-row lg:items-center lg:justify-between p-4 mb-4 bg-white dark:bg-gray-800 shadow rounded"

                >
                    <div class="flex space-x-2">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>

                        <div class="flex-col space-y-2">
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ officer.name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ officer.email }}
                            </p>
                        </div>
                    </div>
                    <PrimaryButton @click="routeOfficerTask(officer.id)" class="flex items-center justify-center">
                        View Assigned Tasks
                    </PrimaryButton>
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
                    :data="props.officers" 
                    :updatedPageNumber="updatedPageNumber"
                /> 
            </div>
        </div>
    </ChiefLayout>
</template>
