<?

namespace Mywebstor\Wazzup\Integration\Rest\Hms;

/* Module classes */

/* mywebstor.hms classes */
use MyWebstor\Hms\DoctorTable;

/* Bitrix classes */
use Bitrix\Rest\RestException;

class Doctor extends \IRestService
{
  const NAMESPACE = 'mwi.hms.doctor';

  public static $methods = [
    self::NAMESPACE . '.list' => [__CLASS__, 'list'],
  ];

  /**
   * @todo TODO можно будет сделать фильтрацию и селективность
   * 
   * @throws RestException
   * @return array
   */
  public static function list(): array
  {
    return DoctorTable::query()
      ->setSelect([
        'ID',
        'NAME' => 'USER.NAME',
        'SECOND_NAME' => 'USER.SECOND_NAME',
        'LAST_NAME' => 'USER.LAST_NAME',
      ])
      ->setFilter(['USER.ACTIVE' => 'Y'])
      ->fetchAll();
  }
}
