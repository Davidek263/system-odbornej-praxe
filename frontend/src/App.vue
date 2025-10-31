<template>
  <div id="app" class="flex flex-col min-h-screen">
    <!-- Header sa zobrazuje vždy -->
    <HeaderComponent />
    <ScrollTopComponent />
    <main class="flex-grow">
      <router-view />
    </main>

  </div>
</template>

<script setup>
import HeaderComponent from './components/Header.vue'
import ScrollTopComponent from './components/ScrollTop.vue'
</script>

<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
}
</style>
