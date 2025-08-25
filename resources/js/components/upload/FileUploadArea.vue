<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    file: File | null;
    errors?: Record<string, string>;
}

interface Emits {
    select: [file: File];
    remove: [];
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const fileInput = ref<HTMLInputElement>();

const onFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        emit('select', target.files[0]);
    }
};

const onFileRemove = () => {
    emit('remove');
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <!-- File Upload -->
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Employee Data File </label>

        <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 dark:border-gray-600">
            <div class="flex flex-col items-center justify-center">
                <svg class="mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                </svg>

                <input
                    ref="fileInput"
                    type="file"
                    accept=".csv,.txt,.xlsx"
                    @change="onFileSelect"
                    class="mb-4 block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-400 dark:file:bg-blue-900/20 dark:file:text-blue-300 dark:hover:file:bg-blue-900/40"
                />

                <p class="text-center text-gray-500">Choose a file or drag and drop</p>
                <p class="mt-2 text-sm text-gray-400">Supported formats: CSV, TXT, Excel (.xlsx)</p>

                <div v-if="file" class="mt-4 flex items-center space-x-2">
                    <span class="text-sm text-green-600 dark:text-green-400">{{ file.name }}</span>
                    <button type="button" @click="onFileRemove" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="errors?.file" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ errors.file }}
        </div>
    </div>
</template>
