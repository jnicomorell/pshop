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

function init_tabs(id){
    $('document').ready( function() {


        if(id == 3){
            $('#navtabs16 a[href="#modulesettings"]').tab('show');
        }


    });
}



function tabs_custom(id){


    if(id == 6){
        $('#navtabs16 a[href="#info"]').tab('show');
    }





}