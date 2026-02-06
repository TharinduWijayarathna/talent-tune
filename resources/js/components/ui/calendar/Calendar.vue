<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'
import { format, startOfMonth, endOfMonth, eachDayOfInterval, isSameMonth, isSameDay, addMonths, subMonths, startOfWeek, endOfWeek } from 'date-fns'

interface Props {
  modelValue?: Date
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined,
})

const emits = defineEmits<{
  (e: 'update:modelValue', date: Date | undefined): void
}>()

const currentDate = ref(props.modelValue ? new Date(props.modelValue) : new Date())
const selectedDate = computed({
  get: () => props.modelValue,
  set: (value) => emits('update:modelValue', value),
})

// Update currentDate when modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    currentDate.value = new Date(newValue)
  }
}, { immediate: true })

const monthStart = computed(() => startOfMonth(currentDate.value))
const monthEnd = computed(() => endOfMonth(currentDate.value))
const calendarStart = computed(() => startOfWeek(monthStart.value))
const calendarEnd = computed(() => endOfWeek(monthEnd.value))

const days = computed(() => {
  return eachDayOfInterval({
    start: calendarStart.value,
    end: calendarEnd.value,
  })
})

const monthYear = computed(() => format(currentDate.value, 'MMMM yyyy'))

const previousMonth = () => {
  currentDate.value = subMonths(currentDate.value, 1)
}

const nextMonth = () => {
  currentDate.value = addMonths(currentDate.value, 1)
}

const selectDate = (date: Date) => {
  selectedDate.value = date
}

const isSelected = (date: Date) => {
  return selectedDate.value && isSameDay(date, selectedDate.value)
}

const isCurrentMonth = (date: Date) => {
  return isSameMonth(date, currentDate.value)
}
</script>

<template>
  <div :class="cn('p-3', props.class)">
    <div class="flex items-center justify-between mb-4">
      <button
        type="button"
        @click="previousMonth"
        class="rounded-md p-1 hover:bg-accent hover:text-accent-foreground"
      >
        <ChevronLeft class="h-4 w-4" />
      </button>
      <div class="text-sm font-medium">{{ monthYear }}</div>
      <button
        type="button"
        @click="nextMonth"
        class="rounded-md p-1 hover:bg-accent hover:text-accent-foreground"
      >
        <ChevronRight class="h-4 w-4" />
      </button>
    </div>
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
        :key="day"
        class="text-center text-xs font-medium text-muted-foreground p-1"
      >
        {{ day }}
      </div>
    </div>
    <div class="grid grid-cols-7 gap-1">
      <button
        v-for="day in days"
        :key="day.toISOString()"
        type="button"
        @click="selectDate(day)"
        :class="cn(
          'rounded-md p-2 text-sm hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none',
          !isCurrentMonth(day) && 'text-muted-foreground opacity-50',
          isSelected(day) && 'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
        )"
      >
        {{ format(day, 'd') }}
      </button>
    </div>
  </div>
</template>

