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

const formatActivity = (activity) => {
    const map = {
        Assigned: 'Assigned',
        Edited: 'Edited',
        Deleted: 'Deleted',
        Done_Notify: 'Done Notify',
        Done_Confirmation: 'Done Confirmation',
        Not_Done_Notify: 'Not Done Notify',
        Not_Done_Confirmation: 'Not Done Confirmation',
        Done_Notify_Cancel: 'Done Notify Cancel',
        Not_Done_Notify_Cancel: 'Not Done Notify Cancel'
    }

    return map[activity] ?? activity;
};

const activityColor = (activity) => {
    switch (activity) {
        case 'Assigned':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-white'; // Info/Action
        case 'Edited':
            return 'bg-orange-300 text-orange-800 dark:bg-orange-600 dark:text-white';
        case 'Deleted':
            return 'bg-red-300 text-red-800 dark:bg-red-600 dark:text-white'; 
        case 'Done_Notify':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-white'; // Notification of done
        case 'Done_Confirmation':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-600 dark:text-white'; // Final confirmation
        case 'Not_Done_Notify':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-white'; // Warning
        case 'Not_Done_Confirmation':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-600 dark:text-white'; // Still not done
        case 'Done_Notify_Cancel':
            return 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-white'; // Neutral
        case 'Not_Done_Notify_Cancel':
            return 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-white'; // Error/Cancel
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white'; // Fallback
    }
}

const formattedLogMessage = (log) => {
    const chief = `<span class="font-semibold text-blue-600 dark:text-blue-300 bg-blue-100 dark:bg-blue-900 px-1 rounded">${log.chief_name}</span>`;    
    const officer = `<span class="font-semibold text-green-600 dark:text-green-300 bg-green-100 dark:bg-green-900 px-1 rounded">${log.officer_name}</span>`; 
    const task = `<span class="font-semibold text-violet-700 dark:text-violet-200 bg-violet-100 dark:bg-violet-900 px-1 rounded">${log.task_name}</span>`;    
    const odts_code_old = `<span class="font-semibold text-amber-700 dark:text-amber-200 bg-amber-100 dark:bg-amber-900 px-1 rounded">${log.odts_code_old}</span>`; 
    const assigned_at_old = `<span class="font-semibold text-amber-700 dark:text-amber-200 bg-amber-100 dark:bg-amber-900 px-1 rounded">${log.assigned_at_old}</span>`; 
    const odts_code = `<span class="font-semibold text-teal-800 dark:text-teal-100 bg-teal-100 dark:bg-teal-900 px-1 rounded">${log.odts_code}</span>`;    
    const assigned_at = `<span class="font-semibold text-teal-800 dark:text-teal-100 bg-teal-100 dark:bg-teal-900 px-1 rounded">${log.assigned_at}</span>`; 



    switch (log.activity) {
        case 'Assigned':
            return `Legal Chief ${chief} assigned the task ${task} to Officer ${officer}.`;

        case 'Edited':
            return `Legal Chief ${chief} updated the assigned task ${task}: changed the ODTS code from ${odts_code_old} to ${odts_code}, and the assignment date from ${assigned_at_old} to ${assigned_at}.`;
        
        case 'Deleted':
            return `Legal Chief ${chief} deleted the assigned task ${task}.`;
        
        case 'Done_Notify':
            return `Officer ${officer} is done with the task ${task}. Waiting for confirmation from Legal Chief.`;

        case 'Done_Confirmation':
            return `Legal Chief ${chief} confirmed that Officer ${officer} completed the task ${task}.`;

        case 'Not_Done_Notify':
            return `Officer ${officer} is still not done with the task ${task}. Waiting for confirmation from Legal Chief.`;

        case 'Not_Done_Confirmation':
            return `Legal Chief ${chief} confirmed that task ${task} is not completed by Officer ${officer}.`;

        case 'Done_Notify_Cancel':
            return `Officer ${officer} canceled the done notify on ${task}.`;

        case 'Not_Done_Notify_Cancel':
            return `Officer ${officer} canceled the not done notify on ${task}.`;

        default:
            return `Activity logged by Legal Chief ${chief} regarding task ${task}.`;
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
                        class="mt-4 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-4 rounded-lg bg-white shadow dark:bg-gray-800 text-base text-gray-900 dark:text-gray-100 leading-relaxed border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between align-center">
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ history.created_at }}
                                </p>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    Odts Code: {{ history.odts_code }} 
                                </p>
                            </div>

                            <p>
                                <span :class="`${activityColor(history.activity)} px-2 py-1 rounded-md font-semibold text-sm inline-block mb-2`">
                                    {{ formatActivity(history.activity) }}
                                </span>
                                <br />
                                <span v-html="formattedLogMessage(history)"></span>
                            </p>
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
