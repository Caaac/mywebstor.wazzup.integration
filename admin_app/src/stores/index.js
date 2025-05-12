import { defineStore } from 'pinia'
import { settingsStore } from './settings'

export const rootStore = defineStore('root', () => {

  const settings = () => {
    return settingsStore()
  }

  return { settings }
})

