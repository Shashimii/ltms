<script setup>
import ChiefLayout from '@/Layouts/ChiefLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
import Pagination from '@/Components/Pagination.vue';
import ModalTable from '@/Components/ModalTable.vue';
import StatCard from '@/Components/StatCard.vue';

import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch, onMounted } from 'vue';
import ExcelJS from 'exceljs';

let props = defineProps({
    officers: {
        type: Object,
        required: true
    },

    tasks: {
        type: Object,
        required: true
    },

    assignedTasks: {
        type: Object,
        required: true
    },

    requests: {
        type: Object,
        required: true
    },

    histories: {
        type: Object,
        required: true,
    }
});


let officers = props.officers.data,
    tasks = props.tasks.data,
    assignedTasks = props.assignedTasks.data

const requests = computed(() => props.requests.data);

const completedTasks = computed(() => {
    return assignedTasks.filter(task => task.is_done).length;
});

const pendingTasks = computed(() => {
    return assignedTasks.filter(task => !task.is_done).length;
});

const officerCount = computed(() => {
    return officers.length;
})

const requestCount = computed(() => {
    return requests.value.length;
});

let taskMap = computed(() => {
    const map = {};
    assignedTasks.forEach((assignedDuty) => {
        const key = `${assignedDuty.officer.id}-${assignedDuty.task.id}`;
        if (!map[key]) {
            map[key] = [];
        }
        map[key].push(assignedDuty);
    });
    return map;
});

const mapTable = ref(null);
let isDragging = false,
    startX = 0,
    startY = 0,
    scrollLeft = 0,
    scrollTop = 0

const getAssignedDuty = (officerId, taskId) => {
    let key = `${officerId}-${taskId}`;
    return taskMap.value[key] || [];
}

// Excel Exporting

const excelExport = async () => {
    let workbook = new ExcelJS.Workbook();
    let worksheet = workbook.addWorksheet("Assigned Tasks");

    // Add headers
    worksheet.addRow(["Tasks / Targets", ...officers.map(o => o.name)]);

    // Add data rows
    tasks.forEach((task) => {
        let rowData = [task.name];

        officers.forEach((officer) => {
            let assignedTasks = getAssignedDuty(officer.id, task.id);
            if (assignedTasks.length > 0) {
                rowData.push(assignedTasks.map(ad => `${ad.odts_code}\n${ad.assigned_at}`).join("\n"));
            } else {
                rowData.push("");
            }
        });

        const row = worksheet.addRow(rowData);

        // Auto height calculation based on line breaks
        const maxLines = Math.max(...rowData.map(cell => (cell.match(/\n/g) || []).length + 1));
        row.height = Math.max(80, maxLines * 20); // 20px per line
    });

    // Apply styles
    worksheet.columns.forEach(column => {
        column.width = 30; // Set column width
    });

    // Format odts code color
    const formatTextColorGreen = (text) => {
    const regex = /(\([^)]+\))/g; // match texts like "(text)"
    const textParts = text.split(regex); // split text into parts needed for richText

        return textParts.map(part => {
            if (regex.test(part)) {
                return { font: { color: { argb: '008000' } }, text: part };
            } else {
                return { text: part };
            }
        });
    }

    // Loop to Apply styles to each row and cells
    worksheet.eachRow((row, rowNumber) => {
        row.eachCell((cell, colNumber) => {

            let cellValue = cell.value;

            if (cellValue != '' && colNumber != 1) {
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FFF9AA' } // Cell with value color
                }
            }

            if (colNumber === 1) {
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'F1F1F1' } // Side column color
                }
            }

            if (typeof cellValue === 'string' && cellValue.includes('(')) {
                cell.value = { richText: formatTextColorGreen(cellValue) };
            }

            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
            cell.border = {
                top: { style: "medium", color: { argb: "000000" } },
                left: { style: "medium", color: { argb: "000000" } },
                bottom: { style: "medium", color: { argb: "000000" } },
                right: { style: "medium", color: { argb: "000000" } },
            };

            if (rowNumber === 1) {
                cell.font = { bold: true, size: 14 }; // Header font style
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'F1F1F1' } }; // Header color
            }
        });

        if (rowNumber === 1) {
            row.height = 40; // Header Height
        }
    });

    // Download Excel File
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `IPCR_${new Date().toISOString().split('T')[0]}.xlsx`;
    link.click();
};

// map scrolling drag

const handleDrag = (e) => {
  if (!isDragging || !mapTable.value) return;

  const moveX = e.clientX - startX;
  const moveY = e.clientY - startY;

  mapTable.value.scrollLeft = scrollLeft - moveX;
  mapTable.value.scrollTop = scrollTop - moveY;
};

