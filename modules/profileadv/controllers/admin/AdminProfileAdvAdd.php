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

class AdminProfileAdvAddController extends ModuleAdminController
{
    private $translationList;
    protected $action;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->action = isset($_POST['action']) ? $_POST['action'] : 'create';
        parent::__construct();
    }

    public function initContent()
    {
        require_once _PS_MODULE_DIR_ . 'profileadv/classes/TranslationManager.php';
        $iso = $this->context->language ? $this->context->language->iso_code : 'es';
        $this->translationList = ProfileadvTranslationManager::getDataTranslations($iso);

        parent::initContent();
    }

    public function renderList()
    {

        $name_module = 'profileadv';

        $cookie = $this->context->cookie;

        $dogBreedList = $this->translationList['breed']['dog'];
        $catBreedList = $this->translationList['breed']['cat'];
        $genreList = $this->translationList['genre'];
        $typeList = $this->translationList['type'];
        $esterilizedList = $this->translationList['esterilized'];
        $activityList = $this->translationList['activity'];
        $physicalConditionList = $this->translationList['physical-condition'];
        $feedingList = $this->translationList['feeding'];
        $pathologiesList = $this->translationList['pathologies'];
        $allergiesList = $this->translationList['allergies'];
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

        return $this->module->display(_PS_MODULE_DIR_ . 'profileadv', 'views/templates/admin/back_add.tpl');
    }

    public function postProcess()
    {
        $name_module = 'profileadv';

        if ($this->action === 'addpet') {
            $petBreedData = "";

            switch ($_POST['pet-type']) {
                case '1':
                    $petBreedData = $_POST['pet-breed-dog'];
                    break;
                case '2':
                    $petBreedData = $_POST['pet-breed-cat'];
                    break;
            }

            //Get all pathologies
            $petPathologies = array();

            if (isset($_POST['pet-pathology']) && count($_POST['pet-pathology']) > 0) {
                foreach ($_POST['pet-pathology'] as $value) {
                    if ($value !== "1") {
                        $petPathologies[] = (int)$value;
                    }
                }
            }

            //Get all allergies
            $petAllergies = array();

            if (isset($_POST['pet-allergies']) && count($_POST['pet-allergies']) > 0) {
                foreach ($_POST['pet-allergies'] as $value) {
                    if ($value !== "1") {
                        $petAllergies[] = (int) $value;
                    }
                }
            }

            //Sanitize data
            $newPetData = [
                "pet-name" => $_POST['pet-name'],
                "pet-reference" => md5((int)$_POST['pet-customer'] . time()),
                "pet-birth" => $_POST['pet-birth'],
                "pet-type" => (int)$_POST['pet-type'],
                "pet-genre" => (int)$_POST['pet-genre'],
                "pet-physical-condition" => (int)$_POST['pet-physical-condition'],
                "pet-weight" => (float)$_POST['pet-weight'],
                "pet-desired-weight" => (float)$_POST['pet-desired-weight'],
                "pet-esterilized" => (int)$_POST['pet-esterilized'],
                "pet-breed" => $petBreedData,
                "pet-activity" => (int)$_POST['pet-activity'],
                "pet-feeding" => (int)$_POST['pet-feeding'],
                "pet-pathology" => $petPathologies,
                "pet-allergies" => $petAllergies,
                "pet-customer" => (int)$_POST['pet-customer'],
                "pet-amount" => (int)$_POST['pet-amount'],
                "pet-message" => $_POST['pet-message']
            ];

            //Calculate amount if is not populated
            if (!isset($newPetData['pet-amount']) || (int)$newPetData['pet-amount'] === 0) {
                //Calculate daily amount 
                require_once(_PS_MODULE_DIR_ . $name_module . "/calculateamount.php");
                $calculator = new calculateAmount();
                $newPetData = $calculator->calculateDailyEatAmount($newPetData);
            }

            include_once(_PS_MODULE_DIR_ . $name_module . "/classes/profileadvanced.class.php");
            $obj = new profileAdvanced();
            $obj->saveImage(array('newpetdata' => $newPetData, 'action' => $this->action));

            $_href = Context::getContext()->link->getAdminLink('AdminCustomers', true, ['id_customer' => (int)$_POST['pet-customer'], 'viewcustomer' => '']);

            header('Location: ' . $_href);
        }
    }
}
