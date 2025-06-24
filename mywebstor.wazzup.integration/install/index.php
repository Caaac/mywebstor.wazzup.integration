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

        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
        $this->InstallOptions();

        return true;
    }

    public function DoUninstall()
    {
        if (!check_bitrix_sessid())
            return false;

        global $APPLICATION, $USER, $DB, $step;
        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("MYWEBSTOR_UNINSTALL_TITLE", array("#MODULE_NAME#" => $this->MODULE_NAME)), __DIR__ . "/unstep1.php");
        } elseif ($step === 2) {

            if (!array_key_exists('savedata', $_REQUEST) || $_REQUEST['savedata'] != 'Y') {
                $this->UnInstallDB();
            }

            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallOptions();

            ModuleManager::unRegisterModule($this->MODULE_ID);
        }

        return true;
    }


    function InstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/install.sql');
        if (is_array($this->errors)) {
            $APPLICATION->ThrowException(implode('<br />', $this->errors));
            return false;
        }
        return true;
    }

    function UnInstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/uninstall.sql');
        if (is_array($this->errors)) {
            $APPLICATION->ThrowException(implode('<br />', $this->errors));
            return false;
        }

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
            __DIR__ . "/activities",
            Application::getDocumentRoot() . "/bitrix/activities/custom/",
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
                IO\File::deleteFile(
                    Application::getDocumentRoot() . '/bitrix/admin/' . $item->getName()
                );
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

        $dirPath = Application::getDocumentRoot() . "/local/modules/mywebstor.wazzup.integration/install/activities";
        $dir = new IO\Directory($dirPath);

        foreach ($dir->getChildren() as $item) {
            if ($item->isDirectory()) {
                IO\Directory::deleteDirectory(
                    Application::getDocumentRoot() . '/bitrix/activities/custom/' . $item->getName() . '/'
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

    public function InstallOptions()
    {
        Bitrix\Main\Config\Option::set('mywebstor.wazzup.integration', 'auto_send_notification_settings', json_encode([
            'ACTIVE' => 'N',
            'DATE' => '1970-01-01T12:00:00+03:00',
            'DIFF' => 1,
            'DISABLED_DOCTORS' => [],
        ]));
        return true;
    }

    public function UnInstallOptions()
    {
        Bitrix\Main\Config\Option::delete('mywebstor.wazzup.integration');
        return true;
    }
}
