import { defineStore } from 'pinia'
import { rootStore } from './index'
import { ref } from 'vue'

export const settingsStore = defineStore('settings', () => {
  const store = rootStore()

  const hasChanges = ref(true)
  const params = ref({
    API_KEY: "",
    WEBHOOK_URL: "",
    SUBSCRIPTIONS: [],
  })

  const init = async (loading = true) => {
    store.loading.settings = loading;
    return new Promise((resolve, reject) => {

      const cmd = {}

      cmd["integrationState"] = ['mwi.integration.get', {}]
      cmd["apiKey"] = ['mwi.settings.api_key.get', {}]

      BX.rest.callBatch(
        cmd,
        (responce) => {

          console.log(responce);
          

          Object.keys(responce).forEach(key => {
            if (responce[key].error()) {
              store.helper().errorToast(responce[key].answer.error.error_description)
              reject(responce[key].error())
            }
          });

          const integrationState = responce.integrationState.data();

          if (integrationState == -1) {
            params.value.SUBSCRIPTIONS = [];
          } else {
            setSubscription()
            Object?.keys(integrationState?.subscriptions || []).forEach(key => {
              params.value.SUBSCRIPTIONS.find(s => s.KEY == key).VALUE = integrationState.subscriptions[key];
            })
            
            params.value.WEBHOOK_URL = integrationState.webhooksUri;
          }

          params.value.API_KEY = responce.apiKey.data();

          store.loading.settings = false;
          hasChanges.value = false
          resolve(true)
        }
      )
    })
  }


  const save = async (reload = false, loading = true) => {
    store.loading.settings = loading;
    return new Promise((resolve, reject) => {

      const cmd = {}

      /* Порядок важен */
      cmd["apiKey"] = ['mwi.settings.api_key.set', { apiKey: params.value.API_KEY }]
      cmd["integrate"] = ['mwi.integration.set', {}]

      params.value.SUBSCRIPTIONS.forEach(element => {
        cmd["integrate"][1][element.KEY] = element.VALUE
      });

      console.log(cmd);

      resolve()

      BX.rest.callBatch(
        cmd,
        async (responce) => {
          Object.keys(responce).forEach(key => {
            if (responce[key].error()) {
              store.helper().errorToast(responce[key].error())
              reject(responce[key].error())
            }
          });

          store.loading.settings = false;
          hasChanges.value = false;

          if (reload) await init();

          resolve(true)
        }
      )
    })
  }

  const setSubscription = () => {
    const subscription = [
      {
        VALUE: false,
        KEY: 'channelsUpdates',
        TITLE: 'Вебхук об изменении статуса канала.',
      },
      {
        VALUE: false,
        KEY: 'contactsAndDealsCreation',
        TITLE: 'Вебхук о том, что нужно создать новый контакт или сделку.',
      },
      {
        VALUE: false,
        KEY: 'messagesAndStatuses',
        TITLE: 'Вебхук о новых сообщениях и вебхук об изменении статуса исходящих.',
      },
      {
        VALUE: false,
        KEY: 'wabaTemplatesStatus',
        TITLE: 'Вебхук об изменении статуса модерации шаблона WABA.',
      },
    ]

    params.value.SUBSCRIPTIONS = subscription.deepCopy()
  }

  return { init, save, params, hasChanges }
})

