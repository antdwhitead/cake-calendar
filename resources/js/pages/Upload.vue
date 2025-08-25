<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-2xl mx-auto">
        <div class="mb-8">
          <Link
            :href="route('home')"
            class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mb-4"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Calendar
          </Link>
          
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Upload Employee Data
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Upload a CSV, TXT, or Excel file with employee names and birthdays
          </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <div class="space-y-6">
              <!-- Year Selection -->
              <div>
                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Year for Cake Calendar
                </label>
                <select
                  id="year"
                  v-model="form.year"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  Select the year for which to calculate cake days
                </p>
              </div>

              <!-- File Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Employee Data File
                </label>
                
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
                      <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      <p class="text-gray-500 text-center">
                        Drag and drop files here or click to browse
                      </p>
                      <p class="text-sm text-gray-400 mt-2">
                        Supported formats: CSV, TXT, Excel (.xlsx)
                      </p>
                    </div>
                  </template>
                </FileUpload>

                <div v-if="errors.file" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ errors.file }}
                </div>
              </div>

              <!-- File Format Info -->
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">
                  File Format Requirements
                </h3>
                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
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
                  class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center"
                >
                  <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
import { Link, router, useForm } from '@inertiajs/vue3'
import FileUpload from 'primevue/fileupload'
import { computed, ref } from 'vue'

const fileUpload = ref()
const processing = ref(false)

const form = useForm({
  file: null as File | null,
  year: new Date().getFullYear()
})

const { errors } = defineProps<{
  errors: Record<string, string>
}>()

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let year = currentYear - 2; year <= currentYear + 5; year++) {
    years.push(year)
  }
  return years
})

const onFileSelect = (event: { files: File[] }) => {
  if (event.files.length > 0) {
    form.file = event.files[0]
  }
}

const onFileRemove = () => {
  form.file = null
}

const submitForm = () => {
  if (!form.file) return
  
  processing.value = true
  
  form.post(route('upload.store'), {
    onFinish: () => {
      processing.value = false
    },
    onError: () => {
      processing.value = false
    }
  })
}
</script>