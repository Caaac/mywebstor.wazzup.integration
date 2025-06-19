<script setup>
import Button from 'primevue/button';
import Column from "primevue/column";
import Calendar from 'primevue/calendar';
import Dropdown from "primevue/dropdown";
import DataTable from "primevue/datatable";
import MultiSelect from 'primevue/multiselect';
/* Pinia store */
import { rootStore } from "@/stores";
import { computed, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";

import moment from 'moment';

const store = rootStore();

const { params, filter, selectedAppointments } = storeToRefs(store.newsletter());

const invalid = ref({
  SELECTED_BIZ_PROC: false
})

onMounted(() => {
  store.newsletter().init(true);
});

const appointments = computed(() => {
  return (params.value?.APPOINTMENTS || []).filter(item => {
    return !filter.value.DOCTORS.length || filter.value.DOCTORS.includes(String(item.DOCKTOR_ID))
  })
})

const workflowStart = async () => {
  if (!params.value.SELECTED_BIZ_PROC) {
    invalid.value.SELECTED_BIZ_PROC = true
    return
  }

  if (!selectedAppointments.value.length) {
    store.helper().warnToast('Выберите записи на прием')
    return
  }

  invalid.value.SELECTED_BIZ_PROC = false

  try {
    await store.newsletter().saveAppSettings()
    await store.newsletter().workflowStart()
  } catch (error) {
    console.error(error);
  }
}

</script>

<template>
  <div id="newsletter-tab">
    <div class="newsletter-header">
      <div>
        <Dropdown v-model="params.SELECTED_BIZ_PROC" :options="params.BIZ_PROC_LIST"
          :invalid="invalid.SELECTED_BIZ_PROC" optionLabel="NAME" optionValue="ID" filter
          placeholder="Выберите бизнес-процесс" style="width: 300px" />
        <MultiSelect v-model="filter.DOCTORS" :options="params.DOCTORS" optionLabel="FULL_NAME" optionValue="ID" filter
          placeholder="Выберите врача" style="width: 300px; margin-left: 20px" />
        <Calendar v-model="params.APPOINTMENT_DATE" dateFormat="dd.mm.yy" style="width: 300px; margin-left: 20px">
          <template #footer>
            <Button @click="store.newsletter().reloadData()" label="Применить" />
          </template>
        </Calendar>
      </div>
      <Button @click="workflowStart" label="Запустить бизнес процессы" />
    </div>

    <DataTable v-model:selection="selectedAppointments" :value="appointments" dataKey="ID"
      tableStyle="min-width: 50rem">
      <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
      <Column field="ID" header="ID записи на прием"></Column>

      <Column header="ФИО врача">
        <template #body="slotProps">
          {{ slotProps.data.DOCKTOR_LAST_NAME + ' ' + slotProps.data.DOCKTOR_NAME + ' ' +
            slotProps.data.DOCKTOR_SECOND_NAME }}
        </template>
      </Column>

      <Column field="CONTACT_FULL_NAME" header="ФИО пациента"></Column>
      <Column field="STATUS_NAME" header="Статус"></Column>

      <Column header="Дата создания записи">
        <template #body="slotProps">
          {{ moment(slotProps.data.DATE_CREATE).format('DD.MM.YYYY HH:mm:ss') }}
        </template>
      </Column>

      <Column header="Дата приема">
        <template #body="slotProps">
          {{ moment(slotProps.data.DATE_FROM).format('DD.MM.YYYY HH:mm:ss') }}
        </template>
      </Column>
    </DataTable>

  </div>
</template>

<style>
.newsletter-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}
</style>
