<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ref, computed, watch } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { ChevronUp, ChevronDown } from 'lucide-vue-next'

interface Props {
  modelValue?: string
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
})

const emits = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const hours = ref(0)
const minutes = ref(0)

const hoursDisplay = computed({
  get: () => String(hours.value).padStart(2, '0'),
  set: (val: string) => {
    const num = parseInt(val) || 0
    hours.value = Math.max(0, Math.min(23, num))
    updateTime()
  }
})

const minutesDisplay = computed({
  get: () => String(minutes.value).padStart(2, '0'),
  set: (val: string) => {
    const num = parseInt(val) || 0
    minutes.value = Math.max(0, Math.min(59, num))
    updateTime()
  }
})

const updateTime = () => {
  const h = String(hours.value).padStart(2, '0')
  const m = String(minutes.value).padStart(2, '0')
  emits('update:modelValue', `${h}:${m}`)
}

// Sync with modelValue changes
const syncFromValue = () => {
  if (props.modelValue) {
    const [h, m] = props.modelValue.split(':').map(Number)
    hours.value = isNaN(h) ? 0 : Math.max(0, Math.min(23, h))
    minutes.value = isNaN(m) ? 0 : Math.max(0, Math.min(59, m))
  } else {
    hours.value = 0
    minutes.value = 0
  }
}

// Watch for external changes to modelValue
watch(() => props.modelValue, () => {
  syncFromValue()
}, { immediate: true })

const incrementHours = (event: Event) => {
  event.stopPropagation()
  hours.value = (hours.value + 1) % 24
  updateTime()
}

const decrementHours = (event: Event) => {
  event.stopPropagation()
  hours.value = hours.value === 0 ? 23 : hours.value - 1
  updateTime()
}

const incrementMinutes = (event: Event) => {
  event.stopPropagation()
  minutes.value = (minutes.value + 5) % 60
  updateTime()
}

const decrementMinutes = (event: Event) => {
  event.stopPropagation()
  minutes.value = minutes.value === 0 ? 55 : minutes.value - 5
  updateTime()
}

const handleInputClick = (event: Event) => {
  event.stopPropagation()
}

const handleInputFocus = (event: Event) => {
  event.stopPropagation()
}
</script>

<template>
  <div :class="cn('flex items-center gap-3 p-4', props.class)" @mousedown.stop @click.stop>
    <!-- Hours -->
    <div class="flex flex-col items-center gap-2">
      <Button
        type="button"
        variant="ghost"
        size="sm"
        class="h-7 w-10 p-0 hover:bg-accent"
        @click="incrementHours"
      >
        <ChevronUp class="h-4 w-4" />
      </Button>
      <input
        v-model.number="hoursDisplay"
        type="number"
        min="0"
        max="23"
        class="w-14 text-center text-xl font-semibold bg-background border border-input rounded-md px-2 py-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [-moz-appearance:textfield]"
        @click="handleInputClick"
        @focus="handleInputFocus"
        @mousedown.stop
      />
      <Button
        type="button"
        variant="ghost"
        size="sm"
        class="h-7 w-10 p-0 hover:bg-accent"
        @click="decrementHours"
      >
        <ChevronDown class="h-4 w-4" />
      </Button>
    </div>

    <!-- Separator -->
    <div class="text-xl font-semibold text-muted-foreground pb-6">:</div>

    <!-- Minutes -->
    <div class="flex flex-col items-center gap-2">
      <Button
        type="button"
        variant="ghost"
        size="sm"
        class="h-7 w-10 p-0 hover:bg-accent"
        @click="incrementMinutes"
      >
        <ChevronUp class="h-4 w-4" />
      </Button>
      <input
        v-model.number="minutesDisplay"
        type="number"
        min="0"
        max="59"
        class="w-14 text-center text-xl font-semibold bg-background border border-input rounded-md px-2 py-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [-moz-appearance:textfield]"
        @click="handleInputClick"
        @focus="handleInputFocus"
        @mousedown.stop
      />
      <Button
        type="button"
        variant="ghost"
        size="sm"
        class="h-7 w-10 p-0 hover:bg-accent"
        @click="decrementMinutes"
      >
        <ChevronDown class="h-4 w-4" />
      </Button>
    </div>
  </div>
</template>

