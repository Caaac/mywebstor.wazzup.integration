import { defineStore } from 'pinia'
import { rootStore } from './index'
import { ref, watch } from 'vue'
import moment from 'moment';

export const autosendsettingsStore = defineStore('autosendsettings', () => {
  const store = rootStore()

  const selectedAppointments = ref([])
  const filter = ref({
    DOCTORS: [],
  })
  const params = ref({
    SETTINGS: {},
    DATE_CURR_TIMEZONE: null,
    DOCTORS: [],
    DISABLED_DOCTORS: []
  })

  const init = async (loading = true) => {
    store.loading.newsletter = loading;

    params.value.APPOINTMENT_DATE = moment().add(1, 'day').toDate();

    return new Promise((resolve, reject) => {
      reloadData()
        .then(res => { resolve(res) })
        .catch(err => { reject(err) })
    })
  }

  const reloadData = async (loading = true) => {
    store.loading.autosendsettings = loading;

    return new Promise((resolve, reject) => {

      const cmd = {}

      cmd["settings"] = ['mwi.autosendsettings.get', {}]
      cmd["doctors"] = ['mwi.hms.doctor.list', {}]

      BX.rest.callBatch(
        cmd,
        (responce) => {
          Object.keys(responce).forEach(key => {
            if (responce[key].error()) {
              store.helper().errorToast(responce[key].answer.error.error_description)
              reject(responce[key].error())
            }
          });

          params.value.DOCTORS = responce.doctors.data();
          params.value.SETTINGS = responce.settings.data() || {};
          params.value.SETTINGS.DIFF = +params.value.SETTINGS.DIFF
          params.value.DATE_CURR_TIMEZONE = toMSKTimeZone(new Date(params.value.SETTINGS.DATE))

          params.value.DOCTORS.map(doctor => {
            doctor.FULL_NAME = `${doctor.LAST_NAME} ${doctor.NAME} ${doctor.SECOND_NAME} `
          });

          params.value.DOCTORS.sort((a, b) => a.FULL_NAME.localeCompare(b.FULL_NAME));

          params.value.DISABLED_DOCTORS = params.value.DOCTORS.filter(doctor => params.value.SETTINGS.DISABLED_DOCTORS.includes(doctor.ID));

          store.loading.autosendsettings = false;
          resolve(responce)
        }
      )
    })
  }

  const saveSettings = () => {
    params.value.SETTINGS.DATE = copyDate(params.value.DATE_CURR_TIMEZONE).addHours(-1 * getOffsetDiff()).toISOString();

    return new Promise((resolve, reject) => {
      BX.rest.callMethod(
        "mwi.autosendsettings.set",
        params.value.SETTINGS,
        (responce) => {
          if (responce.error()) {
            store.helper().errorToast(responce.answer.error.error_description)
            reject(responce.error())
          }
          resolve(responce)
        }
      )
    })
  }

  const getOffsetDiff = () => {
    const currentDate = new Date()
    const currentOffset = currentDate.getTimezoneOffset() / -60;
    const targetOffset = 3; // MSK
    const offsetDifference = targetOffset - currentOffset;
    return offsetDifference
  }


  const toMSKTimeZone = (currentDate) => {
    const offsetDifference = getOffsetDiff()
    const newTime = new Date(currentDate.getTime() + offsetDifference * 60 * 60 * 1000);
    return newTime
  }

  const copyDate = (date) => new Date(JSON.parse(JSON.stringify(date)))

  watch(
    () => params.value.DISABLED_DOCTORS,
    (n, o) => {
      params.value.SETTINGS.DISABLED_DOCTORS = n.map(doctor => doctor.ID);
      store.autosendsettings().saveSettings()
    }
  )

  return { init, params, filter, selectedAppointments, reloadData, toMSKTimeZone, saveSettings }
})

