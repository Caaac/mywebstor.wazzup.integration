<?

/* Modules classes */
use Mywebstor\Wazzup\Integration\Helper;
use Mywebstor\Wazzup\Integration\WorkflowSendedMessagesTable;

/* Bizproc classes */
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CBPGetterWhatsappMessage extends CBPActivity implements IBPEventActivity, IBPActivityExternalEventListener
{

  const STATUS_SUCCESS = 'success';
  const STATUS_ERROR = 'error';

  private $status = null;

  function __construct($name)
  {
    parent::__construct($name);
    $this->arProperties = self::getArProperties();
    $this->SetPropertiesTypes(self::getArPropertiesTypes());
  }

  protected static function includeModules()
  {
    $modules = ['bizproc', 'mywebstor.wazzup.integration'];

    foreach ($modules as $module) {
      if (!\Bitrix\Main\Loader::IncludeModule($module)) {
        throw new \Exception('Module ' . $module . ' is not installed');
      }
    }
  }

  /**
   * @return int Константа CBPActivityExecutionStatus::*.
   * @throws Exception
   */
  public function Execute()
  {

    $this->WriteToTrackingService('Бизнес-процесс запустился');

    self::includeModules();
    $this->Subscribe($this);

    /** @var array $phones */
    $phones = $this->ParseValue('{'.'=Document:PHONE}');

    /** @var string $chatId */
    $chatId = array_shift($phones)['VALUE'];
    $chatId = preg_replace('![^0-9]+!', '', $chatId);

    $queryData = [
      'channelId' => $this->WhatsappChannelId,
      'chatType' => 'whatsapp',
      'chatId' => $chatId, // TODO
      'templateId' => $this->WhatsappMessageTemplateGUID,
      'templateValues' => []
    ];

    foreach (json_decode($this->WhatsappMessageBodyValues, true) as $key => $value) {
      // TODO delete
      $this->WriteToTrackingService($key . ' = ' . $value);
      $queryData['templateValues'][] = $value;
    }

    // TODO delete
    $this->WriteToTrackingService('Запрос на отправку сообщения в Wassup: ' . json_encode($queryData, JSON_UNESCAPED_UNICODE));

    $data = [
      'WORKFLOW_ID' => $this->workflow->getInstanceId(),
      'ACTIVITY_NAME' => $this->name,
      'MESSAGE_TEMPLATE_ID' => $this->WhatsappMessageTemplateGUID,
      'CHANEL_ID' => $this->WhatsappChannelId,
      'CHAT_ID' => $chatId, // TODO
      'MESSAGE_STATUS' => null,
      'SEND_MESSAGE_ID' => null,
      'STATUS' => WorkflowSendedMessagesTable::STATUS_WAIT_ANSWER,
    ];

    try {
      $response = self::sendMessage($queryData);

      $data['MESSAGE_STATUS'] = $response['status'];
      $data['SEND_MESSAGE_ID'] = $response['data']['messageId'] ?: null;

      $this->status = $response['status'];
      $this->WriteToTrackingService('Ответ от Wassup: ' . json_encode($response['data'], JSON_UNESCAPED_UNICODE));
    } catch (\Exception $e) {
      $this->status = $e->getCode();
      $data['MESSAGE_STATUS'] = $e->getCode();
      $data['STATUS'] = WorkflowSendedMessagesTable::STATUS_ERROR;
    }

    // TODO delete
    $this->WriteToTrackingService('Данные в таблицу WorkflowSendedMessagesTable: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

    $resultAdd = WorkflowSendedMessagesTable::add($data);

    if (!$resultAdd->isSuccess()) {
      throw new \Exception(Loc::getMessage('ERROR__MESSAGE_NOT_ADD_TO_TABLE') . ' \n ' . $resultAdd->getErrorMessages()[0]);
    }

    if ($this->status >= 400) {
      CBPRuntime::SendExternalEvent(
        $this->workflow->getInstanceId(),
        $this->name,
        [
          'ANSWERED_MESSAGE' => null,
          'ANSWERED_MESSAGE_ID' => null,
          'DATE_ANSWER' => null,
        ],
      );

      return CBPActivityExecutionStatus::Closed;
    }

    // AddMessage2Log([
    //   '$this' => $this,
    //   '$$document' => $document,
    //   '$documentId' => $documentId,
    //   'name' => $this->name,
    //   'workflow' => $this->workflow,
    //   'arProperties' => $this->arProperties,
    //   'arPropertiesTypes' => $this->arPropertiesTypes,
    //   'documentService' => $this->documentService,
    //   'taskService' => $this->taskService,
    // ], 'documentId');

    // $rootActivity = $this->GetRootActivity();
    // $documentId = $rootActivity->GetDocumentId();
    // $runtime = CBPRuntime::GetRuntime();
    // $documentService = $runtime->GetService('DocumentService');
    // $taskService = $this->workflow->GetService('TaskService');

    // $document = $documentService->getDocument($documentId);

    // $companyId = $document['ID'];

    // $activityName = $this->name;
    // $workflowId = $this->workflow->getInstanceId();

    return CBPActivityExecutionStatus::Executing;
  }

  /**
   * Обработчик ошибки выполнения БП
   * (вызывается, если ошибка произошла во время выполнения данного действия).
   *
   * @param Exception $exception          
   * @return int Константа CBPActivityExecutionStatus::*.
   * @throws Exception
   */
  public function HandleFault(Exception $exception)
  {

    $this->WriteToTrackingService(Loc::getMessage('ERROR__ACTIVITY_EXCEPTION', array('#ERROR#' => $exception)));

    $status = $this->Cancel();

    // if ($status == CBPActivityExecutionStatus::Canceling) {
    //   return CBPActivityExecutionStatus::Faulting;
    // }

    return $status;
  }

  /**
   * Обработчик остановки БП (если остановка произошла во время выполнения
   * данного действия).
   * @return int Константа CBPActivityExecutionStatus::*.
   * @throws Exception
   */
  public function Cancel()
  {

    self::includeModules();
    $this->Unsubscribe($this);

    AddMessage2Log('!Cancel');

    $this->WriteToTrackingService(Loc::getMessage('MWI_ACTIVITY_CANCEL'));

    return CBPActivityExecutionStatus::Closed;
  }

  /**
   * Подписка на событие
   *
   * @param IBPActivityExternalEventListener $eventHandler
   * @throws Exception
   */
  public function Subscribe(IBPActivityExternalEventListener $eventHandler) //2
  {
    if ($eventHandler == null) {
      throw new Exception("eventHandler");
    }
    $this->workflow->AddEventHandler($this->name, $eventHandler);
  }

  /**
   * Обработчик внешнего события
   *
   * @param array $arEventParameters
   * @throws Exception
   */
  public function OnExternalEvent($arEventParameters = [])
  {
    self::includeModules();

    $this->STATUS = $this->status < 400 ? self::STATUS_SUCCESS : self::STATUS_ERROR;
    $this->STATUS_CODE = $this->status;
    $this->ANSWERED_MESSAGE = $arEventParameters['ANSWERED_MESSAGE'];
    $this->ANSWERED_MESSAGE_ID = $arEventParameters['ANSWERED_MESSAGE_ID'];

    AddMessage2Log($arEventParameters, 'OnExternalEvent params');

    if ($this->executionStatus != CBPActivityExecutionStatus::Closed) {
      $this->Unsubscribe($this);
      $this->workflow->CloseActivity($this);
    }
  }

  /**
   * Отписка от события
   *
   * @param IBPActivityExternalEventListener $eventHandler
   * @param int $status
   */
  public function Unsubscribe(IBPActivityExternalEventListener $eventHandler, int $status = 2)
  {
    AddMessage2Log($eventHandler, '!Unsubscribe');
    $this->workflow->RemoveEventHandler($this->name, $eventHandler);
  }

  public static function GetPropertiesDialog(
    $documentType,
    $activityName,
    $arWorkflowTemplate,
    $workflowParameters,
    $workflowVariables,
    $arCurrentValues = null,
    $formName = '',
    $popupWindow = null,
    $siteId = ''
  ) {

    if (!is_array($arCurrentValues)) {
      $arCurrentValues = self::getArProperties();

      $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
        $arWorkflowTemplate,
        $activityName
      );

      if (is_array($arCurrentActivity['Properties'])) {
        $arCurrentValues = array_merge(
          $arCurrentValues,
          $arCurrentActivity['Properties']
        );
      }
    }

    // AddMessage2Log([
    //   'documentType' => $documentType,
    //   'activityName' => $activityName,
    //   'arWorkflowTemplate' => $arWorkflowTemplate,
    //   'workflowParameters' => $workflowParameters,
    //   'workflowVariables' => $workflowVariables,
    //   'arCurrentValues' => $arCurrentValues,
    //   'formName' => $formName,
    //   'popupWindow' => $popupWindow,
    //   'siteId' => $siteId,
    // ], '__FIRST');

    $runtime = CBPRuntime::GetRuntime();
    return $runtime->ExecuteResourceFile(
      __FILE__,
      "properties_dialog.php",
      array(
        "arCurrentValues" => $arCurrentValues,
        "formName" => $formName
      )
    );
  }

  public static function getPropertiesDialogValues(
    $documentType,
    $activityName,
    &$arWorkflowTemplate,
    &$arWorkflowParameters,
    &$arWorkflowVariables,
    $arCurrentValues,
    &$arErrors
  ): bool {

    self::includeModules();

    // AddMessage2Log([
    //   'documentType' => $documentType,
    //   'activityName' => $activityName,
    //   'arWorkflowTemplate' => $arWorkflowTemplate,
    //   'arWorkflowParameters' => $arWorkflowParameters,
    //   'arWorkflowVariables' => $arWorkflowVariables,
    //   'arCurrentValues' => $arCurrentValues,
    //   // 'arErrors' => $arErrors,
    // ], '__SECOND');

    $arProperties = array(
      'CrmContactId' => $arCurrentValues['CrmContactId'],
      'WhatsappMessageTemplateGUID' => '',
      'WhatsappMessageBodyValues' => '',
      'WhatsappChannelId' => '',
    );

    $wf = CBPWorkflowTemplateLoader::GetList(
      [],
      ["DOCUMENT_TYPE" => $documentType]
    );

    /** @var array $wfData | Need for get activity properties setted by app */
    $wfData = null;

    /* Get actual activity data */
    while ($elWf = $wf->fetch()) {
      if (str_contains(json_encode($elWf['TEMPLATE']), $activityName)) {
        $wfData = $elWf;
        break;
      }
    }

    if (!$wfData) {
      $arErrors[] = array(
        'code' => 'Empty',
        'message' => Loc::getMessage('ERROR__NOT_FOUND_WORKFLOW'),
      );

      return false;
    }

    $arActivityPropByApp = &CBPWorkflowTemplateLoader::FindActivityByName(
      $wfData['TEMPLATE'],
      $activityName
    );

    /** Check app setted fields */
    foreach (self::getAppFields() as $field) {

      /** Check array filed */
      if (self::getArPropertiesTypes($field)['Array']) {
        try {
          $resultDecode = json_decode($arActivityPropByApp['Properties'][$field], true);
          if (gettype($resultDecode) != 'array') throw new \Exception('TypeError', 400);
        } catch (\Exception $e) {
          $arErrors[] = array(
            'code' => 'Empty',
            'message' => Loc::getMessage('ERROR__IS_NOT_ARRAY', ['#FIELD#' => $field]),
          );
        }
      }

      /** Check uuid filed */
      if (
        self::getArPropertiesTypes($field)['UUID']
        && !preg_match(Helper::UUID_PATTERN, $arActivityPropByApp['Properties'][$field])
      ) {
        $arErrors[] = array(
          'code' => 'Empty',
          'message' => Loc::getMessage('ERROR__IS_NOT_UUID', ['#FIELD#' => $field]),
        );
      }

      $arProperties[$field] = $arActivityPropByApp['Properties'][$field];
    }

    if ($arErrors) {
      return false;
    }

    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
      $arWorkflowTemplate,
      $activityName
    );
    $arCurrentActivity['Properties'] = $arProperties;

    return true;
  }

  public static function sendMessage(array $queryData): array
  {
    $httpClient = new HttpClient(['socketTimeout' => 15]);

    $queryToWassup = $httpClient
      ->setHeaders(['Authorization' => 'Bearer ' . Helper::getApiKey()])
      ->post(Helper::MESSAGE_SEND_URL, $queryData);

    if ($errors = $httpClient->getError()) {
      throw new \Exception(json_encode($errors), $httpClient->getStatus());
      return [];
    }

    return [
      'data' => json_decode($queryToWassup, true),
      'status' => $httpClient->getStatus(),
    ];
  }

  protected static function getArProperties($field = null)
  {
    $data = [
      'CrmContactId' => '',
      'WhatsappMessageTemplateGUID' => '',
      'WhatsappMessageBodyValues' => '',
      'WhatsappChannelId' => '',
    ];

    return $field ? $data[$field] : $data;
  }

  protected static function getArPropertiesTypes($field = null)
  {
    $data = [
      'CrmContactId' => [
        'Type' => FieldType::INT
      ],
      'WhatsappMessageTemplateGUID' => [
        'Type' => FieldType::STRING,
        'UUID' => true
      ],
      'WhatsappMessageBodyValues' => [
        'Type' => FieldType::TEXT,
        'Array' => true,
      ],
      'WhatsappChannelId' => [
        'Type' => FieldType::STRING,
        'UUID4' => true
      ],
    ];

    return $field ? $data[$field] : $data;
  }

  protected static function getAppFields()
  {
    return [
      'WhatsappMessageTemplateGUID',
      'WhatsappMessageBodyValues',
      'WhatsappChannelId',
    ];
  }
}
