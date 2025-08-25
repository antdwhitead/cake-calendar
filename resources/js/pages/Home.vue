<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();

// Route helper function
const route = (name: string, params?: any) => {
    return window.route(name, params);
};

interface CakeDay {
    id: number;
    date: string;
    cakeType: 'small' | 'large';
    names: string[];
}

interface Props {
    cakeDays: CakeDay[];
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

const upcomingCakeDays = computed(() => {
    const today = new Date();
    return props.cakeDays
        .filter((cakeDay) => new Date(cakeDay.date) >= today)
        .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
        .slice(0, 6);
});

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4 py-8">
            <!-- Success Message -->
            <div
                v-if="page.props.flash?.success"
                class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
            >
                <div class="flex items-center">
                    <svg class="mr-3 h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-green-700 dark:text-green-300">
                        {{ page.props.flash.success }}
                    </p>
                </div>
            </div>

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

                    <Link
                        :href="route('upload.show')"
                        class="rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition-colors hover:bg-blue-700"
                    >
                        Upload File
                    </Link>
                </div>
            </div>

            <!-- Calendar removed -->

            <div v-if="cakeDays.length === 0" class="mt-8 text-center">
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <h3 class="mb-2 text-lg font-semibold text-yellow-800 dark:text-yellow-200">No cake days scheduled</h3>
                    <p class="mb-4 text-yellow-700 dark:text-yellow-300">Upload a file with employee birthdays to generate the cake calendar.</p>
                    <Link
                        :href="route('upload.show')"
                        class="inline-flex items-center rounded-lg bg-yellow-600 px-4 py-2 font-medium text-white transition-colors hover:bg-yellow-700"
                    >
                        Upload Employee Data
                    </Link>
                </div>
            </div>

            <div v-if="cakeDays.length > 0" class="mt-8">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Upcoming Cake Days</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="cakeDay in upcomingCakeDays"
                        :key="cakeDay.id"
                        class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ formatDate(cakeDay.date) }}
                            </span>
                            <span
                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="
                                    cakeDay.cakeType === 'large'
                                        ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300'
                                        : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'
                                "
                            >
                                {{ cakeDay.cakeType }} cake
                            </span>
                        </div>
                        <div class="text-gray-900 dark:text-white">
                            {{ cakeDay.names.join(', ') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
