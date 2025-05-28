<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */

use Mywebstor\Wazzup\Integration\Helper;

/* Bitrix classes */
use Bitrix\Main\Application;
use \Bitrix\Rest\RestException;
use \Bitrix\Main\Web\HttpClient;

class Integration extends \IRestService
{
  const NAMESPACE = 'mwi.integration';

  public static $methods = [
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
    self::NAMESPACE . '.set' => [__CLASS__, 'set'],
  ];

  public static function get()
  {
    $apiKey = Helper::getApiKey();

    if (!strlen($apiKey)) {
      throw new RestException('Ключ API не задан', 400);
      return (object)[];
    }

    $httpClient = new HttpClient(['socketTimeout' => 15]);

    $query = $httpClient
      ->setHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $apiKey,
      ])
      ->get(Helper::getWazzupWebhook());

    $response = json_decode($query, true);

    if (!$response) {
      throw new RestException('Wazzup server is not responding', 500);
      return (object)[];
    }

    if ($httpClient->getStatus() != 200) return -1;

    return $response;
  }

  public static function set($settings)
  {
    $apiKey = Helper::getApiKey();

    if (!strlen($apiKey)) {
      throw new RestException('Api key not setted', 400);
      return (object)[];
    }

    $request = Application::getInstance()->getContext()->getRequest();
    $httpOrigin = $request->getServer()->get('HTTP_ORIGIN');

    foreach ($settings as $key => $value) $settings[$key] = $value == 1;

    $data = [
      'webhooksUri' => $httpOrigin . '/mywebstor_wazzup_integration/',
      'subscriptions' => $settings
    ];

    $httpClient = new HttpClient(['socketTimeout' => 15]);

    $httpClient
      ->setHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $apiKey,
      ])
      ->query($httpClient::HTTP_PATCH, Helper::getWazzupWebhook(), json_encode($data));

    if ($httpClient->getResult() != 'OK') {
      throw new RestException($httpClient->getError(), $httpClient->getStatus());
      return false;
    }

    return true;
  }
}
