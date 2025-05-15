<?

use Mywebstor\Wazzup\Integration\Rest;

class CMywebstorWazzupIntegrationRestService extends \IRestService
{
  public static function onRestServiceBuildDescription()
  {
    return [
      // "mywebstor.wazzup.integration" => array_merge(
      CRestUtil::GLOBAL_SCOPE => array_merge(
        Rest\Settings::$methods,
        Rest\Integration::$methods,
      ),
    ];
  }
}
// 