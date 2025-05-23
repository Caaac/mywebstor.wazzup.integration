<?

use \Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MywebstorPAMain extends \CBitrixComponent
{
  const STATUS_OK = "200 OK";
  const STATUS_CREATED = "201 Created";
  const STATUS_WRONG_REQUEST = "400 Bad Request";
  const STATUS_UNAUTHORIZED = "401 Unauthorized";
  const STATUS_PAYMENT_REQUIRED = "402 Payment Required";
  const STATUS_FORBIDDEN = "403 Forbidden";
  const STATUS_NOT_FOUND = "404 Not Found";
  const STATUS_TO_MANY_REQUESTS = "429 Too Many Requests";
  const STATUS_INTERNAL = "500 Internal Server Error";

  /** @var \Bitrix\Main\HttpResponse $response */
  protected $response = null;
  protected $command = null;

  function executeComponent()
  {
    try {

      $this->init();

      $result = $this->debugIntegrate();
      $status = self::STATUS_OK;

      $this->response
        ->setStatus($status);

      $this->end();
    } catch (SystemException $e) {
      $this->showError($e->getMessage());
    }
  }

  private function includeModules()
  {
    $modules = [];

    foreach ($modules as $module) {
      if (!\CModule::IncludeModule($module)) {
        throw new SystemException('Module ' . $module . ' is not installed');
      }
    }
  }

  protected function init()
  {
    /**
     * @var \CMain $APPLICATION
     * @var \CUser $USER
     * @var \CDatabase $DB
     */
    global $APPLICATION, $USER, $DB;
    $APPLICATION->RestartBuffer();

    $this->includeModules();

    $this->response = Application::getInstance()
      ->getContext()
      ->getResponse();
    $this->response
      ->getHeaders()
      ->set(
        "Content-Type",
        "application/json"
      );
  }

  protected function debugIntegrate()
  {
    $result = array(
      "status" => "success",
      'data' => $this->request->getJsonList()->toArray(),
    );

    AddMessage2Log(print_r([
      'METHOD' => 'debugIntegrate',
      'DATA' => $this->request->getJsonList()->toArray(),
      '_SERVER' => $_SERVER,
    ], true));

    return $result;
  }

  protected function showError($errorMessage = "Unexpected error", $errorCode = self::STATUS_WRONG_REQUEST)
  {
    if (!$errorMessage)
      $errorMessage = "Unexpected error";

    $this->response
      ->setContent(
        \Bitrix\Main\Web\Json::encode(array(
          "status" => "error",
          "message" => $errorMessage
        ))
      )
      ->setStatus($errorCode);

    $this->end();
  }

  protected function sendLog($message, $code, $fromOneC = true)
  {
    $template = [
      'MODULE' => 'mywebstor.personal_account',
      'COMPONENT' => 'pa.integration.spm',
      'DIRECTION' => $fromOneC ? 'FROM_1C' : "TO_1C",
      'CODE' => $code,
      'MESSAGE' => $message,
    ];

    AddMessage2Log(print_r($template, true));

    return;
  }

  protected function end()
  {
    Application::getInstance()->end();
  }
}
