/* Pinia */
import { defineStore } from 'pinia'
import { helperStore } from './helper'
import { messageStore } from './message'
/* Vue */
import { ref } from 'vue'

export const rootStore = defineStore('root', () => {

  const activityId = ref(null)

  const init = () => {
    activityId.value = window.parent.location.hash.slice(1)
    // activityId.value = 'A82394_96494_15549_79836';

    message().init()
  }

  const helper = () => helperStore()
  const message = () => messageStore()

  return { activityId, init, helper, message }
})
