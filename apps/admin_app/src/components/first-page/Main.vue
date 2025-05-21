<script setup>
/* PrimeVue components */
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InlineMessage from 'primevue/inlinemessage';
/* Custom componenst */
/* Pinia store */
import { storeToRefs } from 'pinia'
import { rootStore } from '@/stores/index';
/* Vue v3 */
import { computed, onMounted, ref, watch } from 'vue';

const store = rootStore()

const { params } = storeToRefs(store.settings())

</script>

<template>
  <div id="main-tab">

    <InlineMessage v-if="!params.API_KEY" severity="warn" class="w-full">Введите ключ API</InlineMessage>
    <InlineMessage v-else-if="params.API_KEY && params.SUBSCRIPTIONS.length == 0" severity="error" class="w-full">Введен некорректный ключ API</InlineMessage>
    <DataTable v-else :value="params.SUBSCRIPTIONS" tableStyle="min-width: 50rem">
      <Column field="TITLE" header="Наименование подписки"></Column>
      <Column field="quantity" header="Активна">
        <template #body="slotProps">
          {{ slotProps.data.VALUE ? "Да" : "Нет" }}
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style>
.p-inline-message.w-full {
  width: 100% !important;
}
</style>