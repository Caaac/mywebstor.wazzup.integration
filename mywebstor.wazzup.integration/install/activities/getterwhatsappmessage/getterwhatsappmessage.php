<?

use Mywebstor\Wazzup\Integration\Helper;
use Mywebstor\Wazzup\Integration\WorkflowSendedMessagesTable;

use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\WorkflowTemplateTable;

use Bitrix\Main\Web\HttpClient;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CBPGetterWhatsappMessage extends CBPActivity implements IBPEventActivity, IBPActivityExternalEventListener
{

  private $test = '{{ID элемента CRM}}';
  private $test_2 = '{=Document:CRM_ID}';
  private $channelId = '020dd4fc-f6c2-497c-a103-df1737822682';

  function __construct($name)
  {
    parent::__construct($name);
    $this->arProperties = self::getArProperties();
    $this->SetPropertiesTypes(self::getArPropertiesTypes());
  }

  protected function includeModules()
  {
    $modules = ['bizproc', 'mywebstor.wazzup.integration'];

    foreach ($modules as $module) {
      if (!\Bitrix\Main\Loader::IncludeModule($module)) {
        AddMessage2Log('Module ' . $module . ' is not installed');
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

    $this->WriteToTrackingService('БП запустился');

    $this->includeModules();
    $this->Subscribe($this);

    // Я разрабатываю кастомное действие в для бизнес процессов в Битрик 24

    // Как мне из шаблолна значения {{ID элемента CRM}} получить {=Document:CRM_ID}

    $aaa = '{=Document:CRM_ID}';
    $bbb = '{{ID элемента CRM}}';

    $rootActivity = $this->GetRootActivity();
    $documentId = $rootActivity->GetDocumentId();
    $runtime = CBPRuntime::GetRuntime();
    $documentService = $runtime->GetService('DocumentService');
    $taskService = $this->workflow->GetService('TaskService');

    $document = $documentService->getDocument($documentId);
    
    AddMessage2Log([
      'ID элемента CRM test' => $this->test,
      'ID элемента CRM test 2' => $this->test_2,
      'CrmContactId' => $this->CrmContactId,
    ]);

    AddMessage2Log([
      // 'document' => $document->getFields(),
      'p1' => $this->parseExpression('{{ID элемента CRM}}'),
      'p2' => $this->parseExpression('{'. '{ID элемента CRM}}'),
      'p3' => $this->parseExpression('{=Document:CRM_ID}'),
      'p4' => $this->parseExpression('{'.'=Document:CRM_ID}'),
      'pv1' => $this->parseValue('{{ID элемента CRM}}'),
      'pv2' => $this->parseValue('{'. '{ID элемента CRM}}'),
      'pv3' => $this->parseValue('{=Document:CRM_ID}'),
      'pv4' => $this->parseValue('{'.'=Document:CRM_ID}'),
      // 'getArProperties' => $this->getArProperties(),
      // 'getRawProperty' => $this->getRawProperty('CrmContactId'),
      // 'getArProperties' => $this->getRuntimeProperty(),
    ]);

    $this->WriteToTrackingService('$this->CrmContactId = ' . $this->CrmContactId);

    $queryData = [
      'channelId' => $this->WhatsappChannelId,
      'chatType' => 'whatsapp',
      'chatId' => '79134570795',
      'templateId' => $this->WhatsappMessageTemplateGUID,
      'templateValues' => []
    ];

    $WhatsappMessageBodyValues = $this->WhatsappMessageBodyValues;

    AddMessage2Log([
      'NOT_PARSE' => $WhatsappMessageBodyValues,
      'PARSE' => json_decode($WhatsappMessageBodyValues, true),
    ]);

    try {
      // TODO добавить валидацию сохраняемых данных
      foreach (json_decode($WhatsappMessageBodyValues, true) as $key => $value) {
        AddMessage2Log($value);
        $this->WriteToTrackingService($key . ' = ' . $value);
        $queryData['templateValues'][] = $value;
      }
    } catch (\Exception $e) {
      throw new \Exception(Loc::getMessage('ERROR__INVALID_JSON'));
    }
    
    $this->WriteToTrackingService('Запрос на отправку сообщения в Wassup: ' . json_encode($queryData, JSON_UNESCAPED_UNICODE));

    $response = self::sendMessage($queryData);

    $this->WriteToTrackingService('Ответ от Wassup: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
    
    $data = [
      'WORKFLOW_ID' => $this->workflow->getInstanceId(),
      'ACTIVITY_NAME' => $this->name,
      'MESSAGE_TEMPLATE_ID' => $this->WhatsappMessageTemplateGUID,
      'SENDER_ID' => 1, // TODO убрать поле
    ];
    
    $this->WriteToTrackingService('Данные в таблицу WorkflowSendedMessagesTable: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

    $resultAdd = WorkflowSendedMessagesTable::add($data);

    if (!$resultAdd->isSuccess()) {
      $this->WriteToTrackingService($resultAdd->getErrorMessages()[0]);
      throw new \Exception(Loc::getMessage('ERROR__MESSAGE_NOT_ADD_TO_TABLE'));
    }

    // AddMessage2Log([
    //   'WhatsappMessageTemplateGUID' => $this->WhatsappMessageTemplateGUID,
    //   'WhatsappMessageTemplateCode' => $this->WhatsappMessageTemplateCode,
    //   'WhatsappMessageBodyValues' => $this->WhatsappMessageBodyValues,
    // ]);

    // AddMessage2Log([
    //   // '$WhatsappMessageTemplateGUID' => $this->WhatsappMessageTemplateGUID,
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

    // return CBPActivityExecutionStatus::Closed;

    $rootActivity = $this->GetRootActivity();
    $documentId = $rootActivity->GetDocumentId();
    $runtime = CBPRuntime::GetRuntime();
    $documentService = $runtime->GetService('DocumentService');
    $taskService = $this->workflow->GetService('TaskService');

    $document = $documentService->getDocument($documentId);

    $companyId = $document['ID'];

    $activityName = $this->name;
    $workflowId = $this->workflow->getInstanceId();

    // return CBPActivityExecutionStatus::Closed;
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

    $this->WriteToTrackingService($exception);

    AddMessage2Log($exception, '!HandleFault');

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

    $this->includeModules();
    $this->Unsubscribe($this);
    
    AddMessage2Log('!Cancel');

    $obj = WorkflowSendedMessagesTable::query()->setSelect(['ID'])->fetchObject();

    if ($obj) {
      $obj->delete();
    }

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

    AddMessage2Log($eventHandler, '!Subscribe');

    if ($eventHandler == null) throw new Exception("eventHandler");

    $this->workflow->AddEventHandler($this->name, $eventHandler);
    AddMessage2Log('!SubscribeEEEE');
  }

  /**
   * Обработчик внешнего события
   *
   * @param array $arEventParameters
   * @throws Exception
   */
  public function OnExternalEvent($arEventParameters = array())
  {

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

    if (! is_array($arCurrentValues)) {
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
        // $arCurrentValues['Responsible'] = CBPHelper::UsersArrayToString(
        //   $arCurrentValues['Responsible'],
        //   $arWorkflowTemplate,
        //   $documentType
        // );
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

    AddMessage2Log([
      'documentType' => $documentType,
      'activityName' => $activityName,
      'arWorkflowTemplate' => $arWorkflowTemplate,
      'arWorkflowParameters' => $arWorkflowParameters,
      'arWorkflowVariables' => $arWorkflowVariables,
      'arCurrentValues' => $arCurrentValues,
      // 'arErrors' => $arErrors,
    ], '__SECOND');


    $r = CBPWorkflowTemplateLoader::GetList(
      array(),
      array(
        "DOCUMENT_TYPE" => array('crm', 'CCrmDocumentContact', 'CONTACT'),
      )
    );
    /* Актуальные данные из приложения */
    while ($res = $r->fetch()) {
      // if (str_contains(json_encode($res['TEMPLATE']), 'A82394_96494_15549_79836'))
      // AddMessage2Log($res, '__FIND');
    }

    // CBPHelper::UsersArrayToString();

    // $arErrors[] = array(
    //   'code' => 'Empty',
    //   'message' => json_encode($arWorkflowTemplate, JSON_UNESCAPED_UNICODE),
    //   // 'message' => Loc::getMessage('ERROR_NO_ASSIGN_NAME')
    // );

    // $arErrors[] = array(
    //   'code' => 'Empty',
    //   'message' => json_encode($activityName, JSON_UNESCAPED_UNICODE),
    //   // 'message' => Loc::getMessage('ERROR_NO_ASSIGN_NAME')
    // );

    if (empty($arCurrentValues['CrmContactId'])) {
      $arErrors[] = array(
        'code' => 'Empty',
        'message' => 'CrmContactId не заполнно'
        // 'message' => Loc::getMessage('ERROR_NO_ASSIGN_NAME')
      );
    }

    $arProperties = array(
      'CrmContactId' => $arCurrentValues['CrmContactId'],
      // 'WhatsappMessageTemplateGUID' => $arCurrentValues['WhatsappMessageTemplateGUID'],
      // 'Responsible' => CBPHelper::UsersStringToArray(
      //   $arCurrentValues['Responsible'],
      //   $documentType,
      //   $arErrors
      // ),
      // 'AssignmentName' => $arCurrentValues['AssignmentName'],
    );

    if ($arErrors) {
      return false;
    }

    // WorkflowTemplateTable::class

    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
      $arWorkflowTemplate,
      $activityName
    );
    $arCurrentActivity['Properties'] = $arProperties;

    return true;

    // $dialog = new PropertiesDialog(static::getFileName(), [
    //   'documentType' => $documentType,
    //   'activityName' => $activityName,
    //   'workflowTemplate' => $workflowTemplate,
    //   'workflowParameters' => $workflowParameters,
    //   'workflowVariables' => $workflowVariables,
    //   'currentValues' => $currentValues,
    // ]);

    // $extractingResult = static::extractPropertiesValues($dialog, static::getPropertiesDialogMap($dialog));
    // if (!$extractingResult->isSuccess()) {
    //   foreach ($extractingResult->getErrors() as $error) {
    //     $errors[] = [
    //       'code' => $error->getCode(),
    //       'message' => $error->getMessage(),
    //       'parameter' => $error->getCustomData(),
    //     ];
    //   }
    // } else {
    //   $errors = static::ValidateProperties(
    //     $extractingResult->getData(),
    //     new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser)
    //   );
    // }

    // if ($errors) {
    //   return false;
    // }

    // $currentActivity = &\CBPWorkflowTemplateLoader::FindActivityByName(
    //   $workflowTemplate,
    //   $activityName
    // );
    // $currentActivity['Properties'] = $extractingResult->getData();

    // return true;
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

    return json_decode($queryToWassup, true);
  }

  protected static function getArProperties()
  {
    return [
      'CrmContactId' => '',
      'WhatsappMessageTemplateGUID' => '',
      // 'WhatsappMessageTemplateCode' => '',
      'WhatsappMessageBodyValues' => '',
      'WhatsappChannelId' => '',
    ];
  }

  protected static function getArPropertiesTypes()
  {
    return [
      'CrmContactId' => [
        'Type' => FieldType::INT
      ],
      'WhatsappMessageTemplateGUID' => [
        'Type' => FieldType::STRING
      ],
      // 'WhatsappMessageTemplateCode' => [
      //   'Type' => FieldType::TEXT
      // ],
      'WhatsappMessageBodyValues' => [
        'Type' => FieldType::TEXT
      ],
      'WhatsappChannelId' => [
        'Type' => FieldType::STRING
      ],
    ];
  }
}
