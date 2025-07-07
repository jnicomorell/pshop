<?php

require_once(_PS_MODULE_DIR_ . 'profileadv/controllers/front/ProfileadvFrontController.php');
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

class ProfileadvshopperModuleFrontController extends ProfileadvFrontController
{
    public function init()
    {

        parent::init();
    }



    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();


        $name_module = 'profileadv';

        include_once(_PS_MODULE_DIR_.$name_module.'/profileadv.php');
        $obj_profileadv = new profileadv();

        include_once(_PS_MODULE_DIR_.$name_module.'/classes/profileadvanced.class.php');
        $obj = new profileAdvanced();

        $shopper_id = (int)Tools::getValue('id');


        $data_urls = $obj->getSEOURLs();
        $shoppers_url = $data_urls['shoppers_url'];

        if (!$shopper_id) {
            Tools::redirect($shoppers_url);
        }

        $info = $obj->getShopperInfo(array('shopper_id' => $shopper_id));


        if (sizeof($info['customer']) == 0) {
            Tools::redirect($shoppers_url);
        }

        $this->context->smarty->assign($name_module.'is16', 1);

        $this->context->smarty->assign(array(
            $name_module.'customer' => $info['customer']
        ));

        $_data_translate = $obj_profileadv->translateItems();


        $title = @$info['customer'][0]['firstname']." " .@$info['customer'][0]['lastname']. " ".$_data_translate['profile'];



        $obj_profileadv->setSEOUrls();

        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || (bool)Configuration::get('PS_SSL_ENABLED')) {
            $custom_ssl_var = 1;
        }
        $this->context->smarty->assign($name_module.'is_ssl', $custom_ssl_var);


        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->context->smarty->tpl_vars['page']->value['meta']['title'] = $title;
            $this->context->smarty->tpl_vars['page']->value['meta']['description'] = $title;
            $this->context->smarty->tpl_vars['page']->value['meta']['keywords'] = $title;
        }



        $this->context->smarty->assign('meta_title', $title);
        $this->context->smarty->assign('meta_description', $title);
        $this->context->smarty->assign('meta_keywords', $title);


        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:' . $name_module . '/views/templates/front/shopper17.tpl');
        } else {
            $this->setTemplate('shopper.tpl');
        }




    }
}
