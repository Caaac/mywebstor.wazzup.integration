<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */

use Mywebstor\Wazzup\Integration\Helper;
use Mywebstor\Wazzup\Integration\ActivitySettingsTable;


/* Bitrix classes */
/* -- Bizproc --  */
use Bitrix\Bizproc\Automation\Helper as AutomationHelper;
use Bitrix\Bizproc\WorkflowTemplateTable;
/* -- e.t.c. --  */
use Bitrix\Rest\RestException;
use Bitrix\Main\Web\HttpClient;
use CBPWorkflowTemplateLoader;

class ActivitySettings extends \IRestService
{
  const NAMESPACE = 'mwi.activity.settings';

  public static $methods = [
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
    self::NAMESPACE . '.update' => [__CLASS__, 'update'],
  ];


  /**
   * @var array $query | Expected:
   *    - activityName
   *    - select (optional)
   * @throws RestException
   */
  public static function get($query)
  {
    $validField = ['activityName'];

    foreach ($validField as $fieldKey) {
      if (empty($query[$fieldKey])) {
        throw new RestException('Field ' . $fieldKey . ' is required', 400);
        return 0;
      }
    }

    $activityName = $query['activityName'];

    $wf = WorkflowTemplateTable::query()->setSelect(['*']);
    $wfObject = null;

    foreach ($wf->fetchCollection() ?: [] as $el) {
      if (str_contains(json_encode($el->getTemplate(), JSON_UNESCAPED_UNICODE), $activityName)) {
        $wfObject = $el;
        break;
      }
    }

    if (!$wfObject) {
      throw new RestException('Workflow template not found', 404);
      return 0;
    }

    $wfDocument = [
      $wfObject->getModuleId(),
      $wfObject->getEntity(),
      $wfObject->getDocumentType()
    ];

    $arWorkflowTemplate = $wfObject->getTemplate();
    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
      $arWorkflowTemplate,
      $activityName
    );

    if (!isset($query["select"]) || empty($query["select"])) {
      return self::convertExpressions($arCurrentActivity["Properties"], $wfDocument);
    }

    foreach ($query["select"] as $propKey) {
      $responce[$propKey] = $arCurrentActivity["Properties"][$propKey] ?: '';
    }

    return self::convertExpressions($responce, $wfDocument);
  }


  /**
   * @var array $query | Expected:
   *    - activityName
   *    - activitySettings
   * @throws RestException
   */
  public static function update($query): int
  {
    $validField = ['activityName', 'activityProperties'];

    foreach ($validField as $fieldKey) {
      if (empty($query[$fieldKey])) {
        throw new RestException('Field ' . $fieldKey . ' is required', 400);
        return 0;
      }
    }

    $activityName = $query['activityName'];
    $activityProperties = $query['activityProperties'];

    $wf = WorkflowTemplateTable::query()->setSelect(['*']);
    $wfObject = null;

    foreach ($wf->fetchCollection() ?: [] as $el) {
      if (str_contains(json_encode($el->getTemplate(), JSON_UNESCAPED_UNICODE), $activityName)) {
        $wfObject = $el;
        break;
      }
    }

    if (!$wfObject) {
      throw new RestException('Workflow template not found', 404);
      return 0;
    }

    $arWorkflowTemplate = $wfObject->getTemplate();
    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
      $arWorkflowTemplate,
      $activityName
    );

    $wfDocument = [
      $wfObject->getModuleId(),
      $wfObject->getEntity(),
      $wfObject->getDocumentType(),
    ];

    foreach ($activityProperties as $key => $value) {
      if (gettype($value) == 'array') {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
      }
      $arCurrentActivity['Properties'][$key] = AutomationHelper::unConvertExpressions(
        $value,
        [
          $wfObject->getModuleId(),
          $wfObject->getEntity(),
          $wfObject->getDocumentType(),
        ],
        false
      );
    }

    $r = CBPWorkflowTemplateLoader::update(
      $wfObject->getId(),
      [
        'TEMPLATE' => $arWorkflowTemplate,
        'DOCUMENT_TYPE' => [
          $wfObject->getModuleId(),
          $wfObject->getEntity(),
          $wfObject->getDocumentType(),
        ]
      ]
    );

    if (!$r) {
      throw new RestException('Failed to update activity settings', 400);
      return 0;
    }

    return $r;
  }

  public static function convertExpressions(array $propsList,array $wfDocument)
  {
    $responce = [];

    foreach ($propsList as $propKey => $propValue) {
      $responce[$propKey] = AutomationHelper::convertExpressions(
        $propValue,
        $wfDocument,
        false
      );
    }

    return $responce;
  }
}
