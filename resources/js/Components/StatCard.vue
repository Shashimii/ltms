<template>
    <div class="p-4 bg-white flex items-center justify-start space-x-4 rounded shadow dark:bg-gray-800">
        <slot name="icon" />
        <div class="flex flex-col">
            <span class="text-xl font-bold">
                <slot name="count" />
            </span>
            <span class="text-gray-500">
                <slot name="label" />
            </span>
        </div>
    </div>
</template>