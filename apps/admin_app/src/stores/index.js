import { defineStore } from 'pinia'
import { helperStore } from './helper'
import { settingsStore } from './settings'
import { newsletterStore } from './newsletter'
import { autosendsettingsStore } from './autosendsettings'

import { ref } from 'vue'

export const rootStore = defineStore('root', () => {

  const loading = ref({
    'settings': false,
    'newsletter': false,
    'autosendsettings': false,
  })

  const init = async () => {
    await settings().init();
  }

  const helper = () => helperStore()
  const settings = () => settingsStore()
  const newsletter = () => newsletterStore()
  const autosendsettings = () => autosendsettingsStore()

  return {
    /* State */
    loading,
    /* Methods */
    /* Computed */
    /* Store refs */
    helper,
    init,
    settings,
    newsletter,
    autosendsettings
  }
})

