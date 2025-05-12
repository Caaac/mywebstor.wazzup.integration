<script setup>

import Toast from 'primevue/toast';

import { rootStore } from '@/stores/index'

import { RouterView, useRouter } from 'vue-router'
import { onMounted } from 'vue';

const router = useRouter()
const store = rootStore()

onMounted(() => {
})

</script>

<template>
  <Toast />
</template>

<style>

</style>
