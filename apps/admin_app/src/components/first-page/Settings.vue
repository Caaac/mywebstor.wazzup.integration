<script setup>
/* PrimeVue components */
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import InlineMessage from 'primevue/inlinemessage';
/* Custom componenst */
/* Pinia store */
import { rootStore } from '@/stores/index';
import { storeToRefs } from 'pinia'
/* Router */
import { useRouter } from 'vue-router';
/* Vue v3 */
import { onMounted, ref } from 'vue';

const store = rootStore()
const { params, hasChanges } = storeToRefs(store.settings())

</script>

<template>
  <div id="settings-tab">
    <div class="item-container">
      Ключ API (Wazzup):
      <InputText v-model="params.API_KEY" @update:modelValue="(value) => { hasChanges = true; }" class="__redefine"
        type="text" />
    </div>

    <InlineMessage v-if="!params.API_KEY" severity="warn" class="w-full">Введите ключ API</InlineMessage>
    <InlineMessage v-else-if="params.API_KEY && params.SUBSCRIPTIONS.length == 0" severity="error" class="w-full">
      Введен некорректный ключ API
    </InlineMessage>
    <template v-else>
      <div v-for="subscription in params.SUBSCRIPTIONS" :key="subscription.KEY" class="item-container">
        <Checkbox v-model="subscription.VALUE" @change="(e) => { hasChanges = true; }" :binary="true" />
        {{ subscription.TITLE }}
      </div>
    </template>


  </div>
  <div v-if="hasChanges" class="save-panel">
    <Button @click="store.settings().save(reload = true)" label="Сохранить" />
    <Button @click="store.settings().init()" label="Отменить" style="margin-left: 20px;" />
  </div>
</template>

<style scoped>
.item-container {
  margin-bottom: 20px;
}

.save-panel {
  display: flex;
  justify-content: center;
}
</style>