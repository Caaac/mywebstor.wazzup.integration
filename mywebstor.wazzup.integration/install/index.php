<?

use Bitrix\Main\IO;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

IncludeModuleLangFile(__FILE__);

class mywebstor_wazzup_integration extends CModule
{
    public $MODULE_ID = "mywebstor.wazzup.integration";
    public $errors = '';
    static $events = [
        [
            "FROM_MODULE" => "rest",
            "FROM_EVENT" => "onRestServiceBuildDescription",
            "TO_CLASS" => "CMywebstorWazzupIntegrationRestService",
            "TO_FUNCTION" => "onRestServiceBuildDescription",
            "VERSION" => "1"
        ],
        [
            "FROM_MODULE" => "main",
            "FROM_EVENT" => "OnBuildGlobalMenu",
            "TO_CLASS" => "CMywebstorWazzupIntegrationEvents",
            "TO_FUNCTION" => "addGroupAtAdminPanel",
            "VERSION" => "1"
        ]
    ];

    public function __construct()
    {
        if (file_exists(__DIR__ . "/version.php")) {
            $arModuleVersion = array();

            include_once(__DIR__ . "/version.php");
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME = Loc::getMessage("MYWEBSTOR_WASSUP_INTEGRATION_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("MYWEBSTOR_WASSUP_INTEGRATION_DESCRIPTION");
            $this->PARTNER_NAME = Loc::getMessage("MYWEBSTOR_WASSUP_INTEGRATION_PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("MYWEBSTOR_WASSUP_INTEGRATION_PARTNER_URI");
        }
        return true;
    }

    public function DoInstall()
    {
        if (!check_bitrix_sessid())
            return false;

        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallFiles();
        $this->InstallEvents();

        return true;
    }

    public function DoUninstall()
    {
        if (!check_bitrix_sessid())
            return false;

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        return true;
    }


    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . "/admin",
            Application::getDocumentRoot() . "/bitrix/admin/",
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . "/components",
            Application::getDocumentRoot() . "/local/components/",
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . "/public/mywebstor_wazzup_integration",
            Application::getDocumentRoot() . "/mywebstor_wazzup_integration/",
            true,
            true
        );

        return true;
    }

    public function UnInstallFiles()
    {
        IO\Directory::deleteDirectory(Application::getDocumentRoot() . "/mywebstor_wazzup_integration/");

        $dirPath = Application::getDocumentRoot() . "/local/modules/mywebstor.wazzup.integration/install/admin";
        $dir = new IO\Directory($dirPath);

        foreach ($dir->getChildren() as $item) {
            if (!$item->isDirectory()) {
                IO\File::deleteFile(Application::getDocumentRoot() . '/bitrix/admin/' . $item->getName());
            }
        }

        $dirPath = Application::getDocumentRoot() . "/local/modules/mywebstor.wazzup.integration/install/components";
        $dir = new IO\Directory($dirPath);

        foreach ($dir->getChildren() as $item) {
            if ($item->isDirectory()) {
                IO\Directory::deleteDirectory(
                    Application::getDocumentRoot() . '/local/components/' . $item->getName() . '/'
                );
            }
        }

        return true;
    }


    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event)
            $eventManager->registerEventHandlerCompatible($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event)
            $eventManager->unRegisterEventHandler($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
        return true;
    }


    public function DirPath()
    {
        return Application::getDocumentRoot() . '/upload/' . $this->MODULE_ID . '/excel/';
    }
}
