<script setup lang="ts">
import { computed } from 'vue';

interface CakeDay {
    id: number;
    date: string;
    cakeType: 'small' | 'large';
    names: string[];
}

interface Props {
    cakeDays: CakeDay[];
}

const props = defineProps<Props>();

const upcomingCakeDays = computed(() => {
    const today = new Date();
    return props.cakeDays
        .filter((cakeDay) => new Date(cakeDay.date) >= today)
        .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
        .slice(0, 6);
});

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-UK', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
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
</template>
