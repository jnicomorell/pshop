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

class ProfileadvshoppersModuleFrontController extends ProfileadvFrontController
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

        $_data_translate = $obj_profileadv->translateItems();

        $obj_profileadv->setSEOUrls();


        if(version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->context->smarty->tpl_vars['page']->value['meta']['title'] = $_data_translate['meta_title_shoppers'];
            $this->context->smarty->tpl_vars['page']->value['meta']['description'] = $_data_translate['meta_description_shoppers'];
            $this->context->smarty->tpl_vars['page']->value['meta']['keywords'] = $_data_translate['meta_keywords_shoppers'];
        }


        $this->context->smarty->assign('meta_title' , $_data_translate['meta_title_shoppers']);
        $this->context->smarty->assign('meta_description' , $_data_translate['meta_description_shoppers']);
        $this->context->smarty->assign('meta_keywords' , $_data_translate['meta_keywords_shoppers']);



        $this->context->smarty->assign($name_module.'is16' , 1);

        include_once(_PS_MODULE_DIR_.$name_module.'/classes/profileadvanced.class.php');
        $obj = new profileAdvanced();

        $start = (int)Tools::getValue('start');
        $info_customers = $obj->getShoppersList(array('start' => $start));

        $this->context->smarty->assign(array(
            $name_module.'customers' => $info_customers['customers'],
            $name_module.'data_count_customers' => $info_customers['data_count_customers'],
            $name_module.'paging' => $info_customers['paging']
        ));


        if(version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:' . $name_module . '/views/templates/front/shoppers17.tpl');
        }else {
            $this->setTemplate('shoppers.tpl');
        }







    }
}