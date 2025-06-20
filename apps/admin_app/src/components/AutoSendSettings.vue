<script setup>
import Button from 'primevue/button';
import Column from "primevue/column";
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import DataTable from "primevue/datatable";
import InputSwitch from 'primevue/inputswitch';

import { rootStore } from '@/stores';
import { ref } from "vue";
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';

const store = rootStore();
const { params } = storeToRefs(store.autosendsettings());

const diffs = ref([
  { title: '1 день', value: 1 },
  // { title: '2 дня', value: 2 },
  // { title: '3 дня', value: 3 },
]);

onMounted(() => {
  store.autosendsettings().init();
})

</script>

<template>
  <div id="auto-send-tab">
    <h2 style="margin: 0; margin-left: 10px">Исключить сотрудников из рассылки</h2>
    <div class="auto-send-container">
      <div class="disabled-docktors">
        <div class="disabled-docktors-container">
          <DataTable v-model:selection="params.DISABLED_DOCTORS" :value="params.DOCTORS" dataKey="ID" selec scrollable
            scrollHeight="500px">
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
            <Column header="ФИО врача">
              <template #body="slotProps">
                {{ slotProps.data.LAST_NAME + ' ' + slotProps.data.NAME + ' ' + slotProps.data.SECOND_NAME }}
              </template>
            </Column>
          </DataTable>
        </div>
      </div>
      <div class="auto-send-settings">
        <div class="auto-send-settings-container">
          <div class="settings-block-wrapper">
            <div class="settings-block">
              <div class="settings-item row a-center">
                <b>Активность</b>
                <InputSwitch v-model="params.SETTINGS.ACTIVE" @change="store.autosendsettings().saveSettings()"
                  true-value="Y" false-value="N" style="margin-left: 20px" />
              </div>
              <div class="settings-item">
                <b style="margin-bottom: 3px">Время рассылки (мск)</b>
                <Calendar id="calendar-timeonly" v-model="params.DATE_CURR_TIMEZONE" timeOnly inline
                  :disabled="params.SETTINGS.ACTIVE == 'Y'" />
              </div>
              <div class="settings-item">
                <b style="margin-bottom: 3px">Предупредить за</b>
                <Dropdown v-model="params.SETTINGS.DIFF" :options="diffs" option-label="title" option-value="value"
                  placeholder="выберите" style="width: 100%" :disabled="params.SETTINGS.ACTIVE == 'Y'" />
              </div>
              <!-- <div class="settings-item">
                <Button @click="store.autosendsettings().saveSettings()" label="Сохранить" severity="_success" />
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
#auto-send-tab .auto-send-container {
  display: flex;
  flex-direction: row;
}

#auto-send-tab .disabled-docktors {
  width: 100%;
}

#auto-send-tab .auto-send-settings {
  width: 450px;
}

#auto-send-tab .disabled-docktors-container,
#auto-send-tab .auto-send-settings-container {
  padding: 20px;
}

#auto-send-tab .disabled-docktors-container {
  padding-left: 0;
}

#auto-send-tab .auto-send-settings-container {
  padding-right: 0;
}

#auto-send-tab .settings-block-wrapper {
  border-radius: 16px;
  background-color: rgb(221, 243, 254);
}

#auto-send-tab .settings-block {
  display: flex;
  flex-direction: column;
  padding: 20px 20px;
}

#auto-send-tab .settings-item {
  display: flex;
  flex-direction: column;
  margin-bottom: 10px;
}

#auto-send-tab .settings-item.row {
  flex-direction: row;
}

#auto-send-tab .settings-item.a-center {
  align-items: center;
}

#auto-send-tab .settings-item.j-center {
  justify-content: center;
}

#auto-send-tab .settings-item:last-child {
  margin-bottom: 0;
}

/* 
#auto-send-tab
*/
</style>