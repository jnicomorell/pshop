<?php

use PrestaShop\PrestaShop\Adapter\Tools;

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

class ProfileadvshopperaccountModuleFrontController extends ModuleFrontController
{

    public function init()
    {

        parent::init();
    }

    public function setMedia()
    {

        $module_name = "profileadv";

        //$this->context->controller->addJs(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/jquery.form.js');

        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/css/custom-input-file.css');
        $this->context->controller->addJs(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/custom-input-file.js');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/profileadv-custom.js');

        parent::setMedia();
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cookie = Context::getContext()->cookie;
        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : 0;
        if (!$is_logged)
            Tools::redirect('authentication.php');


        $name_module = 'profileadv';

        include_once(_PS_MODULE_DIR_ . $name_module . '/classes/profileadvanced.class.php');
        $obj = new profileAdvanced();

        include_once(_PS_MODULE_DIR_ . $name_module . '/profileadv.php');
        $obj_profileadv = new profileadv();
        $_data_translate = $obj_profileadv->translateItems();

        $obj_profileadv->setSEOUrls();


        $data_urls = $obj->getSEOURLs();
        $my_account = $data_urls['my_account'];

        //$pet_reference = Tools::getValue('pet');
        $pet_reference = pSQL($_REQUEST['pet']);

        if (!$pet_reference)
            Tools::redirect('index.php?controller=my-account'); //Redirect to my account

        $info_customer = $obj->getCustomerInfo($pet_reference);
        $avatar_thumb = $info_customer['avatar_thumb'];
        $exist_avatar = $info_customer['exist_avatar'];
        $is_show = $info_customer['is_show'];
        $is_chrome = 1;
        $pet_data = $info_customer['pet_data'];

        if (
            preg_match("/chrome/i", $_SERVER['HTTP_USER_AGENT']) ||
            preg_match("/Firefox\/10\.0\.1/i", $_SERVER['HTTP_USER_AGENT'])
        )
            $is_chrome = 1;


        $this->context->smarty->assign($name_module . 'is16', 1);


        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->context->smarty->tpl_vars['page']->value['meta']['title'] = $_data_translate['meta_title_myaccount'];
            $this->context->smarty->tpl_vars['page']->value['meta']['description'] = $_data_translate['meta_description_myaccount'];
            $this->context->smarty->tpl_vars['page']->value['meta']['keywords'] = $_data_translate['meta_keywords_myaccount'];
        }



        $this->context->smarty->assign('meta_title', $_data_translate['meta_title_myaccount']);
        $this->context->smarty->assign('meta_description', $_data_translate['meta_description_myaccount']);
        $this->context->smarty->assign('meta_keywords', $_data_translate['meta_keywords_myaccount']);

        $this->context->smarty->assign(array(
            $name_module . 'avatar_thumb' => $avatar_thumb,
            $name_module . 'exist_avatar' => $exist_avatar,
            $name_module . 'is_show' => $is_show,
            $name_module . 'is_chrome' => $is_chrome,
            $name_module . 'my_account' => $my_account,
            $name_module . 'is_demo' => $obj_profileadv->is_demo,
            //$name_module . 'status_error' => Tools::getValue('error'),
            //$name_module . 'message_error' => Tools::getValue('message'),
            $name_module . 'pet_data' => $pet_data
        ));


        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:' . $name_module . '/views/templates/front/profileadv17.tpl');
        } else {
            $this->setTemplate('profileadv.tpl');
        }
    }
}
