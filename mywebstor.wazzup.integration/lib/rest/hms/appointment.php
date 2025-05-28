<?

namespace Mywebstor\Wazzup\Integration\Rest\Hms;

/* Module classes */

/* mywebstor.hms classes */
use \MyWebstor\Hms\AppointmentTable;

/* Bitrix classes */
/* -- Bizproc --  */
/* -- e.t.c. --  */
use CIBlockElement;
use DateTimeInterface;
use Bitrix\Rest\RestException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\ExpressionField;

class Appointment extends \IRestService
{
  const NAMESPACE = 'mwi.hms.appointment';

  public static $methods = [
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
  ];

  /**
   * @var array $query | Expected:
   *    - @param array filter (optional)
   *        - @param array docktors (optional)
   *        - @param string appointment_date ISO format (optional) | if not exist -> next date
   * @throws RestException
   * @return array
   */
  public static function get(array $query): array
  {

    $absenceIblockId = Option::get('intranet', 'iblock_absence', 0);

    if (!$absenceIblockId) {
      throw new RestException('Iblock absence not found', 400);
      return [];
    }

    $selectedDate = isset($query['filter']['appointment_date']) && !empty($query['filter']['appointment_date'])
      ? new DateTime($query['filter']['appointment_date'], DateTimeInterface::ATOM)
      : (new DateTime())->add('+1 day');

    $absencesObj = CIBlockElement::GetList(
      [],
      [
        'IBLOCK_ID' => $absenceIblockId,
        'ACTIVE' => 'Y',
        '<=DATE_ACTIVE_FROM' => $selectedDate,
        '>=DATE_ACTIVE_TO' => (clone $selectedDate)->add('-1 day'), // Потому что в графике отсуствий стоит по 29.05.2025, а DATE_ACTIVE_FROM хранит по 29.05.2025 00:00:00, а не 23:59:59
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

    $elProps = CIBlockElement::GetPropertyValues($absenceIblockId, ['ID' => array_column($absences, 'ID')], false, ['ID' => $userPropId]);

    $userIsAbsense = [];
    while ($el = $elProps->GetNext()) $userIsAbsense[] = $el[$userPropId];

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
      ->setSelect(['ID', 'DOCTOR_ID', 'DATE_FROM', 'DATE_CREATE', 'DOCTOR', 'DOCTOR.USER', 'CONTACT_ID'])
      ->setFilter([
        '!CONTACT_ID' => null,
        'DATE_FROM_STR' => $selectedDate->format('d.m.Y'),
        '!DATE_CREATE_STR' => (new DateTime())->format('d.m.Y'),
      ]);

    $result = [];

    foreach ($query->fetchCollection() as $obj) {
      if (in_array($obj->getDoctorId(), $userIsAbsense)) continue;

      $result[] = [
        'ID' => $obj->getId(),
        'CONTACT_ID' => $obj->getContactId(),
        'DOCKTOR_ID' => $obj->getDoctorId(),
        'DOCKTOR_NAME' => $obj->getDoctor()->getUser()->getName(),
        'DOCKTOR_LAST_NAME' => $obj->getDoctor()->getUser()->getLastName(),
        'DOCKTOR_SECOND_NAME' => $obj->getDoctor()->getUser()->getSecondName(),
        'DATE_FROM' => $obj->getDateFrom()->format(DateTimeInterface::ATOM),
        'DATE_CREATE' => $obj->getDateCreate()->format(DateTimeInterface::ATOM),
      ];
    }

    return $result;
  }
}
