<?
use Bitrix\Main\Localization\Loc;

$iframeUrl = 'https://' . $_SERVER['SERVER_NAME'];
$iframeUrl .= '/mywebstor_wazzup_integration/apps/getter_whatsapp_message/';
?>

<tbody>
  <tr>
    <td>
      <iframe src="<?= $iframeUrl ?>" frameborder="0" width="850" height="500"></iframe>
    </td>
  </tr>
  <tr>
    <td align="right" width="40%"><span class="adm-required-field">ID Контакта:</span>
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
  <!-- <tr>
    <td align="right" width="40%"><span class="adm-required-field">WhatsappMessageBodyValues:</span>
    </td>
    <td width="60%">
      <?= CBPDocument::ShowParameterField(
        'string',
        'WhatsappMessageBodyValues',
        $arCurrentValues['WhatsappMessageBodyValues']
      )
      ?>
    </td>
  </tr> -->
  <tr>
    <td align="right" width="40%"><span class="adm-required-field">WhatsappMessageTemplateGUID:</span>
    </td>
    <td width="60%">
      <input type="text" name="WhatsappMessageTemplateGUID" id="WhatsappMessageTemplateGUID"
        value="<?= htmlspecialcharsbx($arCurrentValues['WhatsappMessageTemplateGUID']) ?>" size="50">
      <input type="button" value="..." onclick="BPAShowSelector('WhatsappMessageTemplateGUID', 'string');">

      <!-- <?=
            CBPDocument::ShowParameterField(
              'user',
              'WhatsappMessageTemplateGUID',
              $arCurrentValues['WhatsappMessageTemplateGUID']
            )
            ?> -->
    </td>
  </tr>
</tbody>