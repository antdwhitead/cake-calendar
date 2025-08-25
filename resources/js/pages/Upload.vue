<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                <div class="mb-8">
                    <Link
                        :href="route('home')"
                        class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Calendar
                    </Link>

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Upload Employee Data</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Upload a CSV, TXT, or Excel file with employee names and birthdays</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                    <form @submit.prevent="submitForm" enctype="multipart/form-data">
                        <div class="space-y-6">
                            <!-- Year Selection -->
                            <div>
                                <label for="year" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Year for Cake Calendar
                                </label>
                                <select
                                    id="year"
                                    v-model="form.year"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option v-for="year in availableYears" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select the year for which to calculate cake days</p>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Employee Data File </label>

                                <FileUpload
                                    ref="fileUpload"
                                    name="file"
                                    :multiple="false"
                                    accept=".csv,.txt,.xlsx"
                                    :max-file-size="2097152"
                                    @select="onFileSelect"
                                    @remove="onFileRemove"
                                    :auto="false"
                                    choose-label="Choose File"
                                    upload-label="Upload"
                                    cancel-label="Clear"
                                    :show-upload-button="false"
                                    :show-cancel-button="true"
                                >
                                    <template #empty>
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <svg class="mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                                />
                                            </svg>
                                            <p class="text-center text-gray-500">Drag and drop files here or click to browse</p>
                                            <p class="mt-2 text-sm text-gray-400">Supported formats: CSV, TXT, Excel (.xlsx)</p>
                                        </div>
                                    </template>
                                </FileUpload>

                                <div v-if="errors.file" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.file }}
                                </div>
                            </div>

                            <!-- File Format Info -->
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                                <h3 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-200">File Format Requirements</h3>
                                <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-300">
                                    <li>• Each row should contain: Employee Name, Date of Birth</li>
                                    <li>• Date format: YYYY-MM-DD (e.g., 1990-06-15)</li>
                                    <li>• No header row required</li>
                                    <li>• Example: "John Doe,1990-06-15"</li>
                                </ul>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="!form.file || processing"
                                    class="flex items-center rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                >
                                    <svg v-if="processing" class="mr-3 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    {{ processing ? 'Processing...' : 'Upload & Generate Calendar' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FileUpload from 'primevue/fileupload';
import { computed, ref } from 'vue';

const fileUpload = ref();
const processing = ref(false);

const form = useForm({
    file: null as File | null,
    year: new Date().getFullYear(),
});

const { errors } = defineProps<{
    errors: Record<string, string>;
}>();

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = currentYear - 2; year <= currentYear + 5; year++) {
        years.push(year);
    }
    return years;
});

const onFileSelect = (event: { files: File[] }) => {
    if (event.files.length > 0) {
        form.file = event.files[0];
    }
};

const onFileRemove = () => {
    form.file = null;
};

const submitForm = () => {
    if (!form.file) return;

    processing.value = true;

    form.post(route('upload.store'), {
        onFinish: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script>
