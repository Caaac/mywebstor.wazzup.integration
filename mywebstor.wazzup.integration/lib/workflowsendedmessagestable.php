<?php

namespace Mywebstor\Wazzup\Integration;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\ORM\Data\DataManager;

use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;

Loc::loadMessages(__FILE__);

class WorkflowSendedMessagesTable extends DataManager
{
  public static function getTableName()
  {
    return 'mwi_bp_workflow_sended_messages';
  }

  public static function getMap()
  {
    return [
      /**
       * Table fields 
       */
      'ID' => (new IntegerField('ID'))
        ->configurePrimary()
        ->configureAutocomplete()
        ->configureTitle(Loc::getMessage('MWI_ID_FIELD')),

      'WORKFLOW_ID' => (new StringField('WORKFLOW_ID'))
        ->configureRequired()
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_WORKFLOW_ID_FIELD')),

      'ACTIVITY_NAME' => (new StringField('ACTIVITY_NAME'))
        ->configureRequired()
        ->configureSize(60)
        ->configureTitle(Loc::getMessage('MWI_ACTIVITY_NAME_FIELD')),

      'MESSAGE_TEMPLATE_ID' => (new StringField('MESSAGE_TEMPLATE_ID'))
        ->configureRequired()
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_MESSAGE_TEMPLATE_ID_FIELD')),

      'SENDER_ID' => (new IntegerField('SENDER_ID'))
        ->configureRequired()
        ->configureTitle(Loc::getMessage('MWI_SENDER_ID_FIELD')),

      'CHANEL_ID' => (new StringField('CHANEL_ID'))
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_CHANEL_ID_FIELD')),

      'CHAT_ID' => (new StringField('CHAT_ID'))
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_CHAT_ID_FIELD')),

      'ANSWERED_MESSAGE' => (new TextField('ANSWERED_MESSAGE'))
        ->configureTitle(Loc::getMessage('MWI_ANSWERED_MESSAGE_FIELD')),

      'MESSAGE_ID' => (new StringField('MESSAGE_ID'))
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_MESSAGE_ID_FIELD')),

      'DATE_SEND' => (new DatetimeField('DATE_SEND'))
        ->configureRequired()
        ->configureDefaultValue(new \Bitrix\Main\Type\DateTime())
        // ->configureDefaultValueNow()
        ->configureTitle(Loc::getMessage('MWI_DATE_SEND_FIELD')),

      'DATE_ANSWER' => (new DatetimeField('DATE_ANSWER'))
        ->configureTitle(Loc::getMessage('MWI_DATE_ANSWER_FIELD')),

        /**
         * References
         */
    ];
  }
}
