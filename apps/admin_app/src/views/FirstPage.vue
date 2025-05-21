<script setup>
/* PrimeVue components */
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
/* Custom componenst */
import Main from '@/components/first-page/Main.vue';
import Settings from '@/components/first-page/Settings.vue';
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
  console.log('onMounted');

  await store.init()
})

</script>

<template>
  <div id="first-page">
    <TabView>
      <TabPanel header="Главная">
        <Main />
      </TabPanel>
      <TabPanel header="Настройки">
        <Settings />
      </TabPanel>
    </TabView>
  </div>
</template>

<style>
</style>