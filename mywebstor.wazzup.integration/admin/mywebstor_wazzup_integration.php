<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройки интегра");



use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

// $componentPath = $this->GetFolder();
$requers = Application::getInstance()->getContext()->getRequest()->toArray();

if (isset($requers['dev_mode'])) {
  print_r($requers['dev_mode']);
  Asset::getInstance()->addString('<script type="module" src="http://localhost/@vite/client"></script>');
  Asset::getInstance()->addString('<script type="module" src="http://localhost/src/main.js"></script>');
} else {
  // Asset::getInstance()->addString('<script type="module" src="' . $componentPath . '/assets/index.js?' . filemtime(__DIR__ . "/assets/index.js") . '"></script>');
  // Asset::getInstance()->addString('<link href="' . $componentPath . '/assets/index.css?' . filemtime(__DIR__ . "/assets/index.css") . '" rel="stylesheet" />');
}

?>
  
<body>
    <div id="app">
      <!-- Hello -->
    </div>
</body>


<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>