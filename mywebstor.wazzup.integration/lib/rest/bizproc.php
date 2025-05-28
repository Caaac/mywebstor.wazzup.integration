<?

namespace Mywebstor\Wazzup\Integration\Rest;

/* Module classes */

/* Bitrix classes */

use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Rest\RestException;
use CBPDocument;

class Bizproc extends \IRestService
{
  const NAMESPACE = 'mwi.bizproc';

  const IBLOCK_CODE = 'bitrix_processes';

  public static $methods = [
    self::NAMESPACE . '.workflow.start' => [__CLASS__, 'workflowStart'],
    self::NAMESPACE . '.list' => [__CLASS__, 'list'],
    self::NAMESPACE . '.settings.get' => [__CLASS__, 'settingsGet'],
    self::NAMESPACE . '.settings.update' => [__CLASS__, 'settingsUpdate'],
  ];

  /**
   * TODO можно будет добавить фильтрацию, выбор полей и тд
   * @throws RestException
   * @return array
   */
  public static function list(): array
  {
    $result = [];
    $wft = WorkflowTemplateTable::query()->setSelect(['ID', 'NAME', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'])->fetchCollection();

    foreach ($wft ?: [] as $obj) {
      $result[] = [
        'ID' => (int)$obj->getId(),
        'NAME' => $obj->getName(),
        // 'MODULE_ID' => $obj->getModuleId(),
        // 'ENTITY' => $obj->getEntity(),
        // 'DOCUMENT_TYPE' => $obj->getDocumentType(),
      ];
    }

    return $result;
  }

  public static function settingsGet(): int | null
  {
    return Option::get('mywebstor.wazzup.integration', 'app_bizproc_selected', null);
  }

  /**
   * @var array $query | Expected:
   *    - @param int bizprocId 
   * @throws RestException
   * @return array
   */
  public static function settingsUpdate(array $query): bool
  {
    if (empty($query['bizprocId'])) {
      throw new RestException('Bizproc id not found', 400);
      return false;
    }

    Option::set('mywebstor.wazzup.integration', 'app_bizproc_selected', $query['bizprocId']);
    return true;
  }

  /**
   * @var array $query | Expected:
   *    - @param array[int] appointments 
   * @throws RestException
   * @return array
   */
  public static function workflowStart(array $query): bool
  {
    if (empty($query['appointments'])) {
      throw new RestException('Appointments not found', 400);
      return false;
    }

    $wfTmplId = Option::get('mywebstor.wazzup.integration', 'app_bizproc_selected', null);

    if (!$wfTmplId) {
      throw new RestException('Bizproc not selected', 400);
      return false;
    }

    $wfObj = WorkflowTemplateTable::query()
      ->setSelect(['ID', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'])
      ->where('ID', $wfTmplId)
      ->fetchObject();

    $arErrorsTmp = [];

    foreach ($query['appointments'] as $appId) {
      $wfId = CBPDocument::StartWorkflow(
        $wfTmplId,
        [
          $wfObj->getModuleId(),
          $wfObj->getEntity(),
          $appId
        ],
        [],
        $arErrorsTmp
      );
    }

    if (count($arErrorsTmp) > 0) {
      AddMessage2Log(print_r([
        'METHOD' => 'mwi.bizproc.settings.update',
        'STATUS' => 'ERROR',
        'ERRORS' => $arErrorsTmp
      ], true));
      throw new RestException('Workflow start error', 400);
      return false;
    }

    return true;
  }
}
