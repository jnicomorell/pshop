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

class ProfileadvAjaxprofileadvModuleFrontController extends ModuleFrontController
{
    private $translationList;
    private $newPetData = [];

    public const DEFAULT_RECOMMENDED_PRODUCT_DOG_250 = 3886;
    public const DEFAULT_RECOMMENDED_PRODUCT_DOG_500 = 3901;
    public const DEFAULT_RECOMMENDED_PRODUCT_DOG_1000 = 3936;
    public const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250 = 3899;
    public const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_500 = 3924;
    public const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_1000 = 3949;
    public const DEFAULT_RECOMMENDED_PRODUCT_CAT = 129;
    public const DEFAULT_RECOMMENDED_BARF_PRODUCT_CAT = 130;

    public function postProcess()
    {
        require_once _PS_MODULE_DIR_.'profileadv/classes/TranslationManager.php';
        $iso = $this->context->language ? $this->context->language->iso_code : 'es';
        $this->translationList = ProfileadvTranslationManager::getDataTranslations($iso);

        $action = Tools::getValue('action');

        if ($action != 'addpet' && $action != 'addfirstpet' && $action != 'editpet') {
            header("Access-Control-Allow-Origin: *");
            $HTTP_X_REQUESTED_WITH = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
            if ($HTTP_X_REQUESTED_WITH != 'XMLHttpRequest') {
                exit;
            }
        }

        $name_module = 'profileadv';
        include_once(_PS_MODULE_DIR_ . $name_module . '/profileadv.php');
        $obj_profileadv = new profileadv();

        $token = Tools::getValue('token');
        $token_orig = $obj_profileadv->getokencron();
        if ($token_orig != $token) {
            die('Invalid token.');
        }

        ob_start();
        $status = 'success';
        $message = '';
        $_html_page_nav = '';
        $_html = '';
        $avatar_thumb = 0;
        $is_show = 0;

        $cookie = Context::getContext()->cookie;
        $customer = isset($cookie->id_customer) ? $cookie->id_customer : 0;

        if (!$customer && ($action != 'addpet' && $action != 'addfirstpet')) {
            Tools::redirect('authentication.php');
        }

        //Get correct pet breed input data
        $petBreedData = "";

        switch (Tools::getValue('pet-type')) {
            case '1':
                $petBreedData = (int)Tools::getValue('pet-breed-dog');
                break;
            case '2':
                $petBreedData = (int)Tools::getValue('pet-breed-cat');
                break;
        }

        //Get all pathologies
        $petPathologies = array();

        if (Tools::getValue('pet-pathology')) {
            foreach (Tools::getValue('pet-pathology') as $value) {
                if ($value !== "1") {
                    $petPathologies[] = (int)$value;
                }
            }
        }

        //Get all allergies
        $petAllergies = array();

        if (Tools::getValue('pet-allergies')) {
            foreach (Tools::getValue('pet-allergies') as $value) {
                if ($value !== "1") {
                    $petAllergies[] = (int) $value;
                }
            }
        }

        $petReference = Tools::getValue('pet-reference');

        $isAmountBlocked = false;

        $obj = new profileAdvanced();

        if ($action === 'editpet') {
            $isAmountBlocked = $this->isAmountBlocked(Tools::getValue('pet-reference'), (int)$customer) === 1 ? 1 : 0;
        }

        //Sanitize data
        $this->newPetData = [
            "pet-name" => Tools::getValue('pet-name'),
            "pet-reference" => $petReference ? $petReference : md5($customer . time()), //If is set pet-reference -> Customer is modifying this pet data, if not --> there is a new pet
            "pet-birth" => Tools::getValue('pet-birth'),
            "pet-type" => (int)Tools::getValue('pet-type'),
            "pet-genre" => (int)Tools::getValue('pet-genre'),
            "pet-physical-condition" => $this->calculatePhysicalCondition((float)Tools::getValue('pet-weight'), (float)Tools::getValue('pet-desired-weight')),
            "pet-weight" => (float)Tools::getValue('pet-weight'),
            "pet-desired-weight" => (float)Tools::getValue('pet-desired-weight'),
            "pet-esterilized" => (int)Tools::getValue('pet-esterilized'),
            "pet-breed" => $petBreedData,
            "pet-activity" => (int)Tools::getValue('pet-activity'),
            "pet-feeding" => (int)Tools::getValue('pet-feeding'),
            "pet-pathology" => $petPathologies,
            "pet-allergies" => $petAllergies,
            "pet-amount" => (int)Tools::getValue('pet-amount'),
            "pet-amount-blocked" => (int)$isAmountBlocked,
            "pet-message" => Tools::getValue('pet-message') !== null && !empty(Tools::getValue('pet-message')) ? Tools::getValue('pet-message') : null,
            "pet-customer-email" => Tools::getValue('pet-customer-email') !== null && !empty(Tools::getValue('pet-customer-email')) ? pSQL(Tools::getValue('pet-customer-email')) : false,
            "action" => $action,
            "is_guest" => false
        ];

        //Validate data before process
        if (!$this->validateFormData()) {
            header("Location: /");
            die();
        };

        //Save customer email as comment
        if ($action === 'addfirstpet') {
            //Check if is customer
            $this->newPetData['pet-customer'] = Customer::customerExists($this->newPetData['pet-customer-email'], true) ? Customer::customerExists($this->newPetData['pet-customer-email'], true) : 1;

            $this->newPetData["pet-message"] = $this->newPetData['pet-customer'] === 1 ? pSQL(Tools::getValue('pet-customer-email')) : null;
            $this->newPetData['is_guest'] = true;
        }

        //Calculate daily amount
        if ($this->newPetData['pet-amount-blocked'] === 0) {
            include_once(_PS_MODULE_DIR_ . $name_module . "/calculateamount.php");
            $calculator = new calculateAmount();
            $this->newPetData = $calculator->calculateDailyEatAmount($this->newPetData);
        }

        $info_upload = "";

        switch ($action) {
            case 'addpet' || 'editpet' || 'addpetbo' || 'addfirstpet':

                $this->newPetData['is_guest'] ? $this->newPetData['validate_token'] = md5(uniqid(mt_rand(), true)) : false;

                $info_upload = $obj->saveImage(array('newpetdata' => $this->newPetData, 'action' => $action, 'validate_token' => $this->newPetData['validate_token']));
                $error = $info_upload['error'];

                if ($error > 0) {

                    $status = 'error';
                    $message = $info_upload['error_text'];

                    $shopperaccount_url = $this->context->link->getModuleLink('profileadv', 'petlist', array('error' => $info_upload['error']));
                    Tools::redirect($shopperaccount_url);
                } else {
                    $info_customer = $obj->getCustomerInfo();
                    $avatar_thumb = $info_customer['avatar_thumb'];
                    $is_show = $info_customer['is_show'];
                }

                if ($this->newPetData['is_guest'] === true) {
                    $email_customer = $this->newPetData['pet-customer-email'];
                } elseif ($cookie->id_customer) {
                    $custom = new Customer($cookie->id_customer);
                    $email_customer = $custom->email;
                } else {
                    $custom = new Customer($this->newPetData['pet-customer']);
                    $email_customer = $custom->email;
                }

                $recommended_product = $this->getRecommendedProduct($this->newPetData);
                //Send email to customer
                $this->sendCustomerEmail($obj, $email_customer, $recommended_product);

                break;
        }

        //Send email to AT
        if (isset($this->newPetData['reason']) && $action !== 'addpetbo') {

            $reason = "";

            switch ($this->newPetData['reason']) {
                case 1:
                    $reason = "GIGANTON";
                    break;
                case 2:
                    $reason = "LACTANTE";
                    break;
                case 3:
                    $reason = "PATOLOGÍAS";
                    break;
            }

            $pet_name = $this->newPetData['pet-name'];

                $this->sendAdminEmail($custom, $pet_name, $reason);
        }

        $response = new stdClass();
        $content = ob_get_clean();
        $response->status = $status;
        $response->message = $message;
        switch ($action) {
            case 'addpet' || 'editpet' || 'addfirstpet':
                $response->params = array('content' => $content, 'avatar_thumb' => $avatar_thumb, 'is_show' => $is_show);
                break;
            default:
                $response->params = array('content' => $_html, 'page_nav' => $_html_page_nav);
                break;
        }


        if ($action == 'addpet' || $action == 'editpet' || $action == 'addfirstpet') {

            //Show results
            $controller = $action == 'addfirstpet' ? 'addfirstpet' : 'addpet';

            $shopperaccount_url = $this->context->link->getModuleLink('profileadv', $controller, array('reference' => $info_upload['data']['newpetdata']['pet-reference'], 'showdata' => 1));

            if ($status == 'error') {
                $delimeter_rewrite = '&';
                if (Configuration::get('PS_REWRITING_SETTINGS')) {
                    $delimeter_rewrite = '?';
                }
                $shopperaccount_url .= $delimeter_rewrite . 'message=' . $message . '&error=' . $error;
            }

            Tools::redirect($shopperaccount_url);
        } else {
            echo json_encode($response);
        }
        exit;
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

    private function validateFormData(): bool
    {
        foreach ($this->newPetData as $key => $value) {

            if ($key === 'pet-type' || $key === 'pet-genre' || $key === 'pet-esterilized' || $key === 'pet-activity' || $key === 'pet-feeding') {
                $key = str_replace('-', '', strrchr($key, '-'));

                if (!array_key_exists($value, $this->translationList[$key])) {
                    return false;
                }
            }
            if ($key === 'pet-weight' || $key === 'pet-desired-weight') {
                if (is_nan($value)) {
                    return false;
                } else {
                    if ((int)$value > 90 || (int)$value < 0) {
                        return false;
                    }
                }
            }
            if ($key === 'pet-breed') {
                $type = "";

                if ($this->newPetData['pet-type'] === 1) {
                    $type = 'dog';
                } elseif ($this->newPetData['pet-type'] === 2) {
                    $type = 'cat';
                } else {
                    return false;
                }

                if (!array_key_exists($value, $this->translationList['breed'][$type])) {
                    return false;
                }
            }
            if ($key === 'pet-pathology') {
                if (count($this->newPetData[$key]) > 0) {
                    foreach ($this->newPetData[$key] as $k => $val) {
                        if (!array_key_exists($val, $this->translationList['pathologies'])) {
                            return false;
                        }
                    }
                }
            }
            if ($key === 'pet-allergies') {
                if (count($this->newPetData[$key]) > 0) {
                    foreach ($this->newPetData[$key] as $k => $val) {
                        if (!array_key_exists($val, $this->translationList['allergies'])) {
                            return false;
                        }
                    }
                }
            }
            if ($value === 'addfirstpet') {
                return Validate::isEmail($this->newPetData['pet-customer-email']);
            }
        }

        return true;
    }

    private function isAmountBlocked($pet_reference, $customer)
    {

        $obj = new profileAdvanced();
        return $obj->isAmountBlockedByPetReferenceAndCustomer($pet_reference, $customer);
    }
    private function sendCustomerEmail($obj, $email, array $recommended_product)
    {
        if ((int)$this->newPetData["pet-amount"] > 0) {
            Mail::Send(
                (int)Configuration::get("PS_LANG_DEFAULT"),
                $this->newPetData["is_guest"] ? "pet_info_validator" : "pet_info",
                $this->newPetData["is_guest"] ? "Valida los datos de tu mascota" : "Aquí tienes los datos de tu mascota",
                array(
                    '{pet-name}' => $this->newPetData['pet-name'],
                    '{pet-reference}' => $this->newPetData['pet-reference'],
                    '{pet-amount}' => $this->newPetData['pet-amount'],
                    '{pet-amount-month}' => ($this->newPetData['pet-amount'] / 1000) * 30,
                    '{pet-recommended-product-name}' => $recommended_product['name'],
                    '{pet-recommended-product-url}' => $recommended_product['link'],
                    '{pet-amount-cost-daily}' => number_format((($recommended_product['daily_price']  * $this->newPetData['pet-amount'])), 2, ",", ","),
                    '{pet-amount-cost-monthly}' => number_format((($recommended_product['monthly_price'] * $this->newPetData['pet-amount'])), 2, ",", ","),
                    '{pet-isguest}' => $this->newPetData['is_guest'],
                    '{pet-validator-url}' => 'https://guauandcat.com/validate-pet-profile?reference=' . $this->newPetData['pet-reference'] . '&token=' . $obj->getValidationTokenFromReference($this->newPetData['pet-reference'])
                ),
                $email,
                null,
                'hola@guauandcat.com',
                null,
                null,
                null,
                $this->module->getLocalPath() . 'mails/'
            );
        }
    }

    private function sendAdminEmail(Customer $customer, string $petName, string $reason)
    {
        Mail::Send(
            (int)Configuration::get('PS_LANG_DEFAULT'),
            'pet_contact',
            ' Nueva mascota',
            array(
                '{email}' => Configuration::get('PS_SHOP_EMAIL'),
                '{message}' => 'El cliente <strong>#' . $customer->id . ' - ' . $customer->firstname . ' ' . $customer->lastname . ' (' . $customer->email . ')</strong> necesita ayuda con su mascota <strong>' . $petName . '</strong> sobre la porción diaria de comida ya que es un caso especial, el motivo es: "' . $reason . '"'
            ),
            'hola@guauandcat.com',
            null,
            'info@guauandcat.com',
            null,
            null,
            $this->module->getLocalPath() . 'mails/'
        );
    }
    public function getRecommendedProduct(array $data)
    {
        require_once _PS_MODULE_DIR_.'profileadv/classes/MenuConstants.php';
/*
Mida petita (menús 5kg - 200gr)
CACHORROS:

Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú cachorro cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Menú cachorro crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú cachorro cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú cachorro crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú cachorros cocinado perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú cachorro crudo perro pequeño (5kg - 200gr) 

Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat → Menú perros cachorro perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Menú Inici crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Crudo → Menú cachorro crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (variado) crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (menú variado) crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 10kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (Menú Variado) Crudo perro pequeño (5kg - 200gr)



ADULT:

Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú completo cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat crudo → Menú variado crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado  → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú Energy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú Energy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú Energy cocinado perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro pequeño (5kg - 200gr) 
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú Energy crudo perro pequeño (5kg - 200gr) 

Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat → Menú perros esterilizado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Menú Inici crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Crudo → Menú perros esterilizados crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (variado) crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (menú variado) crudo perro pequeño (5kg - 200gr)



Perro → Petit (menys de 10kg) →  Adult (Més d’ 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) →  Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) →  Adult (Més d’ 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (Menú Variado) Crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado →Come Guau&Cat cocinado → Menú Obesidad cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  →Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Menú Inici crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 10kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro pequeño (5kg - 200gr)

SÉNIOR:

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → No esterilizado → Come Guau&Cat cocinado → Menú sénior cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Menú sénior crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat → Menú Senior perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Menú Inici crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Crudo → Menú senior crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado →Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú senior crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 10kg) →  Sénior (Més de 9 anys) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) →  Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) →  Sénior (Més de 9 anys) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Senior Crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado →Come Guau&Cat cocinado → Menú Obesidad cuinat perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come BARF → Menú Inici crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro pequeño (5kg - 200gr)

Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 5kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro pequeño (5kg - 200gr)


Perro → Petit (menys de 10kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro pequeño (5kg - 200gr)
Perro → Petit (menys de 10kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro pequeño (5kg - 200gr)

Mida mitjana (menús 10kg - 500gr)

CACHORROS:

Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú cachorro cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Menú cachorro crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú cachorro cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú cachorro crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú cachorros cocinado perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú cachorro crudo perro mediano (500gr) 

Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat → Menú perros cachorro perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Menú Inici crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Crudo → Menú cachorro crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro pequeño (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) →No esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (variado) crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (menú variado) crudo perro mediano (500gr)



Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (Menú Variado) Crudo perro mediano (500gr)



ADULT:

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú completo cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Menú variado crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú Energy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú Energy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú Energy cocinado perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro mediano (500gr) 
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú Energy crudo perro mediano (500gr) 

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat → Menú perros esterilizado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Menú Inici crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Crudo → Menú perros esterilizados crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Peludo de trabajo → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Peludo de trabajo → No esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Peludo de trabajo → No esterilizado → Come BARF →  Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) ) → Peludo de trabajo → No esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Energy crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Energy crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Esterilizado Crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Menú Obesidad cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  →Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso →  esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat → Menú Obesidad perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Menú Inici crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro mediano (500gr)

SÉNIOR:

Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → No esterilizado → Come Guau&Cat cocinado → Menú sénior cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Menú sénior crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat → Menú Senior cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Menú Inici crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg)  → Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Crudo → Menú senior crudo perro mediano (500gr)


Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)

Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro →Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú senior crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Senior Crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Menú Obesidad cuinat perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg)  → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → No esterilizado → Come Guau&Cat → Menú Obesidad cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → No esterilizado → Come BARF → Menú Inici crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → esterilizado → No esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro pequeño (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro mediano (500gr)

Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro mediano (500gr)
Perro →  Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro mediano (500gr)


Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro mediano (500gr)
Perro → Mitjà (gos 5 - 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro mediano (500gr)

Mida gran i gegant (menús 15kg - 1kg)

CACHORROS:

Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú cachorro cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Menú cachorro crudo perro grande (1kg)

Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú cachorro cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú cachorro crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg) 
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú cachorros cocinado perro grande (1kg) 
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro grande (1kg) 
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú cachorro crudo perro grande (1kg) 

Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat → Menú perros cachorro cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Menú Inici crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Crudo → Menú cachorro crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)

Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat Cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Cachorro crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Cachorro crudo perro grande (1kg)


Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Cachorro (de 3 mesos a 1 any) → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (Menú Variado) Crudo perro grande (1kg)

ADULT:

Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Come Guau&Cat cocinado → Menú completo cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Menú variado crudo perro grande (1kg)

Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Menú Energy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Menú Energy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg) 
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cuinat → Menú Energy cocinado perro grande (1kg) 
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Menú Inicio crudo perro grande (1kg) 
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat Crudo → Menú Energy crudo perro grande (1kg) 

Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat → Menú perros esterilizado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Menú Inici crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Crudo → Menú perros esterilizados crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)

Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Completo sin pescado (variado) crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → No esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → No esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Energy (variado) crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → esterilizado → Peludo de trabajo → Come Guau&Cat cocinado →  Alergia Pescado → Menú Completo sin pescado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → esterilizado → Peludo de trabajo → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo Energy crudo perro grande (1kg)



Perro → Gran (> 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) →  Adult (Més d’ 1 any) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg)  → Adult (Més d’ 1 any)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Completo sin pescado (Menú Variado) Crudo perro grande (1kg)


Perro → Gran (> 25kg)  → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro grande (1kg)
Perro → Gran (> 25kg)  → Adult (Més d’ 1 any) → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Menú Obesidad cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  →Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg)  → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat → Menú Obesidad cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Menú Inici crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) → Tiene Sobrepeso → esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)

Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Adult (Més d’ 1 any) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro grande (1kg)

SÉNIOR:

Perro → Gran (> 25kg) → Sénior (Més de 9 anys) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) → No esterilizado → Come Guau&Cat cocinado → Menú sénior cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Menú sénior crudo perro grande (1kg)


Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat → Menú Senior perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Menú Inici crudo perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Crudo → Menú senior crudo perro grande (1kg)


Perro →  Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro →  Gran (> 25kg)  → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro →  Gran (> 25kg)  → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro →  Gran (> 25kg)  → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro →Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)

Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú senior crudo perro grande (1kg)


Perro → Gran (> 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Completo sin pescado cocinado perro grande (1kg)
Perro → Gran (> 25kg) →  Sénior (Més de 9 anys) → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Senior Crudo perro grande (1kg)


Perro → Gran (> 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Menú Obesidad cuinat perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Menú inicio crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat → Menú Obesidad perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come BARF → Menú Inici crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) → Tiene Sobrepeso → esterilizado → Come Guau&Cat Crudo → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)


Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg)  → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat cocinado → Alergia Pollo → Menú Allergy cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come BARF → Alergia Pollo → Menú Allergy crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo → Alergia Pollo → Menú Allergy crudo perro grande (1kg)

Perro →  Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Alergia Pescado → Menú Inicio cocinado perro grande (1kg)
Perro →  Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat cocinado → Alergia Pescado → Menú Obesidad cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come BARF → Alergia Pescado → Menú Inicio crudo perro grande (1kg)
Perro →  Gran (> 25kg) → Sénior (Més de 9 anys)  → Tiene Sobrepeso → No esterilizado → Come Guau&Cat crudo → Alergia Pescado → Menú Obesidad crudo perro grande (1kg)


Perro → Gran (> 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) →  Alergia Pescado → Menú Inicio Cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat Cocinado →  Alergia Pescado → Menú Obesidad cocinado perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come BARF →  Alergia Pescado → Menú Inicio Crudo perro grande (1kg)
Perro → Gran (> 25kg) → Sénior (Més de 9 anys) →  Tiene Sobrepeso → esterilizado → Come Guau&Cat crudo →  Alergia Pescado → Menú Obesidad Crudo perro grande (1kg)


Este es el array de ejemplo que se recibe en $data
Array
(
    [reference] => 28c8c8b4b694c981a1adf2c701e010bc
    [img] => 1142081429687ebf17465f0.jpg
    [type] => Perro
    [name] => Toribio
    [genre] => Macho
    [birth] => 2021-01-21
    [age] => 54
    [breed] => Mezcla Mini (<5KG)
    [esterilized] => Sí
    [weight] => 15
    [feeding] => Cocinado
    [activity] => Baja. Menos de 1 hora al día
    [physical_condition] => Normal
    [pathology] => Array
        (
            [0] => Obesidad
        )

    [allergies] => Array
        (
            [0] => Pollo
        )

    [comment] => 
    [amount] => 300
    [amount_blocked] => 0
    [id_customer] => 64778
    [customer_email] => nicolas.jnm@gmail.com
    [is_new] => 1
)

Estas son los posibles valores que se envían
    'genre' => array(
        'es' => array(
            '1' => 'Macho',
            '2' => 'Hembra'
        )
    ),
    'physical-condition' => array(
        'es' => array(
            '1' => 'Delgado',
            '2' => 'Normal',
            '3' => 'Gordito'
        )
    ),
    'activity' => array(
        'es' => array(
            '1' => 'Muy alta. Más de 4h al día',
            '2' => 'Alta. Entre 2 y 4 horas al día',
            '3' => 'Media. Entre 1 y 2 horas al día',
            '4' => 'Baja. Menos de 1 hora al día'
        )
    ),
    'feeding' => array(
        'es' => array(
            '1' => 'Pienso',
            '2' => 'Humedo (latas)',
            '3' => 'Barf',
            '4' => 'Cocinado',
            '5' => 'Deshidratado',
            '6' => 'Lactancia',
        )
    ),
    'type' => array(
        'es' => array(
            '1' => 'Perro',
            '2' => 'Gato',
        )
    ),
    'esterilized' => array(
        'es' => array(
            '1' => 'Sí',
            '2' => 'No',
        )
    ),
    'pathologies' => array(
        'es' => array(
            '1' => 'Nada',
            '2' => 'Obesidad',
            '3' => 'Enfermedades renales',
            '4' => 'Alteraciones pancreáticas',
            '5' => 'Enfermedades hepáticas',
            '6' => 'Problemas articulares',
            '7' => 'Problemas gastrointestinales',
            '8' => 'Alergias cutáneas',
            '9' => 'Intolerancias alimentarias'
        )
    ),
    'allergies' => array(
        'es' => array(
            '1' => 'Nada',
            '2' => 'Pollo',
            '3' => 'Pavo',
            '4' => 'Ternera',
            '5' => 'Cerdo',
            '6' => 'Cordero',
            '7' => 'Conejo',
            '8' => 'Pescado azul'
        )
    ),
 */
        // If the wizard didn't calculate the recommended menu we do it here so
        // the product recommendation follows the same logic described above
        if (!isset($data['recommended_menu'])) {
            include_once _PS_MODULE_DIR_ . 'profileadv/calculateamount.php';
            $calculator = new calculateAmount();
            $calcData = $calculator->calculateDailyEatAmount($data);
            if (isset($calcData['recommended_menu'])) {
                $data['recommended_menu'] = $calcData['recommended_menu'];
            }
        }

        switch ((int)$data['type']) {
            case 1:
                switch (true) {
                    case ($data['desired_weight'] < 5):
                        $size = 1;
                        break;
                    case ($data['desired_weight'] >= 5 && $data['desired_weight'] < 14):
                        $size = 2;
                        break;
                    case ($data['desired_weight'] >= 14 && $data['desired_weight'] < 25):
                        $size = 3;
                        break;
                    case ($data['desired_weight'] >= 25 && $data['desired_weight'] < 50):
                        $size = 4;
                        break;
                    case ($data['desired_weight'] > 50):
                        $size = 5;
                        break;
                    default:
                        $size = 1;
                        break;
                }
                break;
            case 2:
                $size = 1; //Cats by default
                break;
            default:
                $size = 1;
                break;
        }

        $menuSizeMap = [
            ProfileadvMenuConstants::MENU_ENERGY_COCINADO_SMALL => 1,
            ProfileadvMenuConstants::MENU_ENERGY_CRUDO_SMALL => 1,
            ProfileadvMenuConstants::MENU_ENERGY_COCINADO_MEDIUM => 3,
            ProfileadvMenuConstants::MENU_ENERGY_CRUDO_MEDIUM => 3,
            ProfileadvMenuConstants::MENU_ENERGY_COCINADO_LARGE => 4,
            ProfileadvMenuConstants::MENU_ENERGY_CRUDO_LARGE => 4,
            ProfileadvMenuConstants::MENU_OBESIDAD_COCINADO_SMALL => 1,
            ProfileadvMenuConstants::MENU_OBESIDAD_CRUDO_SMALL => 1,
            ProfileadvMenuConstants::MENU_OBESIDAD_COCINADO_MEDIUM => 3,
            ProfileadvMenuConstants::MENU_OBESIDAD_CRUDO_MEDIUM => 3,
            ProfileadvMenuConstants::MENU_OBESIDAD_COCINADO_LARGE => 4,
            ProfileadvMenuConstants::MENU_OBESIDAD_CRUDO_LARGE => 4,
            ProfileadvMenuConstants::MENU_COMPLETO_SIN_PESCADO_COCINADO => 1,
            ProfileadvMenuConstants::MENU_COMPLETO_SIN_PESCADO_CRUDO => 1,
        ];

        if (isset($data['recommended_menu']) && isset($menuSizeMap[$data['recommended_menu']])) {
            $size = $menuSizeMap[$data['recommended_menu']];
        }

        $sizePrices = [
            1 => 4.64,
            2 => 4.64,
            3 => 4.27,
            4 => 4.06,
            5 => 4.06,
        ];


        //Recommended product
        if (isset($data['recommended_menu'])) {
            $product = new Product((int)$data['recommended_menu'], true, (int)Context::getContext()->language->id);
        } elseif ((int)$data['feeding'] === 3 && (int) $data['type'] === 1) { //Recommend barf for dogs
            $barfMap = [
                1 => self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250,
                2 => self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250,
                3 => self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_500,
                4 => self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_1000,
                5 => self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_1000,
            ];
            $recommended = $barfMap[$size] ?? self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250;
            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
        } elseif ((int)$data['feeding'] === 3 && (int) $data['type'] === 2) { //Recommend barf for cats
            $product = new Product((int)self::DEFAULT_RECOMMENDED_BARF_PRODUCT_CAT, true, (int)Context::getContext()->language->id);
        } elseif ((int) $data['type'] === 2) { //Cat default menu
            $product = new Product((int)self::DEFAULT_RECOMMENDED_PRODUCT_CAT, true, (int)Context::getContext()->language->id);
        } else {
            $dogMap = [
                1 => self::DEFAULT_RECOMMENDED_PRODUCT_DOG_250,
                2 => self::DEFAULT_RECOMMENDED_PRODUCT_DOG_250,
                3 => self::DEFAULT_RECOMMENDED_PRODUCT_DOG_500,
                4 => self::DEFAULT_RECOMMENDED_PRODUCT_DOG_1000,
                5 => self::DEFAULT_RECOMMENDED_PRODUCT_DOG_1000,
            ];
            $recommended = $dogMap[$size] ?? self::DEFAULT_RECOMMENDED_PRODUCT_DOG_250;
            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
        }

        $link = new Link();

        $product_recommend = $product->getFields();

        $product_recommend['name'] = $product->name;
        if ((int) $data['type'] === 2) {
            //$product_recommend['price'] = number_format((float)($product->price * 1.10), 2, '.', '') / 6; //Default combination weight for cats
        } else {
            //$product_recommend['price'] = number_format((float)($product->price * 1.10), 2, '.', '') / 15; //Default combination weight for dogs
        }

        $product_recommend['link'] = $link->getProductLink($product);

        $img = $product->getCover($product->id);
        $product_recommend['image'] =  $link->getImageLink((string)$product->link_rewrite[(int)Context::getContext()->language->id], (int)$img['id_image'], 'home_default');

        $product_recommend['price'] = $sizePrices[$size] ?? $sizePrices[1];
        $product_recommend['monthly_price'] = ($product_recommend['price'] / 1000) * 30;
        $product_recommend['daily_price'] = ($product_recommend['price'] / 1000);

        return $product_recommend;
    }
}
