<?

namespace Mywebstor\Wazzup\Integration;

use Bitrix\Main\Config\Option;

class Helper
{
  const WEBHOOK_URL = 'https://api.wazzup24.com/v3/webhooks';

  public static function getApiKey()
  {
    return Option::get('mywebstor.wazzup.integration', 'API_KEY', '');
  }

  public static function getWazzupWebhook()
  {
    return self::WEBHOOK_URL;
  }
}
