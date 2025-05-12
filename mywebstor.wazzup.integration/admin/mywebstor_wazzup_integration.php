<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройки интегра");
?>
  
<body>
    <div id="app">
      Hello
    </div>
</body>


<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>