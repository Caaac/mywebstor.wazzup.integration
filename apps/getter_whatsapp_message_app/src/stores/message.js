import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { rootStore } from '.'

export const messageStore = defineStore('message', () => {

  const store = rootStore()

  const params = ref({
    chanels: [],
    templates: [],
    activityProperties: { WhatsappMessageTemplateGUID: null },
    selectedTemplate: null,
    variables: {
      header: {},
      body: {},
      buttons: {},
      test: ''
    }
  })

  const init = async () => {
    const cmd = {}

    cmd['messageTemplates'] = ['mwi.wazzup.getMessageTemplates', {
      filter: {
        status: 'approved'
      }
    }]

    cmd['avaliableChanels'] = ['mwi.wazzup.chanels.get', {
      filter: {
        state: 'active',
        transport: 'wapi',
      }
    }]

    cmd['activityProperties'] = ['mwi.activity.settings.get', {
      activityName: store.activityId,
      select: ['ReservePhone', 'WhatsappMessageTemplateGUID', 'WhatsappMessageBodyValues', 'WhatsappChannelId']
      // select: ['WhatsappMessageTemplateGUID', 'WhatsappMessageTemplateCode', 'WhatsappMessageBodyValues', 'WhatsappChannelId]
    }]

    return new Promise((resolve, reject) => {

      BX.rest.callBatch(
        cmd,
        (responce) => {

          /** Search for errors */
          Object.keys(responce).forEach(key => {
            if (responce[key].error()) {
              reject(responce[key].error())
            }
          });

          params.value.chanels = responce.avaliableChanels.data();
          params.value.templates = responce.messageTemplates.data();
          params.value.activityProperties = responce.activityProperties.data();

          setTemplate();
          resolve(responce)
        }
      );

    })
  }

  const save = async () => {
    BX.rest.callMethod(
      "mwi.activity.settings.update",
      {
        activityName: store.activityId,
        activityProperties: {
          ReservePhone: params.value.activityProperties.ReservePhone,
          WhatsappMessageTemplateGUID: params.value.selectedTemplate.templateGuid,
          // WhatsappMessageTemplateCode: params.value.selectedTemplate.templateCode,
          WhatsappMessageBodyValues: JSON.stringify(params.value.variables.body),
          WhatsappChannelId: params.value.activityProperties.WhatsappChannelId
        }
      },
      response => {
        console.log(response);
      }
    )
  }

  const setTemplate = () => {
    const template = params.value.templates.filter(
      template => template.templateGuid == params.value.activityProperties.WhatsappMessageTemplateGUID
    )[0]

    if (template) {
      params.value.selectedTemplate = JSON.parse(JSON.stringify(template));
    }

  }

  watch(
    () => params.value.activityProperties.WhatsappMessageTemplateGUID,
    (n, o) => {
      if (n == null) {
        params.value.selectedTemplate = null;
        return;
      }

      setTemplate();
    })

  return { init, save, params, setTemplate }
})
