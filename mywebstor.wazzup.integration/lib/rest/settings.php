<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */

use Mywebstor\Wazzup\Integration\Helper;

/* Bitrix classes */
use \Bitrix\Main\Config\Option;
use Bitrix\Rest\RestException;

class Settings extends \IRestService
{
  const NAMESPACE = 'mwi.settings';

  public static $methods = [
    self::NAMESPACE . '.api_key.get' => [__CLASS__, 'getApiKey'],
    self::NAMESPACE . '.api_key.set' => [__CLASS__, 'setApiKey'],

    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
    self::NAMESPACE . '.set' => [__CLASS__, 'set'],
  ];

  public static function getApiKey()
  {
    return Helper::getApiKey();
  }

  public static function setApiKey($query)
  {
    if (!isset($query['apiKey'])) {
      throw new RestException('Field apiKey is empty', 400);
      return false;
    }

    try {
      Option::set('mywebstor.wazzup.integration', 'API_KEY', $query['apiKey']);
    } catch (\Exception $e) {
      throw new RestException($e->getMessage(), 400);
      return false;
    }

    return true;
  }

  public static function get($query)
  {
    $response = [];

    foreach ($query['keys'] ?: [] as $settingsKey) {
      $response[$settingsKey] = Option::get('mywebstor.wazzup.integration', $settingsKey, self::getStandartSettingValue($settingsKey));
    }

    return $response;
  }

  public static function set($query)
  {
    try {
      foreach ($query ?: [] as $settingsKey => $settingsValue) {
        Option::set('mywebstor.wazzup.integration', $settingsKey, $settingsValue);
      }
    } catch (\Exception $e) {
      throw new RestException($e->getMessage(), 400);
      return false;
    }

    return true;
  }

  private static function getStandartSettingValue($settingsKey)
  {
    switch ($settingsKey) {
      case 'app_doctors_selected':
      return '[]';
      break;
      case 'app_bizproc_selected':
      default:
        return null;
        break;
    }
  }
}
