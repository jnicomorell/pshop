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
class ProfileadvEditpetModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $guestAllowed = false;
    public $ssl = true;

    private $petToUpdate;

    public $translationList;

    public function init()
    {
        $this->petToUpdate = pSQL(isset($_GET['pet'])) ? pSQL($_GET['pet']) : false;

        $this->translationList = require_once('./modules/profileadv/translations/translations.php');
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

        $iso_code =  isset($cookie->id_lang) ? Language::getIsoById((int)$cookie->id_lang) : 'es';

        $dogBreedList = $this->translationList['breed']['dog'][$iso_code];
        $catBreedList = $this->translationList['breed']['cat'][$iso_code];
        $genreList = $this->translationList['genre'][$iso_code];
        $typeList = $this->translationList['type'][$iso_code];
        $esterilizedList = $this->translationList['esterilized'][$iso_code];
        $activityList = $this->translationList['activity'][$iso_code];
        $physicalConditionList = $this->translationList['physical-condition'][$iso_code];
        $feedingList = $this->translationList['feeding'][$iso_code];
        $pathologiesList = $this->translationList['pathologies'][$iso_code];
        $allergiesList = $this->translationList['allergies'][$iso_code];
        $currentDate = date('Y-m-d');
        $maxOldDate = date("Y-m-d", strtotime("-25 year"));

        $this->context->smarty->assign($name_module . 'dogbreedlist', $dogBreedList);
        $this->context->smarty->assign($name_module . 'catbreedlist', $catBreedList);
        $this->context->smarty->assign($name_module . 'typelist', $typeList);
        $this->context->smarty->assign($name_module . 'genrelist', $genreList);
        $this->context->smarty->assign($name_module . 'esterilizedlist', $esterilizedList);
        $this->context->smarty->assign($name_module . 'activitylist', $activityList);
        $this->context->smarty->assign($name_module . 'physicalconditionlist', $physicalConditionList);
        $this->context->smarty->assign($name_module . 'feedinglist', $feedingList);
        $this->context->smarty->assign($name_module . 'pathologieslist', $pathologiesList);
        $this->context->smarty->assign($name_module . 'allergieslist', $allergiesList);
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

    public function setMedia()
    {

        $module_name = "profileadv";

        //$this->context->controller->addJs(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/jquery.form.js');

        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/css/custom-input-file.css');
        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/css/profileadv-custom.css');
        $this->context->controller->addJs(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/custom-input-file.js');
        $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/profileadv-custom.js');

        parent::setMedia();
    }

    private function calculateAgeInMonths(string $birth)
    {
        $birth = new DateTime(date('Y/m/d', strtotime($birth)));
        $now =  new DateTime(date('Y/m/d', time()));

        //Calculate age in months
        $age = date_diff($now, $birth);
        $age = ($age->y * 12) + $age->m;
        return $age;
    }
}
