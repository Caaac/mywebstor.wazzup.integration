<?

/* Class loader */
\Bitrix\Main\Loader::registerAutoLoadClasses(
  "mywebstor.wazzup.integration",
  [
    "CMywebstorWazzupIntegrationEvents" => "classes/general/events.php",
    "CMywebstorWazzupIntegrationRestService" => "classes/general/restservice.php",
  ]
);

/* Include Modules */
$modules = array(
  "crm",
);

foreach ($modules as $module) {
  if (!\Bitrix\Main\Loader::includeModule($module)) {
      ShowError("Module \"{$module}\" not found.");
      return false;
  }
}



