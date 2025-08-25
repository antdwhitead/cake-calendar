<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: number;
}

interface Emits {
    'update:modelValue': [value: number];
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = currentYear - 2; year <= currentYear + 5; year++) {
        years.push(year);
    }
    return years;
});

const updateValue = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('update:modelValue', parseInt(target.value));
};
</script>

<template>
    <!-- Year Selection -->
    <div>
        <label for="year" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Year for Cake Calendar </label>
        <select
            id="year"
            :value="modelValue"
            @change="updateValue"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
            <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
            </option>
        </select>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select the year for which to calculate cake days</p>
    </div>
</template>
