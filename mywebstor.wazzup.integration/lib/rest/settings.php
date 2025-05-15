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
}
