<script setup lang="ts">
import { PopoverTrigger as PopoverTriggerPrimitive, type PopoverTriggerProps, useForwardProps } from 'reka-ui'

const props = defineProps<PopoverTriggerProps>()

const forwarded = useForwardProps(props)
</script>

<template>
  <PopoverTriggerPrimitive
    data-slot="popover-trigger"
    v-bind="forwarded"
  >
    <slot />
  </PopoverTriggerPrimitive>
</template>

