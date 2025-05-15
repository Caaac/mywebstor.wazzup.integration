<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CMywebstorWazzupIntegrationEvents
{

  public static function addGroupAtAdminPanel(&$aGlobalMenu, &$aModuleMenu)
  {
    global $USER;

    if ($USER->isAdmin()) {
      $aGlobalMenu['global_menu_mywebstor'] = [
        'menu_id' => 'custom',
        'text' => 'Модули MyWebstor',
        'title' => 'Модули MyWebstor',
        'sort' => 1000,
        'items_id' => 'global_menu_mywebstor',
        'help_section' => 'custom',
        'items' => [],
      ];
    }
  }
}
