<?

use Mywebstor\Wazzup\Integration\Rest;

class CMywebstorWazzupIntegrationRestService extends \IRestService
{
  public static function onRestServiceBuildDescription()
  {
    return [
      // "mywebstor.wazzup.integration" => array_merge(
      CRestUtil::GLOBAL_SCOPE => array_merge(
        Rest\Bizproc::$methods,
        Rest\Settings::$methods,
        Rest\Integration::$methods,
        Rest\ActivitySettings::$methods,
        
        Rest\Hms\Doctor::$methods,
        Rest\Hms\Appointment::$methods,
        
        Rest\Wazzup\Chanels::$methods,
        Rest\Wazzup\MessageTemplates::$methods,
      ),
    ];
  }
}
// 