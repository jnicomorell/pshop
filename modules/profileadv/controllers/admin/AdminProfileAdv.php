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

class AdminprofileadvController extends ModuleAdminController
{
    private $pet_reference;
    private $customer;
    private $translationList;
    protected $action;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->action = isset($_POST['action']) ? $_POST['action'] : 'view';
        parent::__construct();
    }

    public function initContent()
    {
        $this->pet_reference = isset($_GET['reference']) ? $_GET['reference'] : false;
        $this->customer = isset($_GET['customer']) ? $_GET['customer'] : 1;
        require_once _PS_MODULE_DIR_ . 'profileadv/classes/TranslationManager.php';
        $iso = $this->context->language ? $this->context->language->iso_code : 'es';
        $this->translationList = ProfileadvTranslationManager::getDataTranslations($iso);

        parent::initContent();
    }

    public function renderList()
    {

        $name_module = 'profileadv';

        $cookie = $this->context->cookie;

        require_once(_PS_MODULE_DIR_ . '/profileadv/classes/profileadvanced.class.php');

        $pet = new profileAdvanced();
        $pet_data = $pet->getPetDataFromReference($this->pet_reference, (int) $this->customer);

        if (!$pet_data) {
            $pet_data = [
                'name' => '',
                'avatar_thumb' => '',
                'amount' => 0,
                'is_amount_blocked' => 0,
                'birth' => '',
                'type' => 1,
                'genre' => 1,
                'desired_weight' => 0,
                'weight' => 0,
                'esterilized' => 0,
                'breed' => 0,
                'activity' => 0,
                'feeding' => 0,
                'pathology' => [],
                'allergies' => [],
                'message' => '',
                'reference' => '',
                'active' => 0,
                'is_validated' => 0
            ];
        } else {
            /**Convert to array */
            $pet_data['pathology'] = isset($pet_data['pathology']) ? json_decode($pet_data['pathology']) : array();
            $pet_data['allergies'] = isset($pet_data['allergies']) ? json_decode($pet_data['allergies']) : array();
        }

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

        $customer = new Customer((int) $this->customer);
        $addressData = $pet->getLastCustomerIdAddress($customer->id);
        $addressId = (!empty($addressData) && isset($addressData[0]['id_address'])) ? (int) $addressData[0]['id_address'] : 0;
        $address = new Address($addressId);

        $customerData = array(
            "id" => Validate::isLoadedObject($customer) ? $customer->id : 1,
            "name" => $customer->firstname,
            "phone" => $address->phone != 0 ? $address->phone : 0,
            "id_risk" => $customer->id_risk
        );

        $employee = new Employee(intval($this->context->cookie->id_employee));

        $employeeData = array(
            "id" => $employee->id,
            "name" => $employee->firstname,
        );

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
        $this->context->smarty->assign($name_module . 'customerData', $customerData);
        $this->context->smarty->assign($name_module . 'employeeData', $employeeData);

        $this->context->smarty->assign(array(
            'pet_data' => (array) $pet_data
        ));

        return $this->module->display(_PS_MODULE_DIR_ . 'profileadv', 'views/templates/admin/back_edit.tpl');
    }

    public function postProcess()
    {
        $name_module = 'profileadv';

        if ($this->action === 'editpet') {
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

            if (isset($_POST['pet-pathology'])) {
                foreach ($_POST['pet-pathology'] as $value) {
                    if ($value !== "1") {
                        $petPathologies[] = (int)$value;
                    }
                }
            }

            //Get all allergies
            $petAllergies = array();

            if (isset($_POST['pet-allergies'])) {
                foreach ($_POST['pet-allergies'] as $value) {
                    if ($value !== "1") {
                        $petAllergies[] = (int) $value;
                    }
                }
            }

            require_once(_PS_MODULE_DIR_ . '/profileadv/controllers/front/ajaxprofileadv.php');
            include_once(_PS_MODULE_DIR_ . $name_module . "/classes/profileadvanced.class.php");
            $obj = new profileAdvanced();

            //Sanitize data
            $newPetData = [
                "pet-name" => $_POST['pet-name'],
                "pet-reference" => $_POST['pet-reference'],
                "pet-birth" => $_POST['pet-birth'],
                "pet-type" => (int)$_POST['pet-type'],
                "pet-genre" => (int)$_POST['pet-genre'],
                "pet-physical-condition" => $this->calculatePhysicalCondition((float)$_POST['pet-weight'], (float)$_POST['pet-desired-weight']),
                "pet-weight" => (float)$_POST['pet-weight'],
                "pet-desired-weight" => (float)$_POST['pet-desired-weight'],
                "pet-esterilized" => (int)$_POST['pet-esterilized'],
                "pet-breed" => (int)$petBreedData,
                "pet-activity" => (int)$_POST['pet-activity'],
                "pet-feeding" => (int)$_POST['pet-feeding'],
                "pet-pathology" => $petPathologies,
                "pet-allergies" => $petAllergies,
                "pet-customer" => (int)$_POST['pet-customer'],
                "pet-amount" => (int)$_POST['pet-amount'],
                "pet-amount-blocked" => (int)isset($_POST['pet-amount-blocked']) ? 1 : 0,
                "pet-message" => $_POST['pet-message']
            ];

            //Calculate amount if is not blocked
            if ($newPetData['pet-amount-blocked'] === 0) {

                //Calculate daily amount 
                include_once(_PS_MODULE_DIR_ . $name_module . "/calculateamount.php");
                $calculator = new calculateAmount();
                $newPetData = $calculator->calculateDailyEatAmount($newPetData);
            }

            $updated = "";
            $updated = $obj->updatePetDataFromBO($newPetData);

            $redirect = $_SERVER['HTTP_REFERER'];
            if (!empty($updated)) {
                $this->context->smarty->assign($name_module . 'updated', true);
            }
            header('Location: ' . $redirect);
        }
    }

    private function calculatePhysicalCondition($weight, $desiredWeight)
    {
        //10% of margin
        if (((100 * $weight) / $desiredWeight) - 100 < -10) {
            //Delgado
            return 1;
        } elseif (((100 * $weight) / $desiredWeight) - 100 > 10) {
            //Gordito
            return 3;
        } else {
            //Normal
            return 2;
        }
    }
}
