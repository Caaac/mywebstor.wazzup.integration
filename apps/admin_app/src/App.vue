<script setup>
/* PrimeVue components */
import Toast from 'primevue/toast';
/* Pinia store */
import { rootStore } from '@/stores/index'
/* Router */
import { RouterView, useRouter } from 'vue-router'
/* Vue v3 */
import { onMounted } from 'vue';

const router = useRouter()
const store = rootStore()

onMounted(() => {
  setTimeout(() => {
    let pToast = document.querySelector('.p-toast')
    if (!pToast) return
    pToast.style.top = '60px'
  }, 500);
})

</script>

<template>
  <RouterView />
  <Toast />
</template>

<style></style>
