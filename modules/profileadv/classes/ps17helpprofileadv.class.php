<?php
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

class ps17helpprofileadv {
    private $_name = 'profileadv';
	
	public function setMissedVariables(){
        $smarty = Context::getContext()->smarty;



        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || (bool)Configuration::get('PS_SSL_ENABLED'))
            $custom_ssl_var = 1;

        if ($custom_ssl_var == 1)
            $base_dir_ssl = _PS_BASE_URL_SSL_.__PS_BASE_URI__;
        else
            $base_dir_ssl = _PS_BASE_URL_.__PS_BASE_URI__;

        $smarty->assign('base_dir_ssl' , $base_dir_ssl);


        if(version_compare(_PS_VERSION_, '1.7', '>')) {
            $smarty->assign($this->_name.'is17' , 1);
        }

    }
}