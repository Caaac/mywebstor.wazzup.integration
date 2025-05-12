<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$aMenu[] = [
    "parent_menu" => "global_menu_mywebstor",
    "sort" => 1900,
    "text" => Loc::getMessage('MYWEBSTOR_WAZZUP_INTEGRATION_MENU_TEXT'),
    "title" => Loc::getMessage('MYWEBSTOR_WAZZUP_INTEGRATION_MENU_TITLE'),
    "url" => BX_ROOT . '/admin/mywebstor_wazzup_integration.php?lang=' . LANGUAGE_ID,
    "icon" => "util_menu_icon",
    "page_icon" => "util_page_icon"
];

return  $aMenu;