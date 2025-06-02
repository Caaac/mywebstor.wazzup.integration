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

  const STATUS_WAIT_ANSWER = 0;
  const STATUS_NOT_ANSWERED = 1;
  const STATUS_ANSWERED = 2;
  const STATUS_ERROR = 3;
  const STATUS_CANCELED = 4;

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

      'CHANEL_ID' => (new StringField('CHANEL_ID'))
        ->configureRequired()
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_CHANEL_ID_FIELD')),

      'CHAT_ID' => (new StringField('CHAT_ID'))
        ->configureRequired()
        ->configureSize(20)
        ->configureTitle(Loc::getMessage('MWI_CHAT_ID_FIELD')),

      'SEND_MESSAGE_ID' => (new StringField('SEND_MESSAGE_ID'))
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_SEND_MESSAGE_ID_FIELD')),

      'MESSAGE_STATUS' => (new IntegerField('MESSAGE_STATUS'))
        ->configureSize(2)
        ->configureTitle(Loc::getMessage('MWI_MESSAGE_STATUS_FIELD')),

      'STATUS' => (new IntegerField('STATUS'))
        ->configureRequired()
        ->configureSize(2)
        ->configureDefaultValue(self::STATUS_WAIT_ANSWER)
        ->configureTitle(Loc::getMessage('MWI_STATUS_FIELD')),

      'ANSWERED_MESSAGE' => (new TextField('ANSWERED_MESSAGE'))
        ->configureTitle(Loc::getMessage('MWI_ANSWERED_MESSAGE_FIELD')),

      'ANSWERED_MESSAGE_ID' => (new StringField('ANSWERED_MESSAGE_ID'))
        ->configureSize(36)
        ->configureTitle(Loc::getMessage('MWI_ANSWERED_MESSAGE_ID_FIELD')),

      'DATE_SEND' => (new DatetimeField('DATE_SEND'))
        ->configureRequired()
        ->configureDefaultValue(new \Bitrix\Main\Type\DateTime())
        // ->configureDefaultValueNow() // TODO почему не работает?
        ->configureTitle(Loc::getMessage('MWI_DATE_SEND_FIELD')),

      'DATE_ANSWER' => (new DatetimeField('DATE_ANSWER'))
        ->configureTitle(Loc::getMessage('MWI_DATE_ANSWER_FIELD')),

      /**
     * References
     */
    ];
  }
}
