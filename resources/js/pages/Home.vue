<script setup lang="ts">
import CakeDaysList from '@/components/home/CakeDaysList.vue';
import EmptyState from '@/components/home/EmptyState.vue';
import HomeHeader from '@/components/home/HomeHeader.vue';
import type { CakeDay } from '@/types/cakeday';

interface Props {
    cakeDays: CakeDay[];
    currentYear: number;
}

const props = defineProps<Props>();
</script>
<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4 py-8">
            <HomeHeader :current-year="props.currentYear" />
            <EmptyState v-if="cakeDays.length === 0" />
            <CakeDaysList :cake-days="props.cakeDays" />
        </div>
    </div>
</template>
