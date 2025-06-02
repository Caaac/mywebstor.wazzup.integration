<?

namespace Mywebstor\Wazzup\Integration\Agents;

class Message
{
  public static function terminateWorkflow($workflowId = null)
  {
    if (!$workflowId) return '';

    $status = '';
    $errors = [];

    $resultTerminate = \CBPDocument::terminateWorkflow($workflowId, [], $errors, $status);

    if ($errors) {
      \Bitrix\Main\Diag\Debug::writeToFile(print_r([
        'method' => '\Mywebstor\Wazzup\Integration\Agents\Message::terminateWorkflow',
        'status' => 'ERROR',
        'workflowId' => $workflowId,
        'errors' => $errors
      ], true), "", "__mwi_error__.log");

      return '';
    }

    $wsmtObj = WorkflowSendedMessagesTable::query()
      ->setSelect(['ID'])
      ->where('WORKFLOW_ID', $workflowId)
      ->fetchObject();

    if ($wsmtObj) {
      $wsmtObj->setStatus(WorkflowSendedMessagesTable::STATUS_CANCELED)->save();
    }

    return '';
  }
}
