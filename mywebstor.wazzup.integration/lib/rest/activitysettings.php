<?

namespace Mywebstor\Wazzup\Integration\Rest;

/** Module classes */

use Bitrix\Bizproc\WorkflowTemplateTable;
use Mywebstor\Wazzup\Integration\Helper;
use Mywebstor\Wazzup\Integration\ActivitySettingsTable;

/** Bitrix classes */

use Bitrix\Rest\RestException;
use Bitrix\Main\Web\HttpClient;
use CBPWorkflowTemplateLoader;

class ActivitySettings extends \IRestService
{
  const NAMESPACE = 'mwi.activity.settings';

  public static $methods = [
    // self::NAMESPACE . '.get' => [__CLASS__, 'get'],             // DEP
    // self::NAMESPACE . '.list' => [__CLASS__, 'list'],           // DEP
    // self::NAMESPACE . '.template' => [__CLASS__, 'template'],   // DEP
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
    self::NAMESPACE . '.update' => [__CLASS__, 'update'],
  ];


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

    $arWorkflowTemplate = $wfObject->getTemplate();
    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
      $arWorkflowTemplate,
      $activityName
    );

    if (!isset($query["select"]) || empty($query["select"])) {
      return $arCurrentActivity["Properties"];
    }

    $responce = [];

    foreach ($query["select"] as $propKey) {
      $responce[$propKey] = $arCurrentActivity["Properties"][$propKey] ?: '';
    }

    return $responce;
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

    foreach ($activityProperties as $key => $value) {
      if (gettype($value) == 'array') {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
      }
      $arCurrentActivity['Properties'][$key] = $value;
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

  public static function list($query)
  {
    $settings = ActivitySettingsTable::query();

    if (!empty($query['select'])) {
      $settings->setSelect($query['select']);
    } else {
      $settings->setSelect(['*']);
    }

    if (!empty($query['filter'])) {
      $settings->setFilter($query['filter']);
    }

    if (!empty($query['order'])) {
      $settings->setOrder($query['order']);
    }

    if (!empty($query['limit'])) {
      $settings->setLimit($query['limit']);
    }

    $result = $settings->fetchAll();

    return $result ?: [];
  }

  public static function template()
  {
    $tmpl = [];

    foreach (ActivitySettingsTable::getMap() as $key => $_value) {
      $tmpl[$key] = null;
    }

    return $tmpl;
  }
}
