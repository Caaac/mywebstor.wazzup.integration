<script setup>
/* PrimeVue components */
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
/* Custom componenst */
import Main from '@/components/Main.vue';
import Newsletter from '@/components/Newsletter.vue';
import Settings from '@/components/Settings.vue';
/* Pinia store */
import { rootStore } from '@/stores/index';
/* Router */
import { useRouter } from 'vue-router';
/* Vue v3 */
import { onMounted, ref } from 'vue';

const store = rootStore()
// const router = useRouter()
// const confirm = useConfirm();

onMounted(async () => {
  await store.init()
})

</script>

<template>
  <div id="first-page">
    <TabView>
      <TabPanel header="Главная">
        <Main />
      </TabPanel>
      <TabPanel header="Генерация рассылки" contentStyle="overflow-y: auto;">
        <Newsletter />
      </TabPanel>
      <TabPanel header="Настройки">
        <Settings />
      </TabPanel>
    </TabView>
  </div>
</template>

<style>
</style>