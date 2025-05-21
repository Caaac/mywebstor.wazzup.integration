import { defineStore } from 'pinia'
import { helperStore } from './helper'
import { settingsStore } from './settings'
import { firstPageStore } from './first-page'

import { ref } from 'vue'

export const rootStore = defineStore('root', () => {

  const loading = ref(false)  
  
  const init = async () => {
    await settings().init();
  }

  const firstPage = () => {
    return firstPageStore()
  }

  const settings = () => {
    return settingsStore()
  }
  
  const helper = () => {
    return helperStore()
  }

  return { loading, init, firstPage, settings, helper }
})

