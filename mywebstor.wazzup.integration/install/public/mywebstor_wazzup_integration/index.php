<?
set_time_limit(0);
ini_set("memory_limit", -1);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent("mywebstor.wazzup.integration", "", []);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
