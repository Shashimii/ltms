<script setup>
import { watch } from 'vue';

const props = defineProps({
  show: Boolean
});

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
)

// const emit = defineEmits(['close']); disable outside click close
</script>

<template>
    <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
        v-show="show"
        class="fixed inset-0 z-10 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        >
            <!-- Backdrop @click="$emit('close')"-->
            <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"

            />

            <!-- Modal content -->
            <div class="flex min-h-full items-center justify-center py-4 px-4 text-center">
                <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                    v-show="show"
                    class="relative transform overflow-hidden bg-white text-left shadow-xl transition-all w-full h-full sm:my-8 sm:max-w-none sm:max-h-none rounded-none sm:rounded-lg"
                    @click.stop
                    >
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <slot name="main" />
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <div class="mt-6 flex justify-end gap-4">
                                <slot name="footer" />
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </transition>
</template>
