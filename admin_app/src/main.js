import './assets/main.css'
import 'primevue/resources/themes/aura-light-blue/theme.css'
import 'primeicons/primeicons.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import ru from 'primelocale/ru.json';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

import './mixins/index'

const app = createApp(App)

app.use(createPinia())
app.use(PrimeVue, {locale: ru.ru})
app.use(ToastService)
app.use(ConfirmationService)
app.use(router)

app.mount('#app')
