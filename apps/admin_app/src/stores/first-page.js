import { defineStore } from 'pinia'
import { rootStore } from './index'
import { ref } from 'vue'

export const firstPageStore = defineStore('firstPage', () => {
  const store = rootStore()

  const params = ref({})

  const init = async () => {}

  return { init, params }
})

