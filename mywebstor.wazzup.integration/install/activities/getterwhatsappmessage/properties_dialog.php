<?
use Bitrix\Main\Localization\Loc;

$iframeUrl = 'https://' . $_SERVER['SERVER_NAME'];
$iframeUrl .= '/mywebstor_wazzup_integration/apps/getter_whatsapp_message/';
?>

<tbody>
  <tr>
    <td align="right" width="40%"><span class="adm-required-field">Поле не является функциональным, используется для получения шаблона переменных:</span>
    </td>
    <td width="60%">
      <?= CBPDocument::ShowParameterField(
        'string',
        'CrmContactId',
        $arCurrentValues['CrmContactId']
      )
      ?>
    </td>
  </tr>
   <tr>
    <td>
      <iframe src="<?= $iframeUrl ?>" frameborder="0" width="850" height="600"></iframe>
    </td>
  </tr>
</tbody>