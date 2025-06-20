<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */
use \Mywebstor\Wazzup\Integration\Agents\AutoSender;

/* Bitrix classes */
use \Bitrix\Main\Config\Option;
use \Bitrix\Rest\RestException;

class AutoSendSettings extends \IRestService
{
  const NAMESPACE = 'mwi.autosendsettings';

  public static $methods = [
    self::NAMESPACE . '.set' => [__CLASS__, 'set'],
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
  ];

  public static function get($query)
  {
    $settings = Option::get('mywebstor.wazzup.integration', 'auto_send_notification_settings');
    if ($settings) return json_decode($settings, JSON_UNESCAPED_UNICODE);
    return null;
  }

  public static function set(array $settings)
  {
    Option::set('mywebstor.wazzup.integration', 'auto_send_notification_settings', json_encode($settings));

    if ($settings['ACTIVE'] == 'Y') {
      AutoSender::activateAgent();
    } else {
      AutoSender::deactivateAgent();
    }

    return true;
  }
}
