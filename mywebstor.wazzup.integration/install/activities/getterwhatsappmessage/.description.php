<? 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
  "NAME" => Loc::getMessage("GETTERWHATSAPPMESSAGE_DESCR_NAME"),
  "DESCRIPTION" => Loc::getMessage("GETTERWHATSAPPMESSAGE_DESCR_DESCR"),
  "TYPE" => "activity",
  "CLASS" => "GetterWhatsappMessage",
  "JSCLASS" => "BizProcActivity",
  "CATEGORY" => [
    "ID" => "other",
  ],
  "RETURN" => [
    "CLIENT_MESSAGE" => [
      "NAME" => Loc::getMessage("GETTERWHATSAPPMESSAGE_DESCR_FIELD_CLIENT_MESSAGE"),
      "TYPE" => "string",
    ],
  ],
];
