<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { DateFormatter, parseDate, type DateValue, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { ref, defineEmits, watch, onMounted } from 'vue';

// Props and Emits
const model = defineProps({
  modelValue: {
    type: String,
    required: true,
  }
});
const emit = defineEmits(['update:modelValue']);

// Initialize value with a flexible type
const value = ref<any>(null);

// Date Formatter
const df = new DateFormatter('en-US', {
  dateStyle: 'long',
});

// Format Date Function
const formatDateValue = (dateValue: any) => {
  if (!dateValue) return '';
  return `${dateValue.year}-${String(dateValue.month).padStart(2, '0')}-${String(dateValue.day).padStart(2, '0')}`;
};

// Initialize date value
// onMounted(() => {
//   if (model.modelValue) {
//     // Safely cast to any to avoid type issues
//     value.value = parseDate(model.modelValue) as any;
//   }
// });

// watch instead when used on modal
watch(
  () => model.modelValue,
  (newVal) => {
    if (newVal) {
      value.value = parseDate(newVal) as any;
    }
  },
  { immediate: true }
);

// Watch value change and emit
watch(value, (newValue) => {
  emit('update:modelValue', formatDateValue(newValue || ''));
});
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-full mt-1 h-[43px] justify-start text-left font-normal',
          !value && 'text-muted-foreground',
        )"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ modelValue ? df.format(value?.toDate?.(getLocalTimeZone())) : "Pick a date" }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0 z-50">
      <Calendar v-model="value" initial-focus />
    </PopoverContent>
  </Popover>
</template>