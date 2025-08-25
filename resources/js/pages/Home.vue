<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Cake Calendar {{ currentYear }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Track employee cake days throughout the year
          </p>
        </div>
        
        <div class="flex gap-4 items-center">
          <select
            v-model="selectedYear"
            @change="changeYear"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
          >
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
          
          <Link
            :href="route('upload.show')"
            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
          >
            Upload File
          </Link>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <VCalendar
          :attributes="calendarAttributes"
          :rows="2"
          :columns="6"
          class="w-full"
          title-position="left"
          nav-visibility="focus"
        />
      </div>

      <div v-if="cakeDays.length === 0" class="mt-8 text-center">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
            No cake days scheduled
          </h3>
          <p class="text-yellow-700 dark:text-yellow-300 mb-4">
            Upload a file with employee birthdays to generate the cake calendar.
          </p>
          <Link
            :href="route('upload.show')"
            class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors"
          >
            Upload Employee Data
          </Link>
        </div>
      </div>

      <div v-if="cakeDays.length > 0" class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
          Upcoming Cake Days
        </h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="cakeDay in upcomingCakeDays"
            :key="cakeDay.id"
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ formatDate(cakeDay.date) }}
              </span>
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="cakeDay.cakeType === 'large' 
                  ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300'
                  : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'"
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

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

interface CakeDay {
  id: number
  date: string
  cakeType: 'small' | 'large'
  names: string[]
}

interface Props {
  cakeDays: CakeDay[]
  currentYear: number
}

const props = defineProps<Props>()

const selectedYear = ref(props.currentYear)

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let year = currentYear - 2; year <= currentYear + 5; year++) {
    years.push(year)
  }
  return years
})

const changeYear = () => {
  router.get(route('home'), { year: selectedYear.value })
}

const calendarAttributes = computed(() => {
  return props.cakeDays.map(cakeDay => ({
    key: cakeDay.id,
    dates: new Date(cakeDay.date),
    dot: {
      color: cakeDay.cakeType === 'large' ? 'orange' : 'blue',
      class: 'opacity-75'
    },
    popover: {
      label: `${cakeDay.cakeType.charAt(0).toUpperCase() + cakeDay.cakeType.slice(1)} cake for ${cakeDay.names.join(', ')}`,
      visibility: 'hover'
    }
  }))
})

const upcomingCakeDays = computed(() => {
  const today = new Date()
  return props.cakeDays
    .filter(cakeDay => new Date(cakeDay.date) >= today)
    .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
    .slice(0, 6)
})

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>