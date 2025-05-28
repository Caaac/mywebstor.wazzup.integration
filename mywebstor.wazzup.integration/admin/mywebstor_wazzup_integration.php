<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройки интеграции");
?>

<!-- Prod -->
<iframe 
  src="/local/modules/mywebstor.wazzup.integration/admin/index.php" 
  style="border: 0; width:1300px; height:800px;">
</iframe>

<!-- Dev -->
<!-- <iframe 
  src="/local/modules/mywebstor.wazzup.integration/admin/index.php" 
  style="border: 0; width:1500px; height:900px;">
</iframe> -->

<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>