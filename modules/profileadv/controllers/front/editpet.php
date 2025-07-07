<?php

require_once(_PS_MODULE_DIR_ . 'profileadv/controllers/front/ProfileadvFrontController.php');

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
class ProfileadvEditpetModuleFrontController extends ProfileadvFrontController
{
    public $auth = true;
    public $guestAllowed = false;
    public $ssl = true;

    private $petToUpdate;

    public $translationList;

    public function init()
    {
        $this->petToUpdate = pSQL(isset($_GET['pet'])) ? pSQL($_GET['pet']) : false;
        $this->addCustomInputFileAssets = true;
        $this->addProfileadvCustomCss = true;
        $this->addProfileadvJs = true;
        $this->loadTranslations();
        parent::init();
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $name_module = 'profileadv';

        $cookie = Context::getContext()->cookie;
        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : 0;
        if (!$is_logged)
            Tools::redirect('authentication.php');

        include_once(_PS_MODULE_DIR_ . $name_module . '/classes/profileadvanced.class.php');
        $obj = new profileAdvanced();

        $getPetData = $obj->getPetDataFromReference(pSQL($this->petToUpdate), $is_logged);

        //Convert all pathologies index as single array
        $pathologiesArray = array();
        $pathologies = json_decode($getPetData['pathology']);
        for ($i = 0; $i < count($pathologies); $i++) {
            array_push($pathologiesArray, $pathologies[$i]);
        }

        //Convert all allergies index as single array
        $allergiesArray = array();
        $allergies = json_decode($getPetData['allergies']);
        for ($i = 0; $i < count($allergies); $i++) {
            array_push($allergiesArray, $allergies[$i]);
        }


        $petData = array(
            "img" => $getPetData['avatar_thumb'],
            "type" => (int) $getPetData['type'],
            "reference" => $getPetData['reference'],
            "name" => $getPetData['name'],
            "genre" => (int)$getPetData['genre'],
            "birth" => $getPetData['birth'],
            "age" => $this->calculateAgeinMonths($getPetData['birth']),
            "breed" => (int) $getPetData['breed'],
            "esterilized" => (int)$getPetData['esterilized'],
            "weight" => (float)$getPetData['weight'],
            "desiredweight" => (float)$getPetData['desired_weight'],
            "feeding" => (int)$getPetData['feeding'],
            "activity" => (int)$getPetData['activity'],
            "physical_condition" => (int)$getPetData['physical_condition'],
            "pathologies" => $pathologiesArray,
            "allergies" => $allergiesArray,
            "amount" => $getPetData['amount'],
            "message" => $getPetData['message'],
            "text" => html_entity_decode($getPetData['comment'])
        );

        $this->context->smarty->assign($name_module . 'editpetdata', $petData);

        $currentDate = date('Y-m-d');
        $maxOldDate = date("Y-m-d", strtotime("-25 year"));

        $this->assignTranslations($this->translationList, $name_module);

        $this->context->smarty->assign($name_module . 'currentdate', $currentDate);
        $this->context->smarty->assign($name_module . 'maxolddate', $maxOldDate);

        include_once(_PS_MODULE_DIR_ . $name_module . '/profileadv.php');
        $obj_profileadv = new profileadv();
        $_data_translate = $obj_profileadv->translateItems();

        $obj_profileadv->setSEOUrls();

        $data_urls = $obj->getSEOURLs();
        $my_account = $data_urls['pet_list'];

        $is_chrome = 1;

        if (
            preg_match("/chrome/i", $_SERVER['HTTP_USER_AGENT']) ||
            preg_match("/Firefox\/10\.0\.1/i", $_SERVER['HTTP_USER_AGENT'])
        )
            $is_chrome = 1;


        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->context->smarty->tpl_vars['page']->value['meta']['title'] = $_data_translate['meta_title_myaccount'];
            $this->context->smarty->tpl_vars['page']->value['meta']['description'] = $_data_translate['meta_description_myaccount'];
            $this->context->smarty->tpl_vars['page']->value['meta']['keywords'] = $_data_translate['meta_keywords_myaccount'];
        }

        $this->context->smarty->assign('meta_title', $_data_translate['meta_title_myaccount']);
        $this->context->smarty->assign('meta_description', $_data_translate['meta_description_myaccount']);
        $this->context->smarty->assign('meta_keywords', $_data_translate['meta_keywords_myaccount']);

        $this->context->smarty->assign(array(
            $name_module . 'is_chrome' => $is_chrome,
            $name_module . 'my_account' => $my_account,
        ));

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:' . $name_module . '/views/templates/front/editpet.tpl');
        }
    }

}