const stopDrag = () => {
  isDragging = false;
  if (mapTable.value) {
    mapTable.value.style.cursor = '';
    mapTable.value.style.userSelect = '';
  }

  document.removeEventListener('mousemove', handleDrag);
  document.removeEventListener('mouseup', stopDrag);
};

const startDrag = (e) => {
  if (!mapTable.value) return;

  isDragging = true;
  startX = e.clientX;
  startY = e.clientY;
  scrollLeft = mapTable.value.scrollLeft;
  scrollTop = mapTable.value.scrollTop;

  mapTable.value.style.userSelect = 'none';
  mapTable.value.style.cursor = 'grabbing';

  document.addEventListener('mousemove', handleDrag);
  document.addEventListener('mouseup', stopDrag);
};

onBeforeUnmount(() => {
  document.removeEventListener('mousemove', handleDrag);
  document.removeEventListener('mouseup', stopDrag);
});

// logs

const activityColor = (activity) => {
    switch (activity) {
        case 'Assigned':
            return 'px-2 font-semibold bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white rounded'; // Assign
        case 'Edited':
            return 'px-2 font-semibold bg-orange-300 text-orange-800 dark:bg-orange-600 dark:text-white rounded'; // Edited
        case 'Deleted':
            return 'px-2 font-semibold bg-red-300 text-red-800 dark:bg-red-600 dark:text-white rounded'; // Deleted
        case 'Done':
            return 'px-2 font-semibold bg-green-100 text-green-800 dark:bg-green-600 dark:text-white rounded'; // Done
        case 'Undone':
            return 'px-2 font-semibold bg-pink-300 text-pink-800 dark:bg-pink-600 dark:text-white rounded'; // Undone
        default:
            return 'px-2 font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white rounded'; // Catch
    }
}

// searchbar
let pageNumber = ref(1),
    search = ref(usePage().props.search ?? "")

const updatedPageNumber = (link) => {
    pageNumber.value = link.url.split('=')[1];
}

