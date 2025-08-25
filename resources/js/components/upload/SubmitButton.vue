<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner';

interface Props {
    disabled: boolean;
    processing: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="flex justify-end">
        <button
            type="submit"
            :disabled="disabled"
            class="flex items-center rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
        >
            <ProgressSpinner v-if="processing" class="mr-3 h-4 w-4" style="width: 1.5rem; height: 1.5rem" stroke-width="4" />
            <span class="pl-3"> {{ processing ? 'Processing...' : 'Upload & Generate Calendar' }}</span>
        </button>
    </div>
</template>
