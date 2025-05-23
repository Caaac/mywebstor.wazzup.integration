<?php

namespace Mywebstor\Wazzup\Integration;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

class ActivitySettingsTable extends DataManager
{
  public static function getTableName()
  {
    return 'mwi_activity_settings';
  }

  public static function getMap()
  {
    return [
      /* Table fields */
      'ID' => (new IntegerField('ID'))
        ->configurePrimary()
        ->configureAutocomplete()
        ->configureTitle(Loc::getMessage('MWI_ID_FIELD')),

      'ACTIVITY_ID' => (new StringField('ACTIVITY_ID'))
        ->configureRequired()
        ->configureTitle(Loc::getMessage('MWI_ACTIVITY_ID_FIELD')),

      'MESSAGE_TEMPLATE_ID' => (new StringField('MESSAGE_TEMPLATE_ID'))
        ->configureRequired()
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_MESSAGE_TEMPLATE_ID_FIELD')),
    ];
  }
}
