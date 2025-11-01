<template>
  <!-- Invisible wrapper that simply triggers scroll on route change -->
  <span style="display:none" />
</template>

<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Scroll to top whenever the route changes
watch(
  () => route.path,
  () => {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
  },
  { immediate: true } // Also trigger on component mount
)
</script>