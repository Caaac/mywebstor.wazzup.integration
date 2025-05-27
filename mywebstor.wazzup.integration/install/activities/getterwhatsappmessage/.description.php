<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
  "NAME" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_NAME"),
  "DESCRIPTION" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_DESCR"),
  "TYPE" => "activity",
  "CLASS" => "GetterWhatsappMessage",
  "JSCLASS" => "BizProcActivity",
  "CATEGORY" => [
    "ID" => "other",
    'OWN_ID' => 'mywebstor',
    'OWN_NAME' => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_OWN_NAME"),
  ],
  "RETURN" => [
    "ANSWERED_MESSAGE" => [
      "NAME" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_FIELD_ANSWERED_MESSAGE"),
      "TYPE" => "string",
    ],
    "ANSWERED_MESSAGE_ID" => [
      "NAME" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_FIELD_ANSWERED_MESSAGE_ID"),
      "TYPE" => "string",
    ],
    "STATUS" => [
      "NAME" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_FIELD_STATUS"),
      "TYPE" => "string",
    ],
    "STATUS_CODE" => [
      "NAME" => Loc::getMessage("MWI_GETTERWHATSAPPMESSAGE_DESCR_FIELD_STATUS_CODE"),
      "TYPE" => "int",
    ],
  ],
];
