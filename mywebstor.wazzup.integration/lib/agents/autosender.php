<?

namespace Mywebstor\Wazzup\Integration\Agents;

/* mywebstor.hms classes */

use \MyWebstor\Hms\AppointmentTable;

/* Bitrix classes */
/* -- Rest --  */
use Bitrix\Rest\RestException;
/* -- Iblock --  */
use Bitrix\Iblock\PropertyTable;
/* -- e.t.c. --  */
use CAgent;
use \Exception;
use \CBPDocument;
use CIBlockElement;
use DateTimeInterface;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Bizproc\WorkflowTemplateTable;

class AutoSender
{

  public static function activateAgent()
  {
    $settings = Option::get('mywebstor.wazzup.integration', 'auto_send_notification_settings');
    $settings = json_decode($settings, true);

    $agentHour = (DateTime::createFromTimestamp(strtotime($settings['DATE'])))->format('H:i');
    $currentHour = (new DateTime())->format('H:i');

    $plannedDate = new DateTime();
    if ($currentHour > $agentHour) $plannedDate = $plannedDate->add('1 day');
    $plannedDate = $plannedDate->format('Y-m-d\T' . $agentHour . ':00P');

    $nextExec = new DateTime($plannedDate, DateTimeInterface::ATOM);

    $agentId = CAgent::AddAgent(
      "\Mywebstor\Wazzup\Integration\Agents\AutoSender::sendMessages();",
      "mywebstor.wazzup.integration",
      "Y",
      86400,
      $nextExec,
      'Y',
      $nextExec,
      100
    );

    Option::set('mywebstor.wazzup.integration', 'auto_send_notification_agent_id', $agentId);
  }

  public static function deactivateAgent()
  {
    $agentId = Option::get('mywebstor.wazzup.integration', 'auto_send_notification_agent_id', 0);

    if ($agentId) {
      CAgent::Delete($agentId);
    }

    Option::delete('mywebstor.wazzup.integration', ['name' => 'auto_send_notification_agent_id']);
  }

