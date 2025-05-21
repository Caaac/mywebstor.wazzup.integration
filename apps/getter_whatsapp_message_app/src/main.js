import "primevue/resources/themes/aura-light-teal/theme.css";
import "primevue/resources/primevue.min.css";
import 'primeicons/primeicons.css'
import '@/assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'

/* PrimeVue */
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice'
import ru from 'primelocale/ru.json';

import '@/mixins'

const app = createApp(App)

app
  .use(PrimeVue, { locale: ru.ru })
  .use(ConfirmationService)
  .use(ToastService)
  .use(createPinia())
  .directive('tooltip', Tooltip)

BX.ready(function () {
  app.mount('#app')
})
