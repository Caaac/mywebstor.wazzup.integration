<script setup>
/** PrimeVue components */
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import FloatLabel from 'primevue/floatlabel';
/* Pinia store */
import { storeToRefs } from 'pinia'
import { rootStore } from '@/stores/index'
/* Vue */
import { onMounted, ref, computed, watch } from 'vue';

const store = rootStore()

const { params } = storeToRefs(store.message())

/** @var {Proxy(Array)} matrix Матрица с индексами переменной в каждой строке */
const matrix = ref([])

onMounted(() => {
  store.init()
})

const splitedBodyMessage = computed(() => {
  matrix.value = []
  params.value.variables.body = {}

  const body = params.value.selectedTemplate?.components.find(component => component.type == 'BODY') || { text: '' }
  const splitByRows = body.text.split('\n')

  const result = splitByRows.reduce((acc, row) => {
    /** 
     * Каким-то чудесным образом если regex подставлять сразу в split
     * то он оставляет цифры из шаблонов в разбитом тексте,
     * Хотя если проделывать тоже самое в консоли то все хорошо работает
     */
    const regex = /{{(\d+)}}/g;
    const replaced = '/*^*/'
    row = row.replace(regex, replaced);
    row = row.split(replaced);
    acc.push(row)

    return acc
  }, [])

  return result
})

const save = () => {
  store.message().save().then(() => {
    store.helper().successToast('Сохранено')
  })
    .catch(error => {
      store.helper().errorToast(error)
    })
}

watch(splitedBodyMessage, (newValue) => {
  const maxLen = Math.max(...newValue.map(row => row.length))
  matrix.value = Array.from({ length: newValue.length }, () => [])

  let bodyVal = {}

  if (params.value.activityProperties && params.value.activityProperties['WhatsappMessageBodyValues']) {
    let actProp = JSON.parse(params.value.activityProperties['WhatsappMessageBodyValues']);

    Object.keys(actProp || {}).forEach(key => {
      bodyVal[key] = actProp[key]
    });

  }

  newValue.forEach((row, rowIndex) => {

    matrix.value[rowIndex] = Array.from({ length: maxLen }, () =>
      rowIndex != 0 ? matrix.value[rowIndex - 1][maxLen - 1] + row.length - 1 : row.length - 1
    )

    row.forEach((element, elementIndex) => {
      if (row.length - 1 != elementIndex) {
        matrix.value[rowIndex][elementIndex] -= (row.length - 1 - elementIndex - 1)
        params.value.variables.body[
          `[[bodyVar${matrix.value[rowIndex][elementIndex]}]]`
        ] = bodyVal[`[[bodyVar${matrix.value[rowIndex][elementIndex]}]]`] || ''
      }

    });
  });
});

</script>

<template>
  <div id="settings-menu">
    <div class="settings-menu-container">

      <div class="settings-menu-item">
        <FloatLabel>
          <Dropdown v-model="params.activityProperties.WhatsappChannelId" :options="params.chanels" id="waba-chanel"
            optionLabel="name" optionValue="channelId" placeholder="Выберете отправителя" class="input-field" />
          <label for="waba-chanel">От кого писать</label>
        </FloatLabel>
      </div>

      <div class="settings-menu-item item-margin">
        <FloatLabel>
          <Dropdown v-model="params.activityProperties.WhatsappMessageTemplateGUID" :options="params.templates"
            id="waba-tmpl" optionLabel="title" optionValue="templateGuid" placeholder="Выберете шаблон сообщения"
            class="input-field" />
          <label for="waba-tmpl">Шаблон сообщения</label>
        </FloatLabel>
      </div>

      <div class="settings-menu-item item-margin">
        <FloatLabel>
          <InputText v-model="params.activityProperties.ReservePhone" id="waba-reserve-phone"
            placeholder="Выберете телефон по умолчанию" class="input-field" />
          <label for="waba-reserve-phone">Номер телефона (если не получится определить)</label>
        </FloatLabel>
      </div>

      <div class="settings-menu-item">
        <div class="message-template-wrapper">
          <div v-for="(component, index) in params.selectedTemplate?.components || []" :key="component.type">
            <template v-if="component.type == 'BODY'">
              <div class="message-template-body">
                <div class="message-template-body-container">
                  <template v-for="(row, index) in splitedBodyMessage" :key="index">
                    <p>
                      <template v-for="(text, jndex) in row" :key="jndex">
                        {{ text }}
                        <InputText v-if="jndex + 1 != row.length"
                          v-model="params.variables.body[`[[bodyVar${matrix[index][jndex]}]]`]"
                          @update:model-value="(value) => { params.variables.body[`[[bodyVar${matrix[index][jndex]}]]`] = value; }" />
                      </template>
                    </p>
                  </template>
                </div>
              </div>
            </template>
            <template v-else-if="component.type == 'BUTTONS'">
              <div class="message-template-button-container">
                <div v-for="button in component.buttons" :key="button" class="btn-item">
                  {{ button.text }}
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

    </div>

    <footer>
      <Button v-if="params.selectedTemplate" @click="save" label="Сохранить" />
    </footer>
  </div>

  <Toast />
</template>

<style>
#settings-menu {
  padding: 10px 20px;
}

#settings-menu .settings-menu-container {
  margin-top: 20px;
}

#settings-menu .settings-menu-container .settings-menu-item {
  margin-bottom: 20px;
}

#settings-menu .settings-menu-container .settings-menu-item.item-margin {
  margin-top: 30px;
}

#settings-menu .settings-menu-container .settings-menu-item .input-field {
  width: 400px;
}

#settings-menu .message-template-wrapper .message-template-body {
  background-color: var(--color-main);
  border-radius: 16px;
}

#settings-menu .message-template-body .message-template-body-container {
  padding: 10px 10px;
}

#settings-menu .message-template-wrapper .message-template-button-container {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

#settings-menu .message-template-wrapper .message-template-button-container .btn-item {
  padding: 10px;
  background-color: var(--color-main);
  border-radius: 16px;
}

footer {
  margin-top: 20px;
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
}

footer .p-button {
  width: 100%;
}
</style>