  public static function sendMessages()
  {
    $absenceIblockId = Option::get('intranet', 'iblock_absence', 0);

    if (!$absenceIblockId) {
      throw new RestException('Iblock absence not found', 400);
      return [];
    }

    $settings = Option::get('mywebstor.wazzup.integration', 'auto_send_notification_settings');
    $settings = json_decode($settings, true);

    $selectedDate = (new DateTime())->add('+' . $settings['DIFF'] . ' day');

    /** 
     * (clone $selectedDate)->add('-1 day') => Потому что в графике отсуствий стоит по 29.05.2025, 
     * а DATE_ACTIVE_FROM хранит по 29.05.2025 00:00:00, а не 23:59:59 
     */
    $absencesObj = CIBlockElement::GetList(
      [],
      [
        'IBLOCK_ID' => $absenceIblockId,
        'ACTIVE' => 'Y',
        '<=DATE_ACTIVE_FROM' => $selectedDate,
        '>=DATE_ACTIVE_TO' => (clone $selectedDate)->add('-1 day'),
      ]
    );

    $userPropId = PropertyTable::query()
      ->setSelect(['ID'])
      ->setFilter([
        'IBLOCK_ID' => $absenceIblockId,
        'CODE' => 'USER',
      ])
      ->fetch()['ID'];

    $absences = [];
    while ($absence = $absencesObj->GetNext()) $absences[] = $absence;

    $userIsAbsense = [];

    if (count($absences)) {
      $elProps = CIBlockElement::GetPropertyValues(
        $absenceIblockId,
        ['ID' => array_column($absences, 'ID')],
        false,
        ['ID' => $userPropId]
      );
      while ($el = $elProps->GetNext()) $userIsAbsense[] = $el[$userPropId];
    }

    $query = AppointmentTable::query()
      ->registerRuntimeField(
        new ExpressionField(
          'DATE_FROM_STR',
          "CONCAT( LPAD(DAY(%s), 2, '0'), '.', LPAD(MONTH(%s), 2, '0'), '.', YEAR(%s))",
          ['DATE_FROM', 'DATE_FROM', 'DATE_FROM']
        )
      )
      ->registerRuntimeField(
        new ExpressionField(
          'DATE_CREATE_STR',
          "CONCAT( LPAD(DAY(%s), 2, '0'), '.', LPAD(MONTH(%s), 2, '0'), '.', YEAR(%s))",
          ['DATE_CREATE', 'DATE_CREATE', 'DATE_CREATE']
        )
      )
      ->setSelect([
        'ID',
        'DOCTOR_ID',
        'DATE_FROM',
        'DATE_CREATE',
        'DOCTOR',
        'DOCTOR.USER',
        'CONTACT_ID',
        'CONTACT',
        'STATUS',
      ])
      ->setFilter([
        '!CONTACT_ID' => null,
        '=DATE_FROM_STR' => $selectedDate->format('d.m.Y'),
        '!DATE_CREATE_STR' => (new DateTime())->format('d.m.Y'),
        '!@STATUS.SEMANTICS' => [PhaseSemantics::FAILURE, PhaseSemantics::SUCCESS],
      ])
      ->setOrder(['ID' => 'DESC']);

    $result = [];

    foreach ($query->fetchCollection() as $obj) {
      if (in_array($obj->getDoctorId(), $userIsAbsense) || in_array($obj->getDoctorId(), $settings['DISABLED_DOCTORS'])) continue;

      $result[] = [
        'ID' => $obj->getId(),
        'CONTACT_ID' => $obj->getContactId(),
        'DOCKTOR_ID' => $obj->getDoctorId(),
        'DATE_FROM' => $obj->getDateFrom()->format(DateTimeInterface::ATOM),
        'DATE_CREATE' => $obj->getDateCreate()->format(DateTimeInterface::ATOM),
        'STATUS_NAME' => $obj->getStatus()->getName(),
      ];
    }

    $wfTmplId = Option::get('mywebstor.wazzup.integration', 'app_bizproc_selected', null);

    if (!$wfTmplId) {
      self::deactivate();
      \Bitrix\Main\Diag\Debug::writeToFile(print_r('Bizproc not selected', true), "ERROR", "__mwi_agent_autosender__.log");
      throw new Exception('Bizproc not selected', 400);
      return '';
    }

    $wfObj = WorkflowTemplateTable::query()
      ->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'])
      ->where('ID', $wfTmplId)
      ->fetchObject();

    if (!$wfObj) {
      self::deactivate();
      \Bitrix\Main\Diag\Debug::writeToFile(print_r('Bizproc not found', true), "ERROR", "__mwi_agent_autosender__.log");
      throw new Exception('Bizproc not found', 400);
      return '';
    }

    $arErrorsTmp = [];

    foreach ($result as $appointment) {
      try {
        $wfId = \CBPDocument::StartWorkflow(
          $wfTmplId,
          [
            $wfObj->getModuleId(),
            $wfObj->getEntity(),
            $appointment['ID']
          ],
          [],
          $arErrorsTmp
        );

        \Bitrix\Main\Diag\Debug::writeToFile(print_r([
          'WF_ID' => $wfId,
          'WF' => [
            $wfObj->getModuleId(),
            $wfObj->getEntity(),
            $appointment['ID']
          ],
          'APPOINTMENT' => $appointment,
        ], true), "BP STARTED", "__mwi_agent_autosender__.log");
      } catch (\Exception $ex) {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r($ex->getMessage(), true), "ERROR-11", "__mwi_agent_autosender__.log");
      } catch (\Error $ex) {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r([$ex->getMessage(), $ex->getTraceAsString()], true), "ERROR-11", "__mwi_agent_autosender__.log");
      }
    }

    if (count($arErrorsTmp) > 0) {
      \Bitrix\Main\Diag\Debug::writeToFile(print_r($arErrorsTmp, true), "ERROR", "__mwi_agent_autosender__.log");
    }

    return '\Mywebstor\Wazzup\Integration\Agents\AutoSender::sendMessages();';
  }

  protected static function deactivate()
  {
    $settings = Option::get('mywebstor.wazzup.integration', 'auto_send_notification_settings');
    $settings = json_decode($settings, true);
    $settings['ACTIVE'] = 'N';
    Option::set('mywebstor.wazzup.integration', 'auto_send_notification_settings', json_encode($settings));
  }
}
