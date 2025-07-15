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
                if ((int)$this->newPetData['pet-amount'] > 0) {
                    Mail::Send(
                        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                        $this->newPetData['is_guest'] ? 'pet_info_validator' : 'pet_info', // email template file to be use
                        $this->newPetData['is_guest'] ? 'Valida los datos de tu mascota' : 'Aquí tienes los datos de tu mascota', // email subject
                        array(
                            '{pet-name}' => $this->newPetData['pet-name'],
                            '{pet-reference}' => $this->newPetData['pet-reference'],
                            '{pet-amount}' => $this->newPetData['pet-amount'],
                            '{pet-amount-month}' => ($this->newPetData['pet-amount'] / 1000) * 30,
                            '{pet-recommended-product-name}' => $recommended_product['name'],
                            '{pet-recommended-product-url}' => $recommended_product['link'],
                            '{pet-amount-cost-daily}' => number_format((($recommended_product['daily_price']  * $this->newPetData['pet-amount'])), 2, ",", ","),
                            //'{pet-amount-cost-daily}' => number_format((($this->newPetData['pet-amount'] / 1000) * 30) * ($recommended_product['price'] / 30), 2, ",", ","),
                            '{pet-amount-cost-monthly}' => number_format((($recommended_product['monthly_price'] * $this->newPetData['pet-amount'])), 2, ",", ","),
                            //'{pet-amount-cost-monthly}' => number_format((($this->newPetData['pet-amount'] / 1000) * 30) * $recommended_product['price']/1000, 2, ",", ","),
                            '{pet-isguest}' => $this->newPetData['is_guest'],
                            '{pet-validator-url}' => 'https://guauandcat.com/validate-pet-profile?reference=' . $this->newPetData['pet-reference'] . '&token=' . $obj->getValidationTokenFromReference($this->newPetData['pet-reference']) . ''
                        ),
                        $email_customer, // receiver email address
                        null, //receiver name
                        'hola@guauandcat.com', //from email address
                        null,  //from name
                        null,
                        null,
                        $this->module->getLocalPath() . 'mails/' // 11th parameter is template path
                    );
                }
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

            Mail::Send(
                (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                'pet_contact', // email template file to be use
                ' Nueva mascota', // email subject
                array(
                    '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                    '{message}' => 'El cliente <strong>#' . $custom->id . ' - ' . $custom->firstname . ' ' . $custom->lastname . ' (' . $custom->email . ')</strong> necesita ayuda con su mascota <strong>' . $pet_name . '</strong> sobre la porción diaria de comida ya que es un caso especial, el motivo es: "' . $reason . '"'
                ),
                'hola@guauandcat.com', // receiver email address
                null, //receiver name
                'info@guauandcat.com', //from email address
                null,  //from name
                null,
                null,
                $this->module->getLocalPath() . 'mails/' // 11th parameter is template path
            );
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

    public function getRecommendedProduct(array $data)
    {

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


        //Recommended product
        if ((int)$data['feeding'] === 3 && (int) $data['type'] === 1) { //Recommend barf for dogs
            switch ($size) {
                case 1:
                case 2:
                    $recommended = self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250;
                    $product_recommend['price'] = 4.64;
                    break;
                case 3:
                    $recommended = self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_500;
                    $product_recommend['price'] = 4.27;
                    break;
                case 4:
                case 5:
                    $recommended = self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_1000;
                    $product_recommend['price'] = 4.06;
                    break;
                default:
                    $recommended = self::DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250;
                    $product_recommend['price'] = 4.64;
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
        } elseif ((int)$data['feeding'] === 3 && (int) $data['type'] === 2) { //Recommend barf for cats
            $product = new Product((int)self::DEFAULT_RECOMMENDED_BARF_PRODUCT_CAT, true, (int)Context::getContext()->language->id);
        } elseif ((int) $data['type'] === 2) { //Cat default menu
            $product = new Product((int)self::DEFAULT_RECOMMENDED_PRODUCT_CAT, true, (int)Context::getContext()->language->id);
        } else {
            switch ($size) {
                case 1:
                case 2:
                    $recommended = self::DEFAULT_RECOMMENDED_PRODUCT_DOG_250;
                    $product_recommend['price'] = 4.64;
                    break;
                case 3:
                    $recommended = self::DEFAULT_RECOMMENDED_PRODUCT_DOG_500;
                    $product_recommend['price'] = 4.27;
                    break;
                case 4:
                case 5:
                    $recommended = self::DEFAULT_RECOMMENDED_PRODUCT_DOG_1000;
                    $product_recommend['price'] = 4.06;
                    break;
                default:
                    $recommended = self::DEFAULT_RECOMMENDED_PRODUCT_DOG_250;
                    $product_recommend['price'] = 4.64;
                    break;
            }
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

        switch ($size) {
            case 1:
            case 2:
                $product_recommend['price'] = 4.64;
                break;
            case 3:
                $product_recommend['price'] = 4.27;
                break;
            case 4:
            case 5:
                $product_recommend['price'] = 4.06;
                break;
            default:
                $product_recommend['price'] = 4.64;
                break;
        }

        $product_recommend['monthly_price'] = ($product_recommend['price'] / 1000) * 30;
        $product_recommend['daily_price'] = ($product_recommend['price'] / 1000);

        return $product_recommend;
    }
}
