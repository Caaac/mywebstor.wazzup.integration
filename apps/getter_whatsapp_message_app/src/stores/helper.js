/* Pinia */
import { defineStore } from "pinia";
/* PrimeVue v3 plugins */
import { useToast } from "primevue/usetoast";

export const helperStore = defineStore("helper", () => {
  const toast = useToast();

  /**
   * Toast by PrimeVue v3
   * Start region
   *
   * @param message
   */
  const infoToast = (message) => {
    toast.add({
      severity: "info",
      summary: "Информация",
      detail: message,
      life: 3000,
    });
  };

  const successToast = (message) => {
    toast.add({
      severity: "success",
      summary: "Успешно",
      detail: message,
      life: 3000,
    });
  };

  const warnToast = (message) => {
    toast.add({
      severity: "warn",
      summary: "Внимание",
      detail: message,
      life: 3000,
    });
  };

  const errorToast = (message) => {
    toast.add({
      severity: "error",
      summary: "Ошибка",
      detail: message,
      life: 10000,
    });
  };

  /* Finish region */

  return {
    successToast,
    warnToast,
    infoToast,
    errorToast,
  };
});

