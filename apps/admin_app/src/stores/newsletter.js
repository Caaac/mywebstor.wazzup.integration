import { defineStore } from 'pinia'
import { rootStore } from './index'
import { ref, watch } from 'vue'
import moment from 'moment';

export const newsletterStore = defineStore('newsletter', () => {
  const store = rootStore()

  const selectedAppointments = ref([])
  const filter = ref({
    DOCTORS: [],
  })
  const params = ref({
    APPOINTMENT_DATE: null,
    APPOINTMENTS: [],
    BIZ_PROC_LIST: [],
    SELECTED_BIZ_PROC: null,
    DOCTORS: [],
  })

  const init = async (loading = true) => {
    store.loading.newsletter = loading;

    // params.value.APPOINTMENT_DATE = appointmentDate ? appointmentDate : params.value.APPOINTMENT_DATE;
    params.value.APPOINTMENT_DATE = moment().add(1, 'day').toDate();

    return new Promise((resolve, reject) => {
      reloadData()
        .then(res => { resolve(res) })
        .catch(err => { reject(err) })
    })
  }

  const reloadData = async (loading = true) => {
    store.loading.newsletter = loading;

    return new Promise((resolve, reject) => {

      const cmd = {}

      cmd["appointments"] = ['mwi.hms.appointment.get', {
        filter: {
          appointment_date: moment(params.value.APPOINTMENT_DATE.toISOString()).format('YYYY-MM-DD')
        }
      }]

      cmd["bizProcs"] = ['mwi.bizproc.list', {}]
      cmd["selectedBizProc"] = ['mwi.bizproc.settings.get', {}]
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

          params.value.APPOINTMENTS = responce.appointments.data();
          params.value.BIZ_PROC_LIST = responce.bizProcs.data();
          params.value.SELECTED_BIZ_PROC = responce.selectedBizProc.data();
          params.value.DOCTORS = responce.doctors.data();

          params.value.DOCTORS.map(doctor => {
            doctor.FULL_NAME = `${doctor.LAST_NAME} ${doctor.NAME} ${doctor.SECOND_NAME} `
          });

          store.loading.settings = false;
          resolve(responce)
        }
      )
    })
  }

  const saveBizproc = async (loading = true, stopLoading = false) => {
    store.loading.newsletter = loading;

    return new Promise((resolve, reject) => {
      BX.rest.callMethod(
        'mwi.bizproc.settings.update',
        { bizprocId: params.value.SELECTED_BIZ_PROC },
        (responce) => {
          if (responce.error()) {
            store.helper().errorToast(responce.answer.error.error_description)
            reject(responce.error())
          }
          store.loading.newsletter = !stopLoading;
          resolve(responce)
        }
      )
    })
  }

  const workflowStart = async (loading = true, stopLoading = false) => {
    store.loading.newsletter = loading;

    const appointmentIds = selectedAppointments.value.reduce((acc, item) => {
      acc.push(item.ID)
      return acc
    }, [])

    return new Promise((resolve, reject) => {
      BX.rest.callMethod(
        'mwi.bizproc.workflow.start',
        { appointments: appointmentIds },
        (responce) => {
          if (responce.error()) {
            store.helper().errorToast(responce.answer.error.error_description)
            reject(responce.error())
          }
          store.loading.newsletter = !stopLoading;
          resolve(responce)
        }
      )
    })
  }

  return { init, params, filter, saveBizproc, selectedAppointments, workflowStart, reloadData }
})

