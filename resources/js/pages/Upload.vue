<script setup lang="ts">
import FileFormatInfo from '@/components/upload/FileFormatInfo.vue';
import FileUploadArea from '@/components/upload/FileUploadArea.vue';
import SubmitButton from '@/components/upload/SubmitButton.vue';
import YearSelector from '@/components/upload/YearSelector.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const processing = ref(false);

const form = useForm({
    file: null as File | null,
    year: new Date().getFullYear(),
});

const { errors } = defineProps<{
    errors: Record<string, string>;
}>();

const onFileSelect = (file: File) => {
    form.file = file;
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
                            <YearSelector v-model="form.year" />

                            <FileUploadArea :file="form.file" :errors="errors" @select="onFileSelect" @remove="onFileRemove" />

                            <FileFormatInfo />

                            <SubmitButton :disabled="!form.file || processing" :processing="processing" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
