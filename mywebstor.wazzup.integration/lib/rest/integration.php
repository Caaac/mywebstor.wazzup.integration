<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */

use Bitrix\Main\Application;
use Mywebstor\Wazzup\Integration\Helper;

/* Bitrix classes */
use \Bitrix\Rest\RestException;
use \Bitrix\Main\Web\HttpClient;

class Integration extends \IRestService
{
  const NAMESPACE = 'mwi.integration';
  const WEBHOOK_URL = 'https://api.wazzup24.com/v3/webhooks';

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

    /** PATCH в HttpClient нету */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, Helper::getWazzupWebhook());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);

    if ($response === false) {
      throw new RestException(curl_error($ch), 500);
      curl_close($ch);
      return false;
    }

    curl_close($ch);
    return true;

    // return Helper::setWazzupWebhook($query);
  }
}
