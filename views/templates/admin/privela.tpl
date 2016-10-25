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

<div class="bootstrap"> <div class="{$classlist|escape:'htmlall':'UTF-8'}" > <button type="button" class="close" data-dismiss="alert">&times;</button>{$message|escape:'htmlall':'UTF-8'}</div></div>
<fieldset>
  <legend>{l s='Settings' mod='privela'}</legend>
  <form method="post">
	  <p>
      <label for="PRI_EMAIL">{l s='Administrator\'s E-mail (required):' mod='privela'}</label>
      <input id="PRI_EMAIL" name="PRI_EMAIL" type="text" value="{$PRI_EMAIL|escape:'htmlall':'UTF-8'}" />
    </p>
    <p>
    Required to get access to management console and to receive important information about module operation, performance and status. It is important email exists otherwise you won't receive management console access information. 
    </p>
    <p>
      <label for="PRI_API">{l s='Cart Rule Code (required):' mod='privela'}</label>
      <input id="PRI_API" name="PRI_API" type="text" value="{$PRI_API|escape:'htmlall':'UTF-8'}" />
    </p>
    <p>
    Cart Rule Code (voucher) is used to track cart recovery success rate and boost recovery. Once valid code is provided and module is enabled process of monitoring carts abandonment starts and recovery emails are being sent to customers.
<BR>Just after module starts collecting traffic first welcome email is sent with management console access details.
<BR>Once code gets invalid an email notification is sent to the administrator and sending recovery emails is suspended until existing or new code becomes valid again.
    </p>
    <p>
      <label for="PRI_SENDER_EMAIL">{l s='From E-mail (required):' mod='privela'}</label>
      <input id="PRI_SENDER_EMAIL" name="PRI_SENDER_EMAIL" type="text" value="{$PRI_SENDER_EMAIL|escape:'htmlall':'UTF-8'}" />
    </p>
    <p>
    Used to send recovery messages to customers who abandoned their carts. It is important that from email exists. Just after successful setup we will send a confirmation link to this email address. After successful confirmation sending messages will be activated and we will start monitoring process performance.  
    </p>
    <p>
      <label for="PRI_SECRET_TEXT">{l s='Secret text:' mod='privela'}</label>
      <input id="PRI_SECRET_TEXT" name="PRI_SECRET_TEXT" type="text" value="{$PRI_SECRET_TEXT|escape:'htmlall':'UTF-8'}" />
    </p>
    <p>
    We have generated a random text for you but you can enter here anything you wish. We will use it to verify if data is coming from valid and trusted source.
    </p>
    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='privela'}" class="button" />
    </p>
  </form>
</fieldset>
<br>

<!-- end privela admin page -->
