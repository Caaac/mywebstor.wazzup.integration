<?

namespace Mywebstor\Wazzup\Integration;

use Bitrix\Main\Config\Option;

class Helper
{
  const WEBHOOK_URL = 'https://api.wazzup24.com/v3/webhooks';
  const TEMPLATES_URL = 'https://api.wazzup24.com/v3/templates/whatsapp';
  const MESSAGE_SEND_URL = 'https://api.wazzup24.com/v3/message';
  const CHANELS_URL = 'https://api.wazzup24.com/v3/channels';

  const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
  const UUID4_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

  public static function getApiKey()
  {
    return Option::get('mywebstor.wazzup.integration', 'API_KEY', '');
  }

  public static function getWazzupWebhook()
  {
    return self::WEBHOOK_URL;
  }
}
