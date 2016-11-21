{*
* 2015-2016 KOLIBERO
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to piotr.goliasz@kolibero.eu so we can send you a copy immediately.
*
*  @author    KOLIBERO Piotr Goliasz <piotr.goliasz@kolibero.eu>
*  @copyright 2015-2016 KOLIBERO
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  Registered Trademark & Property of KOLIBERO
*}

<script src="/modules/privela/js/riot+compiler.js"></script>

<script>
function handleError(error) {
  //alert(error);
  var jerror = JSON.parse(error);

  var fa = document.getElementById('form_alert_x');
  fa.style.display = 'inherit';
  fa.innerHTML = '<ps-alert alert-class="alert '+jerror[0].code+'"><div class="alert alert '+jerror[0].code+'"> <button type="button" class="close" data-dismiss="alert">×</button> '+jerror[0].message+' </div></ps-alert>';
}

function submitSave() {
    var secretText = $("input[name='PRI_SECRET_TEXT']").val();
    var promo = $("input[name='PRI_API']").val();
    var owner_mail = $("input[name='PRI_EMAIL']").val();
    var sender_mail = $("input[name='PRI_SENDER_EMAIL']").val();
    //alert(secretText);

    var aurl = "{$current|escape:'html':'UTF-8'}&configure={$module_name|escape:'htmlall':'UTF-8'}&token={$token|escape:'html':'UTF-8'}";

    // Ajax call with secure token
    $.ajax({   type: "POST",
               url: aurl,
               data: { ajax: true, action: "SaveContent", PRI_SECRET_TEXT: secretText, PRI_API: promo, PRI_SENDER_EMAIL: sender_mail, PRI_EMAIL: owner_mail },
               success: function(out) { handleError(out); },
               error: function (xhr, ajaxOptions, thrownError) { alert(thrownError); }  });
}
</script>

<ps-panel icon="icon-cogs" header="Configuration">
<br>

<form class="form-horizontal">
  <ps-alert style="display:none" id="form_alert">"{$message|escape:'htmlall':'UTF-8'}"</ps-alert>

  <div id="form_alert_x"></div>

  <ps-input-text name="PRI_EMAIL" placeholder="Administrator e-mail..." label="Administrators E-mail" required-input="true" value="{$PRI_EMAIL|escape:'htmlall':'UTF-8'}" help="Required to get access to management console and to receive important information about module operation, performance and status. It is important email exists otherwise you won't receive management console access information." hint="Required administrator email">
  </ps-input-text>

  <ps-input-text name="PRI_API" placeholder="Promo code..." label="Cart Rule Code" required-input="true" value="{$PRI_API|escape:'htmlall':'UTF-8'}" help="Cart Rule Code (voucher) is used to track cart recovery success rate and boost recovery. Once valid code is provided and module is enabled process of monitoring carts abandonment starts and recovery emails are being sent to customers.<BR>Just after module starts collecting traffic first welcome email is sent with management console access details.<BR>Once code gets invalid an email notification is sent to the administrator and sending recovery emails is suspended until existing or new code becomes valid again." hint="Required cart rule code">
  </ps-input-text>

  <ps-input-text name="PRI_SENDER_EMAIL" placeholder="From e-mail..." label="From E-mail" required-input="true" value="{$PRI_SENDER_EMAIL|escape:'htmlall':'UTF-8'}" help="Used to send recovery messages to customers who abandoned their carts. It is important that from email exists. Just after successful setup we will send a confirmation request to this email address. After successful confirmation sending messages will be activated and we will start monitoring process performance." hint="Required sender email">
  </ps-input-text>

  <ps-input-text name="PRI_SECRET_TEXT" label="Secret Text" required-input="true" value="{$PRI_SECRET_TEXT|escape:'htmlall':'UTF-8'}" help="We have generated a random text for you but you can enter here anything you wish. We will use it to verify if data is coming from valid and trusted source." hint="Secret text">
  </ps-input-text>
</form>

<ps-panel-footer-submit onClick="submitSave();" label="Save" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" title="Save"><ps-panel-footer-submit>

</ps-panel>

