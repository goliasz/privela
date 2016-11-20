<?php
/**
* 2015-2016 KOLIBERO.
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
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Privela extends Module
{
    protected $_path;

    protected $_domain;

    public function __construct()
    {
        $this->module_key = '1d0f87f5da56b0d9d6417743f41f007a';
        $this->name = 'privela';
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.8';
        $this->author = 'RBox24';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7');

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Privela');
        $this->description = $this->l('Prestashop integration with RBox24 Shopping Cart Recovery. Get 5 carts recovered free of charge each month.');

        $this->confirmUninstall = $this->l('Are you sure to remove?');

        $this->checkContent();

        $this->priGetDomain();
        $this->priGetPath();
    }

    private function checkContent()
    {
        if (!Configuration::get('PRI_EMAIL') || Configuration::get('PRI_EMAIL') == '') {
            $this->warning = $this->l('Administrator\'s email is required!');
        }
        if (!Configuration::get('PRI_API') || Configuration::get('PRI_API') == '') {
            $this->warning = $this->l('Valid Cart Rule Code is required!');
        }
        if (!Configuration::get('PRI_SENDER_EMAIL') || Configuration::get('PRI_SENDER_EMAIL') == '') {
            $this->warning = $this->l('Valid Sender email is required!');
        }
    }

    private function checkApiData()
    {
        if (Configuration::get('PRI_EMAIL') == '' || Configuration::get('PRI_API') == '' || Configuration::get('PRI_SENDER_EMAIL') == '') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * install.
     */
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('actionCustomerAccountAdd') ||
            !$this->registerHook('actionAuthentication') ||
            !$this->registerHook('actionValidateOrder') ||
            !$this->registerHook('actionCartSave') ||
            !$this->createContent()) {
            return false;
        }

        return true;
    }

    /**
     * uninstall.
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->deleteContent()) {
            return false;
        }

        return true;
    }

    private function createContent()
    {
        $secret = Tools::substr(md5(rand()), 0, 7);

        if (!Configuration::updateValue('PRI_API', '') ||
            !Configuration::updateValue('PRI_EMAIL', '') ||
            !Configuration::updateValue('PRI_SENDER_EMAIL', '') ||
            !Configuration::updateValue('PRI_PARTNER', '24') ||
            !Configuration::updateValue('PRI_SECRET_TEXT', $secret)) {
            return false;
        }

        return true;
    }

    private function deleteContent()
    {
        if (!Configuration::deleteByName('PRI_API') ||
            !Configuration::deleteByName('PRI_EMAIL') ||
            !Configuration::deleteByName('PRI_SENDER_EMAIL') ||
            !Configuration::deleteByName('PRI_PARTNER') ||
            !Configuration::deleteByName('PRI_SECRET_TEXT')) {
            return false;
        }

        return true;
    }

    /**
     * admin page.
     */
    public function getContent()
    {
        $message = '';
        $classlist = '';

        if (Tools::isSubmit('submit_'.$this->name)) {
            $arr = $this->saveContent();
            $message = $arr[0];
            $classlist = $arr[1];
        }

        if (Tools::isSubmit('export_'.$this->name)) {
            $message = $this->_exportPRI();
        }

        $this->displayContent($message, $classlist);

        $html = $this->display(__FILE__, 'views/templates/admin/privela.tpl');

        //return $this->display(__FILE__, 'views/templates/admin/privela.tpl');
        return $html.$this->display(__FILE__, 'views/templates/admin/prestui-0.6.0/ps-tags.tpl');
    }

    private function saveContent()
    {
        $message = '';
        $classlist = '';
        $arr = array();

        if (Configuration::updateValue('PRI_API', Tools::getValue('PRI_API')) &&
            Configuration::updateValue('PRI_EMAIL', Tools::getValue('PRI_EMAIL')) &&
            Configuration::updateValue('PRI_SENDER_EMAIL', Tools::getValue('PRI_SENDER_EMAIL')) &&
            Configuration::updateValue('PRI_SECRET_TEXT', Tools::getValue('PRI_SECRET_TEXT'))) {

            $message = 'Configuration saved';
            $classlist = 'module_confirmation conf confirm alert alert-success';

            if (!$this->checkApiData()) {
                $message = 'Fill out all configuration fields please. Two valid and existing email addresses and valid voucher code (Cart Rule Code) are necessary.';
                $classlist = 'module_error alert alert-danger';
            }

            if (!filter_var(Configuration::get('PRI_EMAIL'), FILTER_VALIDATE_EMAIL)) {
                // invalid emailaddress
                $message = "Administrator's email is invalid.";
                $classlist = 'module_error alert alert-danger';
            }

            if (!filter_var(Configuration::get('PRI_SENDER_EMAIL'), FILTER_VALIDATE_EMAIL)) {
                // invalid emailaddress
                $message = 'From email is invalid.';
                $classlist = 'module_error alert alert-danger';
            }

            $promo = $this->getPromoCodeActive(Configuration::get('PRI_API'));
            if (!empty($promo)) {
              if (!$promo[0]['id']) {
                  // invalid voucher
                  $message = 'Cart Rule Code (voucher) is not valid.';
                  $classlist = 'module_error alert alert-danger';
              }
            }
            else {
                $message = 'Valid Cart Rule Code (voucher) is required.';
                $classlist = 'module_error alert alert-danger';
            }
        } else {
            $message = 'Error';
            $classlist = 'module_error alert alert-danger';
        }

        $arr[] = $message;
        $arr[] = $classlist;

        return $arr;
    }

    private function getUptime()
    {
        //placeholder for future use
        $result = 'NO_INFO';

        return $result;
    }

    private function getShopSha($domain, $physical_uri, $virtual_uri)
    {
        $shop_sha = sha1($domain.$physical_uri.$virtual_uri);

        return $shop_sha;
    }

    private function displayContent($message, $classlist)
    {
        $this->context->smarty->assign(array(
            'module_name' => $this->name,
            'message' => $message,
            'classlist' => $classlist,
            'PRI_API' => Configuration::get('PRI_API'),
            'PRI_EMAIL' => Configuration::get('PRI_EMAIL'),
            'PRI_SENDER_EMAIL' => Configuration::get('PRI_SENDER_EMAIL'),
            'PRI_SECRET_TEXT' => Configuration::get('PRI_SECRET_TEXT'),
        ));
    }

    private function getProdViewCnt($products)
    {
        $query = 'SELECT p.ID_OBJECT PRODUCT_ID, SUM(pv.counter) VCNT FROM '._DB_PREFIX_.'page p, '._DB_PREFIX_.'page_viewed pv WHERE p.ID_PAGE_TYPE = '.(int) Page::getPageTypeByName('product').' AND p.ID_OBJECT in ('.$products.') AND p.ID_PAGE=pv.ID_PAGE GROUP BY p.ID_OBJECT';
        //PrestaShopLogger::addLog('Privela:getProdViewCnt: '.$query,1);

        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        return $values;
    }

    private function getPromoCodeActive($code)
    {
        $values = '';
        try {
            $query = 'SELECT id_cart_rule id, code, unix_timestamp(date_from) valid_from, unix_timestamp(date_to) valid_to, date_from, date_to, quantity, minimum_amount, active, date_upd, unix_timestamp(now())*1000 millis FROM '._DB_PREFIX_."cart_rule WHERE active=1 AND date_from<now() AND date_to>now() AND  code = '".$code."'";

            $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Privela:getPromoCodeActive: '.$e->getMessage(), 1);
            $values = $e->getMessage();
        }

        return $values;
    }

    private function getPromoCodeRedeemed($cart_id)
    {
        $values = '';
        try {
            $query = 'SELECT id_cart_rule id FROM '._DB_PREFIX_.'cart_cart_rule WHERE id_cart='.(int) $cart_id;
            $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Privela:getPromoCodeActive: '.$e->getMessage(), 1);
            $values = $e->getMessage();
        }

        return $values;
    }

    private function getProdCartCnt($cart_id, $product_id)
    {
        $query = 'SELECT SUM(`quantity`) FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart`='.(int) ($cart_id).' AND id_product='.(int) ($product_id);
        $value = (int) (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query));

        return $value;
    }

    public function hookDisplayAdminHomeQuickLinks()
    {
        $this->context->smarty->assign('privela', $this->name);
    }

    public function doPostRequestPri($data)
    {
        if ($this->checkApiData()) {
            //PrestaShopLogger::addLog('privela.php-doPostRequestPri',1);

            $arr = array('data' => $data);
            $data2 = Tools::jsonEncode($arr);

            //PrestaShopLogger::addLog('privela.php-doPostRequestPri. JSON: '.$data2,1);

            $epoch_milliseconds = time() * 1000;
            $ch = curl_init('https://mlaas-point-eu.appspot.com/_ah/api/pubapi/v1/msg?token='.number_format($epoch_milliseconds, 0, '.', ''));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.Tools::strlen($data2)));

            return curl_exec($ch);
        }

        return false;
    }

    public function priGetPath()
    {
        $this->_path = trim((Context::getContext()->shop->physical_uri), '/\\').'/';
        if ($this->_path{0} != '/') {
            $this->_path = '/'.$this->_path;
        }
        $this->_path = rawurlencode($this->_path);
        $this->_path = str_replace('%2F', '/', $this->_path);
        $this->_path = str_replace('%7E', '~', $this->_path);
    }

    public function priGetDomain($shared_urls = null)
    {
        $r = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';

        if (!preg_match($r, Tools::getHttpHost(false, false), $out) || !isset($out[4])) {
            return false;
        }

        if (preg_match('/^(((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]{1}[0-9]|[1-9]).)'.'{1}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]).)'.'{2}((25[0-5]|2[0-4][0-9]|[1]{1}[0-9]{2}|[1-9]{1}[0-9]|[0-9]){1}))$/', $out[4])) {
            return false;
        }
        if (!strstr(Tools::getHttpHost(false, false), '.')) {
            return false;
        }

        $domain = false;
        if ($shared_urls !== null) {
            foreach ($shared_urls as $shared_url) {
                if ($shared_url != $out[4]) {
                    continue;
                }
                if (preg_match('/^(?:.*\.)?([^.]*(?:.{2,4})?\..{2,3})$/Ui', $shared_url, $res)) {
                    $domain = '.'.$res[1];
                    break;
                }
            }
        }
        if (!$domain) {
            $domain = $out[4];
        }
        $this->_domain = $domain;
    }

    public function priCreateCookie($name, $value)
    {

        //setcookie($name, $value, time() + 3650 * 86400, $this -> _path, $this ->_domain);
        $this->context->cookie->__set($name, $value);
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $uptime = $this->getUptime();

        $context = Context::getContext();
        $email = $params['newCustomer']->email;

        if (isset($params['newCustomer']->is_guest) && $params['newCustomer']->is_guest == 1) {
            $context->cookie->__set('guestemail', $email);
        }

        $name = $params['newCustomer']->firstname;
        $surname = $params['newCustomer']->lastname;
        $fullname = $name.' '.$surname;
        //$optin = $params ['newCustomer'] -> optin;
        $id_gender = $params ['newCustomer']->id_gender;
        $id_lang = $params ['newCustomer']->id_lang;
        $birthday = '';
        $owner = Configuration::get('PRI_EMAIL');
        $apiSecret = Configuration::get('PRI_API');
        $senderEmail = Configuration::get('PRI_SENDER_EMAIL');
        $secret = Configuration::get('PRI_SECRET_TEXT');
        $partner = Configuration::get('PRI_PARTNER');

        $phone = '';
        $landline = '';
        $address = '';

        $shop_physical_uri = $this->context->shop->physical_uri;
        $shop_virtual_uri = $this->context->shop->virtual_uri;
        $shop_domain = $this->context->shop->domain;

        $data1 = array(
          'action' => 'ACCOUNT_ADD',
          'source' => 'presta',
          'hook' => 'hookActionCustomerAccountAdd',
          'uptime' => $uptime,

          'secret' => $secret,
          'partner' => $partner,
          'sender_email' => $senderEmail,
          'version' => $this->version,
          'promo_config' => $apiSecret,
          'shop_owner' => $owner,
          'shop_sha' => $this->getShopSha($shop_domain, $shop_physical_uri, $shop_virtual_uri),
          'id' => sha1(Tools::strtolower($owner)),
          'millis' => round(time() * 1000),

          'customer' => array(
            'customer_id' => $this->context->customer->id,
            'id_lang' => $id_lang,
            'id_gender' => $id_gender,
            'email' => $email,
            'name' => $fullname,
            'company' => '',
            'phone' => $phone,
            'address' => array(
              'streetAddress' => $address,
              'zipCode' => $params ['_POST']['postcode'],
              'city' => $params ['_POST']['city'],
              'country' => Country::getIsoById($params ['_POST']['id_country']),
            ),

            'birthday' => $birthday,
            'properties' => array(
              'landline' => $landline,
            ),
          ),
        );

        $json1 = Tools::jsonEncode($data1);
        $this->doPostRequestPri($json1);
        $this->priCreateCookie('pricookie', $email);
    }

    public function hookActionAuthentication($params)
    {
        $uptime = $this->getUptime();

        $key = 'PRIeventID';
        $context = Context::getContext();
        $context->cookie->PRIeventID = '';
        $context->cookie->__unset($key);

        $email = $params['cookie']->email;
        $name = $this->context->customer->firstname;
        $surname = $this->context->customer->lastname;
        $birthday = '';
        $fullname = $name.' '.$surname;
        $id_lang = $this->context->customer->id_lang;
        $id_gender = $this->context->customer->id_gender;

        $owner = Configuration::get('PRI_EMAIL');
        $apiSecret = Configuration::get('PRI_API');
        $senderEmail = Configuration::get('PRI_SENDER_EMAIL');
        $secret = Configuration::get('PRI_SECRET_TEXT');
        $partner = Configuration::get('PRI_PARTNER');

        $shop_name = $this->context->shop->name;

        $shop_physical_uri = $this->context->shop->physical_uri;
        $shop_virtual_uri = $this->context->shop->virtual_uri;
        $shop_domain = $this->context->shop->domain;

        $promocode = $this->getPromoCodeActive($apiSecret);

        $data2 = array(
            'source' => 'presta',
            'hook' => 'hookActionAuthentication',
            'action' => 'AUTHENTICATE',
            'uptime' => $uptime,

            'secret' => $secret,
            'partner' => $partner,
            'sender_email' => $senderEmail,
            'version' => $this->version,
            'shop_owner' => $owner,
            'shop_sha' => $this->getShopSha($shop_domain, $shop_physical_uri, $shop_virtual_uri),
            'id' => sha1(Tools::strtolower($owner)),

            'promo' => $promocode,
            'promo_config' => $apiSecret,

            'shop_name' => $shop_name,
            'shop_physical_uri' => $shop_physical_uri,
            'shop_virtual_uri' => $shop_virtual_uri,
            'shop_domain' => $shop_domain,
            'millis' => round(time() * 1000),

            'customer' => array(
              'customer_id' => $this->context->customer->id,
              'name' => $fullname,
              'email' => $email,
              'birthday' => $birthday,
              'id_lang' => $id_lang,
              'id_gender' => $id_gender,
            ),
        );

        $json2 = Tools::jsonEncode($data2);
        $this->doPostRequestPri($json2);

        $this->priCreateCookie('pricookie', $email);
    }

    public function hookActionValidateOrder($params)
    {
        $uptime = $this->getUptime();

        $email = $this->context->cookie->email;
        $guest = $params['customer']->is_guest;

        $cart_total = $params['cart']->getOrderTotal(true);
        $cart_shipping = $params['cart']->getTotalShippingCost(null, true);

        $value = $params['order']->getOrdersTotalPaid();
        $products = $params['cart']->getProducts();
        $productslist = '';
        $items = array();
        foreach ($products as $product) {
            $productslist = $productslist.$product['id_product'].',';
            $items[] = array(
                              'id' => $product['id_product'],
                              'quantity' => $this->getProdCartCnt($params['cart']->id, $product['id_product']),
                            );
        }
        $finalproductslist = Tools::substr($productslist, 0, -1);

        if ($guest) {
            $purchasetype = 'Guest purchase';
        } else {
            $purchasetype = 'Register user purchase';
        }

        $owner = Configuration::get('PRI_EMAIL');
        $apiSecret = Configuration::get('PRI_API');
        $senderEmail = Configuration::get('PRI_SENDER_EMAIL');
        $secret = Configuration::get('PRI_SECRET_TEXT');
        $partner = Configuration::get('PRI_PARTNER');

        $promocode = $this->getPromoCodeActive($apiSecret);

        $shop_physical_uri = $this->context->shop->physical_uri;
        $shop_virtual_uri = $this->context->shop->virtual_uri;
        $shop_domain = $this->context->shop->domain;

        //$dt = new DateTime('NOW');

        $data4 = array(
            'source' => 'presta',
            'hook' => 'hookActionValidateOrder',
            'action' => 'PURCHASE',
            'uptime' => $uptime,

            'secret' => $secret,
            'partner' => $partner,
            'sender_email' => $senderEmail,
            'version' => $this->version,
            'millis' => round(time() * 1000),
            'shop_sha' => $this->getShopSha($shop_domain, $shop_physical_uri, $shop_virtual_uri),
            'shop_owner' => $owner,
            'id' => sha1(Tools::strtolower($owner)),

            'promo' => $promocode,
            'promo_redeemed' => $this->getPromoCodeRedeemed($params['cart']->id),
            'promo_config' => $apiSecret,

            'customer' => array(
              'email' => $email,
              'customer_id' => $params['cart']->id_customer,
            ),
            'cart' => array(
              'cartid' => $params['cart']->id,
              'cart_id' => $params['cart']->id,
              'products_comma' => $finalproductslist,
              'products_views' => $this->getProdViewCnt($finalproductslist),
              'products_quantity' => $items,
              'value' => $value,
              'description' => $purchasetype,
              'cart_total' => $cart_total,
              'cart_shipping' => $cart_shipping,
              'order_total_paid' => $value,
            ),
        );

        $json4 = Tools::jsonEncode($data4);
        $this->doPostRequestPri($json4);
    }

    public function hookActionCartSave($params)
    {
        //PrestaShopLogger::addLog('privela-cartsave-001', 1);
        $uptime = $this->getUptime();

        $guestemail = '';
        if ($this->context->cookie->__get('pricookie')) {
            $guestemail = $this->context->cookie->__get('pricookie');
        }

        $context = Context::getContext();
        $email = $context->customer->email;
        $url = Context::getContext()->shop->domain.Context::getContext()->shop->physical_uri;

        if (!$context->customer->isLogged() && $email == '') {
            $email = $guestemail;
        }

        if ($context->cart && $context->cart->id) {
            if (($params['cart']->nbProducts()) > 0) {
                $products = $params['cart']->getProducts();
                $prices = $params['cart']->getOrderTotal(true);
                $shipping = $params['cart']->getTotalShippingCost(null, true);
                $productslist = '';
                $items = array();
                foreach ($products as $product) {
                    $productslist = $productslist.$product['id_product'].',';
                    $images = Image::getImages((int) $context->cart->id_lang, (int) $product['id_product']);
                    $images_arr = array();
                    if (count($images) > 0) {
                        foreach ($images as $image) {
                            $images_arr [] = array(
                            'id_image' => $image['id_image'],
                            'position' => $image['position'], );
                        }
                    }
                    $items[] = array(
                                     'id' => $product['id_product'],
                                     'quantity' => $params['cart']->getNbProducts($product['id_product']),
                                     'name' => $product['name'],
                                     'description_short' => $product['description_short'],
                                     'link_rewrite' => $product['link_rewrite'],
                                     'category' => $product['category'],
                                     'available_now' => $product['available_now'],
                                     'on_sale' => $product['on_sale'],
                                     'price' => $product['price'],
                                     'quantity' => $product['quantity'],
                                     'id_manufacturer' => $product['id_manufacturer'],
                                     'id_supplier' => $product['id_supplier'],
                                     'minimal_quantity' => $product['minimal_quantity'],
                                     'images' => $images_arr,
                                   );
                }
                $finalproductslist = Tools::substr($productslist, 0, -1);

                $owner = Configuration::get('PRI_EMAIL');
                $apiSecret = Configuration::get('PRI_API');
                $senderEmail = Configuration::get('PRI_SENDER_EMAIL');
                $secret = Configuration::get('PRI_SECRET_TEXT');
                $partner = Configuration::get('PRI_PARTNER');

                $promocode = $this->getPromoCodeActive($apiSecret);

                $shop_physical_uri = $this->context->shop->physical_uri;
                $shop_virtual_uri = $this->context->shop->virtual_uri;
                $shop_domain = $this->context->shop->domain;

                $data5 = array(
                    'source' => 'presta',
                    'hook' => 'hookActionCartSave',
                    'id' => sha1(Tools::strtolower($owner)),
                    'action' => 'CART',
                    'uptime' => $uptime,

                    'millis' => round(time() * 1000),
                    'shop_sha' => $this->getShopSha($shop_domain, $shop_physical_uri, $shop_virtual_uri),

                    'promo' => $promocode,
                    'promo_config' => $apiSecret,

                    'secret' => $secret,
                    'partner' => $partner,
                    'sender_email' => $senderEmail,
                    'version' => $this->version,
                    'shop_owner' => $owner,
                    'shop_url' => $url,
                    'shop_name' => Context::getContext()->shop->name,
                    'shop_id' => $context->cart->id_shop,
                    'customer' => array(
                      'customer_id' => $context->cart->id_customer,
                      'email' => $email,
                      'guestemail' => $guestemail,
                    ),
                    'cart' => array(
                      'cart_id' => $context->cart->id,
                      'lang_iso' => $context->language->getIsoById($context->cart->id_lang),
                      'currency_id' => $context->cart->id_currency,
                      'products_comma' => $finalproductslist,
                      'products_views' => $this->getProdViewCnt($finalproductslist),
                      'order_total_amount' => round($prices, 2),
                      'order_total_shipping_cost' => round($shipping, 2),
                      'items' => $items,
                    ),
                );

                $json5 = Tools::jsonEncode($data5);
                $this->doPostRequestPri($json5);

                //PrestaShopLogger::addLog('privela-cartsave, result: '.$result5, 1);
            }
        }
    }
}
