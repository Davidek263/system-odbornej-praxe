<template>
  <!-- Invisible wrapper that simply triggers scroll on mount -->
  <span style="display:none" />
</template>

<script>
export default {
  name: 'ScrollTop',
  mounted() {
    // always posuň viewport na vrch po načítaní komponentu
    window.scrollTo({ top: 0, left: 0 })
  }
}
</script>
