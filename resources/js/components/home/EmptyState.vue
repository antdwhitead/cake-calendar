<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// Route helper function
const route = (name: string, params?: any) => {
    return window.route(name, params);
};
</script>

<template>
    <div class="mt-8 text-center">
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
</template>
