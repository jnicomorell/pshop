<?php

require_once(dirname(__FILE__) . "/classes/profileadvanced.class.php");
require_once _PS_MODULE_DIR_ . 'profileadv/src/Entity/Pets.php';

/**
 * 2011 - 2019 StorePrestaModules SPM LLC.
 *
 * MODULE profileadv
 *
 * @author    SPM <spm.presto@gmail.com>
 * @copyright Copyright (c) permanent, SPM
 * @license   Addons PrestaShop license limitation
 * @version   1.2.9
 * @link      http://addons.prestashop.com/en/2_community-developer?contributor=790166
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

class profileadv extends Module
{
    private $_is16;
    private $_is15;
    private $_is_cloud;

    private $_template_name;
    private $_template_path = 'views/templates/hooks/';

    public function __construct()
    {

        $this->name = 'profileadv';
        $this->tab = 'content_management';
        $this->author = 'SPM';
        $this->version = '1.2.9';

        parent::__construct(); // The parent construct is required for translations

        if (defined('_PS_HOST_MODE_'))
            $this->_is_cloud = 1;
        else
            $this->_is_cloud = 0;

        //$this->_is_cloud = 1;

        if ($this->_is_cloud) {
            $this->path_img_cloud = "modules/" . $this->name . "/upload/";
        } else {
            $this->path_img_cloud = "upload/";
        }


        $this->bootstrap = true;
        $this->need_instance = 0;

        $this->_is16 = 1;

        $this->_is15 = 1;

        $this->confirmUninstall = $this->l('Are you sure you want to remove it ? Be careful, all your configuration and your data will be lost');


        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Mis mascotas');
        $this->description = $this->l('Add pets to customer account');

        $this->initContext();


        if (version_compare(_PS_VERSION_, '1.7.4', '>') && version_compare(_PS_VERSION_, '1.7.4.2', '<')) {
            $this->_template_name = '';
        } else {
            $this->_template_name = $this->_template_path;
        }

        ## prestashop 1.7 ##
        $smarty = $this->context->smarty;
        $smarty->assign($this->name . 'is17', version_compare(_PS_VERSION_, '1.7', '>') ? 1 : 0);
        ## prestashop 1.7 ##
    }

    private function initContext()
    {
        $this->context = Context::getContext();

        $this->context->currentindex = isset(AdminController::$currentIndex) ? AdminController::$currentIndex : 'index.php?controller=AdminModules';
    }



    public function getokencron()
    {
        $_token_cron_shop = sha1(_COOKIE_KEY_ . $this->name);
        return $_token_cron_shop;
    }


    public function install()
    {
        $tab = new Tab();
        $tab->class_name = 'Adminprofileadv';
        $tab->module = 'profileadv';
        $tab->name[1] = 'profileadv';
        $tab->id_parent = 2;
        $tab->active = 0;
        if (!$tab->save()) {
            return false;
        }

        $tab2 = new Tab();
        $tab2->class_name = 'Adminprofileadvadd';
        $tab2->module = 'profileadv';
        $tab2->name[1] = 'profileadvadd';
        $tab2->id_parent = 2;
        $tab2->active = 0;
        if (!$tab2->save()) {
            return false;
        }

        $tab3 = new Tab();
        $tab3->class_name = 'AdminProfileAdvList';
        $tab3->module = 'profileadv';
        $tab3->name[1] = 'Mascotas';
        $tab3->id_parent = 2;
        $tab3->active = 1;
        $tab3->icon = 'view_comfy';
        $tab3->id_parent = (int) Tab::getIdFromClassName('SELL');
        if (!$tab3->save()) {
            return false;
        }

        Configuration::updateValue($this->name . 'padv_home', 1);
        Configuration::updateValue($this->name . 'padv_footer', 1);

        Configuration::updateValue($this->name . 'padv_left', 1);


        Configuration::updateValue($this->name . 'shoppers_blc', 5);
        Configuration::updateValue($this->name . 'perpage_shoppers', 16);


        if (
            !parent::install() or
            !$this->registerHook('header') or
            !$this->installTable() or
            !$this->registerHook('customerAccount') or
            !$this->registerHook('myAccountBlock') or
            !$this->registerHook('ModuleRoutes') or
            !$this->registerHook('displayAdminCustomers') or
            !$this->registerHook('displayAdminOrder') or
            !$this->registerHook('displayProductAdditionalInfo') or
            !$this->registerHook('displayBackOfficeHeader') or
            !$this->registerHook('addWebserviceResources') or
            !$this->registerHook('actionAuthentication') or
            !$this->registerHook('actionCustomerAccountAdd')

        )
            return false;

        return true;
    }

    public function installTable()
    {

        $db = Db::getInstance();
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'avatar2customer` (
                            `id` INT NOT NULL AUTO_INCREMENT,
                            `id_customer` int(11) NOT NULL,
                            `avatar` text,
                            `avatar_thumb` text,
                            `is_show` int(11) NOT NULL default \'0\',
                            `reference` TINYTEXT NOT NULL, 
                            `type` INT NOT NULL,
                            `esterilized` INT NOT NULL, 
                            `name` VARCHAR(50) NOT NULL, 
                            `genre` INT NOT NULL,
                            `birth` DATE NOT NULL, 
                            `breed` VARCHAR(50) NOT NULL,
                            `weight` FLOAT(10) NOT NULL, 
                            `feeding` INT NOT NULL,
                            `activity` INT NOT NULL,
                            `physical_condition` INT NOT NULL,
                            `pathology` VARCHAR(255) DEFAULT NULL, 
                            `allergies` VARCHAR(255) DEFAULT NULL, 
                            `amount` INT NOT NULL,
                            `comment` text,
                            `message` text,
                            `date_add` TIMESTAMP NOT NULL, 
                            `date_upd` TIMESTAMP ON update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                            `active` BOOLEAN NOT NULL DEFAULT TRUE, 
							KEY `id` (`id`)
							) ENGINE=' . (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : "MyISAM") . ' DEFAULT CHARSET=utf8';
        $db->Execute($query);
        return true;
    }

    public function uninstall()
    {

        if (!parent::uninstall() or !$this->uninstallTable() or $this->uninstallTab() or !$this->uninstallTable2())
            return false;


        return true;
    }

    public function uninstallTable()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'avatar2customer');
        return true;
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('Adminprofileadv');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);
        $tab->delete();

        $tabId = (int) Tab::getIdFromClassName('Adminprofileadvadd');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }


    protected function addBackOfficeMedia()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/font-custom.min.css');
        // JS files
        $this->context->controller->addJs($this->_path . 'views/js/menu16.js');
    }


    public function hookDisplayAdminCustomers($params)
    {
        include_once _PS_MODULE_DIR_ . $this->name . '/classes/petlist.class.php';
        $pets = new petList();
        $petList = $pets->getPetsListFromCustomer((int)$this->context->customer->id);

        $this->context->smarty->assign(array(
            'petList' => (array) $petList['pets'],
            'id_customer' => (int)$this->context->customer->id
        ));

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/customer_account.tpl');
    }

    public function hookDisplayAdminOrder($params)
    {
        include_once _PS_MODULE_DIR_ . $this->name . '/classes/petlist.class.php';
        $order = new Order($params['id_order']);
        $customer = new Customer($order->id_customer);

        $pets = new petList();
        $petList = $pets->getPetsListFromCustomer((int)$customer->id);

        $this->context->smarty->assign(array(
            'petList' => (array) $petList['pets'],
            'id_customer' => (int)$customer->id
        ));

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/customer_account.tpl');
    }

    public function hookDisplayProductAdditionalInfo($params)
    {

        include_once _PS_MODULE_DIR_ . $this->name . '/classes/petlist.class.php';
        $current_product = new Product((int)Tools::getValue('id_product'));

        $product_categories = array(18, 28, 43, 88, 89, 90, 151, 152, 153, 156, 157, 158, 159);

        if (in_array($current_product->id_category_default, $product_categories)) {
            $category = new Category((int)$current_product->id_category_default, (int)$this->context->language->id);

            //There are combinations
            if ($params['product']['id_product_attribute'] > 0) {
                $combination = $current_product->getAttributeCombinationsById((int)$params['product']['id_product_attribute'], $this->context->language->id);

                $current_product->price = number_format((float)($combination[0]['price'] * 1.10), 2, '.', '');
                $current_product->weight_kg = $combination[0]['weight'] * 1000;
            } elseif ($id_product = (int)Tools::getValue('id_product')) {
                $current_product = new Product(
                    $id_product,
                    true,
                    $this->context->language->id,
                    $this->context->shop->id
                );

                $current_product->price = number_format((float)($current_product->price * 1.10), 2, '.', '');
                $current_product->weight_kg = $current_product->weight * 1000;
            } else {
                return false;
            }

            $customer = new Customer((int)$this->context->customer->id);

            $valid_groups = array(1, 3, 4, 14);

            //Only for "clientes", "CLUB", "Guests" and "Empleados
            if (in_array($customer->id_default_group, $valid_groups)) {

                $pets = new petList();
                $petList = $pets->getPetsListFromCustomer((int)$customer->id);
                if (count($petList['pets']) > 0) {
                    for ($i = 0; $i < count($petList['pets']); $i++) {
                        $petList['pets'][$i]['daily_cost'] = $petList['pets'][$i]['amount'] > 0 ? $current_product->price / ($current_product->weight_kg / $petList['pets'][$i]['amount']) : 0;
                        $petList['pets'][$i]['daily_cost'] = $petList['pets'][$i]['amount'] > 0 ? str_replace(',00', '', number_format($petList['pets'][$i]['daily_cost'], 2, ',', '')) : 0;
                        $petList['pets'][$i]['daily_amount'] = $petList['pets'][$i]['amount'] > 0 ? ($current_product->weight_kg) / $petList['pets'][$i]['amount'] : 0;
                        $petList['pets'][$i]['daily_amount'] = $petList['pets'][$i]['amount'] > 0 ? str_replace(',0', '', number_format($petList['pets'][$i]['daily_amount'], 1, ',', '')) : 0;
                    }

                    //Get related pets to category
                    for ($i = 0; $i < count($petList['pets']); $i++) {
                        if (in_array($category->id, $product_categories) && (int)$petList['pets'][$i]['type'] === 1) { //Dog
                            $petList['pet_list'][] = $petList['pets'][$i];
                        } elseif (in_array($category->id, $product_categories) && (int)$petList['pets'][$i]['type'] === 2) { //Cat
                            $petList['pet_list'][] = $petList['pets'][$i];
                        }
                    }
                }
            }
            $this->context->smarty->assign(array(
                'petList' => (array) $petList['pet_list'],
                'current_product' => $current_product,
                'id_customer' => (int)$this->context->customer->id
            ));

            return $this->context->smarty->fetch($this->local_path . 'views/templates/front/product.tpl');
        }
    }

    public function hookModuleRoutes()
    {
        return array(

            ## profileadv ##

            'profileadv-shoppers' => array(
                'controller' =>    null,
                'rule' =>        '{controller}',
                'keywords' => array(
                    'controller'    =>    array('regexp' => 'shoppers', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'profileadv'
                )
            ),

            'profileadv-shopper' => array(
                'controller' =>    null,
                'rule' =>        '{controller}',
                'keywords' => array(
                    'controller'    =>    array('regexp' => 'shopper', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'profileadv'
                )
            ),

            'profileadv-shopperaccount' => array(
                'controller' =>    null,
                'rule' =>        '{controller}',
                'keywords' => array(
                    'controller'    =>    array('regexp' => 'shopperaccount', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'profileadv'
                )
            ),

        );
    }

    public function hookHeader($params)
    {
        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;
        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : 0;
        $smarty->assign($this->name . 'islogged', $is_logged);

        $this->context->controller->addCSS(($this->_path) . 'views/css/profileadv-custom.css', 'all');
        $this->context->controller->addCSS(($this->_path) . 'views/css/profileadv-petlist.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/flickity.css');
        $this->context->controller->addJS($this->_path . 'views/js/flickity.js');
        $this->context->controller->addJS($this->_path . 'views/js/profileadv-custom-front.js');
    }

    public function hookFooter($params)
    {

        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;

        $obj = new profileAdvanced();

        $this->setSEOUrls();

        $info_customer = $obj->getCustomerInfo();
        $avatar_thumb = $info_customer['avatar_thumb'];
        $exist_avatar = $info_customer['exist_avatar'];

        $smarty->assign($this->name . 'avatar_thumb', $avatar_thumb);
        $smarty->assign($this->name . 'exist_avatar', $exist_avatar);


        $smarty->assign($this->name . 'padv_footer', Configuration::get($this->name . 'padv_footer'));


        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : 0;
        $smarty->assign($this->name . 'islogged', $is_logged);

        return $this->display(__FILE__, 'views/templates/hooks/footer.tpl');
    }


    public function hookCustomerAccount($params)
    {
        $name_template = "my-account.tpl";
        $cache_id = $this->name . '|' . $name_template;

        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;
        $id_customer = (int)$cookie->id_customer;

        $petlist_url = $this->context->link->getModuleLink('profileadv', 'petlist');

        $smarty->assign(array($this->name . 'id_customer' => $id_customer));
        $smarty->assign(array($this->name . 'petlist_url' => $petlist_url));

        if (!$this->isCached($this->_template_name . $name_template, $this->getCacheId($cache_id))) {


            $this->setSEOUrls();
            $smarty->assign($this->name . 'is_ps15', $this->_is15);
        }
        return $this->display(__FILE__, $this->_template_path . $name_template, $this->getCacheId($cache_id));
    }

    public function hookMyAccountBlock($params)
    {
        $name_template = "my-account-block.tpl";
        $cache_id = $this->name . '|' . $name_template;

        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;
        $id_customer = (int)$cookie->id_customer;
        $smarty->assign(array($this->name . 'id_customer' => $id_customer));


        if (!$this->isCached($this->_template_name . $name_template, $this->getCacheId($cache_id))) {

            $this->setSEOUrls();

            $smarty->assign($this->name . 'is_ps15', $this->_is15);
        }
        return $this->display(__FILE__, $this->_template_path . $name_template, $this->getCacheId($cache_id));
    }

    public function hookActionCustomerAccountAdd($params)
    {
        //Validate profile pets after registration
        $obj = new profileAdvanced();
        $customer = new Customer($params['newCustomer']->id);
        if (Validate::isLoadedObject($customer)) {
            $list = $obj->getListPetDataFromCustomerEmailOnMessage($customer->email);
            if (count($list) > 0) {
                foreach ($list as $value) {
                    $obj->validatePetFromReference($value['reference'], $customer->id);
                }
            }
        }

        Tools::redirect($this->context->link->getModuleLink('profileadv', 'petlist'));
    }

    public function hookActionAuthentication($params)
    {
        //iniciar-sesion?back=calculadora
        if (str_contains($_SERVER['HTTP_REFERER'], 'calculadora')) {
            Tools::redirect($this->context->link->getModuleLink('profileadv', 'petlist'));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
    }

    /*public function getContent()
    {
        $_html = '';

        $cookie = $this->context->cookie;

        $currentIndex = $this->context->currentindex;

        $errors = array();


        $profileadv_settingsset = Tools::getValue("profileadv_settingsset");
        if (Tools::strlen($profileadv_settingsset) > 0) {
            ob_start();
            $number_tab = 3;
            include(dirname(__FILE__) . '/views/templates/hooks/js.phtml');
            $_html .= ob_get_clean();
        }

        if (Tools::isSubmit('submit_profileadv')) {
            if (!ctype_digit(Tools::getValue('perpage_shoppers')) || Tools::getValue('perpage_shoppers') == NULL) {
                $errors[] = $this->l('Shoppers per page in the list view:') . ' ' . $this->l('must be digit!');
            } else {
                Configuration::updateValue($this->name . 'perpage_shoppers', Tools::getValue('perpage_shoppers'));
            }

            Configuration::updateValue($this->name . 'padv_home', Tools::getValue('padv_home'));
            Configuration::updateValue($this->name . 'padv_left', Tools::getValue('padv_left'));
            Configuration::updateValue($this->name . 'padv_right', Tools::getValue('padv_right'));
            Configuration::updateValue($this->name . 'padv_footer', Tools::getValue('padv_footer'));

            if (!ctype_digit(Tools::getValue('shoppers_blc')) || Tools::getValue('shoppers_blc') == NULL) {
                $errors[] = $this->l('The number of shoppers in the "Block Shoppers":') . ' ' . $this->l('must be digit!');
            } else {
                Configuration::updateValue($this->name . 'shoppers_blc', Tools::getValue('shoppers_blc'));
            }

            if (sizeof($errors) == 0) {



                $url = $currentIndex . '&conf=6&tab=AdminModules&profileadv_settingsset=1&configure=' . $this->name . '&token=' . Tools::getAdminToken('AdminModules' . (int)(Tab::getIdFromClassName('AdminModules')) . (int)($cookie->id_employee)) . '';
                Tools::redirectAdmin($url);
            } else {
                ob_start();
                $number_tab = 3;
                include(dirname(__FILE__) . '/views/templates/hooks/js.phtml');
                $_html .= ob_get_clean();
            }
        }

        $this->addBackOfficeMedia();

        $_html .= $this->_displayForm16(array('errors' => $errors));


        return $_html;
    }*/

    public function hookAddWebserviceResources(): array

    {
        return [
            'pets' => [
                'description' => 'Guau&Cat Pet list',
                'class' => 'Pets',
            ]
        ];
    }

    public function renderTplListShoppers()
    {
        return Module::display(dirname(__FILE__) . '/profileadv.php', 'views/templates/hooks/list-shoppers.tpl');
    }

    public function setSEOUrls()
    {
        $smarty = $this->context->smarty;
        include_once(dirname(__FILE__) . '/classes/profileadvanced.class.php');
        $obj_profileadv = new profileAdvanced();

        $info_customers = $obj_profileadv->getShoppersBlock(
            array(
                'start' => 0,
                'step' => (int)Configuration::get($this->name . 'shoppers_blc')
            )
        );

        $smarty->assign(array(
            $this->name . 'customers_block' => $info_customers['customers']
        ));

        $data_url = $obj_profileadv->getSEOURLs();
        $shoppers_url = $data_url['shoppers_url'];
        $shopper_url = $data_url['shopper_url'];
        $shopperaccount_url = $data_url['shopperaccount_url'];
        $ajax_profile_url = $data_url['ajax_profile_url'];

        $smarty->assign(
            array(
                $this->name . 'shoppers_url' => $shoppers_url,
                $this->name . 'shopper_url' => $shopper_url,
                $this->name . 'shopperaccount_url' => $shopperaccount_url,
                $this->name . 'ajax_profile_url' => $ajax_profile_url,
            )
        );

        $smarty->assign($this->name . 'is16', $this->_is16);

        $smarty->assign($this->name . 'pic', $this->path_img_cloud);

        $smarty->assign($this->name . 'is_urlrewrite', Configuration::get($this->name . 'is_urlrewrite'));
    }

    public function translateItems()
    {
        return array(
            'meta_title_shoppers' => $this->l('All Shoppers'),
            'meta_description_shoppers' => $this->l('All Shoppers'),
            'meta_keywords_shoppers' => $this->l('All Shoppers'),
            'profile' => $this->l('profile'),
            'meta_title_myaccount' => $this->l('User Profile Advanced'),
            'meta_description_myaccount' => $this->l('User Profile Advanced'),
            'meta_keywords_myaccount' => $this->l('User Profile Advanced'),

        );
    }

}
