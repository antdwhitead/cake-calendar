<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    currentYear: number;
}

const props = defineProps<Props>();

const selectedYear = ref(props.currentYear);

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = currentYear - 2; year <= currentYear + 5; year++) {
        years.push(year);
    }
    return years;
});

const changeYear = () => {
    router.get(route('home'), { year: selectedYear.value });
};
</script>

<template>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cake Calendar {{ currentYear }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Track employee cake days throughout the year</p>
        </div>

        <div class="flex items-center gap-4">
            <select
                v-model="selectedYear"
                @change="changeYear"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            >
                <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                </option>
            </select>

            <Link :href="route('upload.show')" class="rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-700">
                Upload File
            </Link>
        </div>
    </div>
</template>
