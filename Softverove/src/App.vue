<template>
  <div id="app" class="flex flex-col min-h-screen">
    <!-- Header sa zobrazuje vždy -->
    <HeaderComponent />

    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Footer sa zobrazuje vždy -->
    <FooterComponent />
  </div>
</template>

<script setup>
import HeaderComponent from './components/Header.vue'
</script>

<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
}
</style>
