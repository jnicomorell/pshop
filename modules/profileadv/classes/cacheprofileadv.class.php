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

class cacheprofileadv extends Module{



    public function clearSmartyCacheModule($data = null){

        $name_module = "profileadv";


        if(version_compare(_PS_VERSION_, '1.6', '>')) {

            $skip_files = array(".","..","index.php",".DS_Store");

            // clear cache for hooks //
            $template_path_hooks = "views".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."hooks".DIRECTORY_SEPARATOR;
            $dir_hooks = _PS_MODULE_DIR_.$name_module.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."hooks";

            $dh  = opendir($dir_hooks);
            while (false !== ($filename = readdir($dh))) {

                if(in_array($filename,$skip_files))
                    continue;


                $template_name = $template_path_hooks.$filename;


                $cache_id = $name_module.'|' .$filename;
                $this->_clearCache($template_name, $cache_id);



            }
            // clear cache for hooks //


            // clear cache on the home page only for ps 17 //
            if(version_compare(_PS_VERSION_, '1.7', '>')) {
                $template_name = "module:ps_featuredproducts/views/templates/hook/ps_featuredproducts.tpl";
                $cache_id = "ps_featuredproducts";
                $this->_clearCache($template_name, $cache_id);
            }
            // clear cache on the home page only for ps 17 //


        }


    }




}