let url = computed(() => {
    let url = new URL(route('chief.dashboard'));
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

// Modal Table
const showModalTable = ref(false);

const openModalTable = () => {
    showModalTable.value = true;
}

// Routes
const assignedTaskRoute = () => {
    router.visit(route('chief.assigned-task.index'))
}

const historyRoute = () => {
    router.visit(route('chief.history'))
}

</script>

<template>
    <Head title="Dashboard" />

    <ChiefLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="mb-4 sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Assigned Task Status
                        </h1>
                        <p class="mt-2 text-sm text-gray-700">
                            All related count on the assigned task will be shown here.
                        </p>
                    </div>

                    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <PrimaryButton
                            @click="assignedTaskRoute"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Assign Task
                        </PrimaryButton>
                    </div>
                </div>

                <div class="w-full grid sm:grid-cols-2 md:grid-cols-3 gap-4">
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
                    <StatCard>
                        <template #icon>
                            <div class="p-4 bg-blue-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-800">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </div>
                        </template>
                        <template #count>
                            {{ officerCount }}
                        </template>
                        <template #label>
                            Officers
                        </template>
                    </StatCard>
                </div>

               <div v-if="assignedTasks.length != 0" class="mt-4 mb-8 overflow-hidden bg-gradient-to-r from-green-400 to-green-600 shadow-sm rounded-md lg:rounded-md">
                    <div class="p-6 text-gray-900">
                        <div class="sm:flex sm:items-center justify-between">
                            <div>
                                <h1
                                    class="text-lg leading-6 font-medium text-black-900"
                                >
                                    Excel Table
                                </h1>
                                <p class="mt-2 text-md text-black-500">
                                    Vizualization of assigned tasks to the officers.
                                </p>
                            </div>
                            <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                                <PrimaryButton
                                    class="hidden sm:inline-flex px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded"
                                    @click="openModalTable()"
                                >
                                    View Table
                                </PrimaryButton>

                                <!-- Export to Excel -->
                                <PrimaryButton
                                    class="px-4 py-2 flex items-center justify-center bg-green-800 hover:bg-green-700 text-white rounded w-full sm:w-auto"
                                    @click="excelExport"
                                >
                                    Export to Excel
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 mb-8 sm:flex sm:items-center justify-start">
                    <div class="sm:flex-auto">
                        <h1
                            class="text-lg leading-6 font-medium text-gray-900"
                        >
                            Recents
                        </h1>
                        <p class="mt-2 text-sm text-gray-500">
                            All recent actions will be shown here.
                        </p>
                    </div>

                    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <PrimaryButton
                            @click="historyRoute"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            View History
                        </PrimaryButton>
                    </div>
                </div>
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
                        placeholder="Search odts code..."
                        id="search"
                        class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:ring-green-500 dark:focus:ring-green-500 sm:text-sm sm:leading-6"
                    />
                </div>
                <div v-if="histories.data.length != 0">
                    <div
                        v-for="history in histories.data"
                        class="mt-4 overflow-hidden bg-white shadow-sm rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-4 rounded-lg bg-white shadow dark:bg-gray-800 text-base text-gray-900 dark:text-gray-100 leading-relaxed border border-gray-200 dark:border-gray-700">
                            <div v-if="history.activity === 'Assigned'" class="flex-col space-y-4">
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Assigned</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Edited'" class="flex-col space-y-4">
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex-col space-y-2">
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <p>Legal Chief Edited</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                                {{ history.task.name }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                            </svg>
                                            <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                                {{ history.officer.name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <p>From:</p>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>
                                            <p>ODTS Code:</p>
                                            <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                                {{ history.old_odts_code }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                            </svg>
                                            <p>Assignment Date:</p>
                                            <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                                {{ history.old_assigned_at }}
                                            </span>
                                        </div>  
                                    </div>
                                    <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                        <p>To:</p>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>
                                            <p>ODTS Code:</p>
                                            <span class="px-2 font-semibold bg-emerald-200 text-emerald-800 rounded">
                                                {{ history.odts_code }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                            </svg>
                                            <p>Assignment Date:</p>
                                            <span class="px-2 font-semibold bg-emerald-200 text-emerald-800 rounded">
                                                {{ history.assigned_at }}
                                            </span> 
                                        </div> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Deleted'" class="flex-col space-y-4">
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Deleted</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
                                </div>
                            </div>
                            <div v-if="history.activity === 'Done'" class="flex-col space-y-4">
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Marked as Done</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span>  
                                    </div>                                  
                                </div>
                            </div>
                            <div v-if="history.activity === 'Undone'" class="flex-col space-y-4">
                                <div class="flex justify-between">
                                    <span :class="activityColor(history.activity)">
                                        {{ history.activity }}
                                    </span>
                                    <span class="px-2 font-semibold bg-gray-200 text-gray-800 rounded">
                                        ODTS Code: {{ history.odts_code }}
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <p>Legal Chief Marked as Undone</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-orange-200 text-orange-800 rounded">
                                            {{ history.task.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                        </svg>
                                        <span class="px-2 font-semibold bg-teal-200 text-teal-800 rounded">
                                            {{ history.officer.name }}
                                        </span>                                        
                                    </div>
                                    <div class="flex space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <p>Assignment Date:</p>
                                        <span class="px-2 font-semibold bg-pink-200 text-pink-800 rounded">
                                            {{ history.assigned_at }}
                                        </span> 
                                    </div>                                   
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
                    :data="histories" 
                    :updatedPageNumber="updatedPageNumber"
                /> 
            </div>
        </div>
    </ChiefLayout>

    <ModalTable :show="showModalTable" @close="showModalTable = false">
        <template #main>
            <div class="mb-8 sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <div class="flex justify-between">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Excel Table
                        </h1>
                        <button @click="showModalTable = false" class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-700">
                        This is available for export on excel format.
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto max-h-[60rem]" @mousedown="startDrag" ref="mapTable">
                <table class="min-w-full divide-y divide-gray-300 bg-white border border-gray-300 rounded-lg shadow">
                    <thead class="bg-gray-100">
                        <tr class="divide-x divide-gray-300">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                <div class="flex items-center justify-center">
                                    <p>Tasks</p>
                                </div>
                            </th>
                            <th :colspan="officers.length" class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                <p>Action Officers</p>
                            </th>
                        </tr>
                        <tr class="bg-gray-50 divide-x divide-gray-300">
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600"></th>
                            <th v-for="officer in officers" :key="officer.id" class="px-4 py-2 text-center text-sm font-semibold text-gray-600">
                                <div class="min-w-[120px] flex items-center justify-center">{{ officer.name }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <tr v-for="duty in tasks" :key="duty.id" class="hover:bg-green-200 divide-x divide-gray-300">
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 bg-gray-100">
                                <div class="min-h-[80px] flex items-center">{{ duty.name }}</div>
                            </th>
                            <td 
                                v-for="officer in officers" 
                                :key="officer.id" 
                                class="px-4 py-2 text-center"
                                :class="{ 'bg-green-200': getAssignedDuty(officer.id, duty.id).length > 0 }"
                            >
                                <div v-if="getAssignedDuty(officer.id, duty.id).length > 0">
                                    <div v-for="(assigned, index) in getAssignedDuty(officer.id, duty.id)" :key="index">
                                        <p>
                                            {{ assigned.odts_code }} <br>
                                            {{ assigned.assigned_at }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="showModalTable= false" class="btn btn-secondary">
                Close
            </SecondaryButton>
        </template>
    </ModalTable>
</template>
