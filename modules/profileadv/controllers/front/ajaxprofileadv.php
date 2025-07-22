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
        // Get the age in years today - $data['birth']
        $age = $this->calculateAge($data);

/*
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
        //Perro → Petit (menys de 5kg) → Cachorro (de 3 mesos a 1 any) → No esterilizado → Come pienso / cocinada / deshidratada / húmeda (latas) → Menú Inici cuinat perro pequeño (5kg - 200gr)
        //public const MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO = 3886;
        // Si es perro menos de 5kg (size1) $age está entre los 3 meses y 1 año, $data['esterilized'] == Sí, $data['feeding'] == Pienso, Cocinado, Deshidratado o Húmedo (latas) entonces se tomará el valor de la constante MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO de la clase ProfileadvMenuConstants.
        if( (int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 && (int)$data['esterilized'] === 1 && in_array((int)$data['feeding'], [1, 2, 5, 4])
        ) {
            $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO;
            $product_recommend['price'] = 4.64;
        }

        // Perro → Petit (<5kg) → Cachorro → No esterilizado → Guau&Cat cocinado
        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
        (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 4) {
        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_GUAUCAT_COCINADO;
        $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
        $product_recommend['price'] = 4.64;
        }

        // Perro → Petit (<5kg) → Cachorro → No esterilizado → BARF
        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
        (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 3) {
        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_BAF;
        $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
        $product_recommend['price'] = 4.64;
        }

        // Perro → Petit (<5kg) → Cachorro → No esterilizado → Guau&Cat crudo
        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 4 && in_array('Guau&Cat crudo', $data)) {
            $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_GUAUCAT_CRUDO;
            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
            }

            if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2 && isset($data['activity']) && (int)$data['activity'] <= 2) {
        
            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
            }
        
            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 && isset($data['activity']) && (int)$data['activity'] <= 2) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PIENSO_ALERGIA_POLLO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_BAF_ALERGIA_POLLO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pescado azul', $data['allergies'], true)) {
        
            $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
        
            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO10KG1A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO5KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_CACHORRO10KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PIENSO_ALERGIA_POLLO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_BAF_ALERGIA_POLLO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pescado', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pescado', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
                    }
                    break;
                case 3:
                    if ((int)$data['activity'] <= 2) {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                    }
                    break;
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            in_array($data['physical_condition'], ['Gordito'], true)) {

            // Sin alergias específicas
            if (empty($data['allergies'])) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_BAF_SOBREPESO;
                        break;
                }
            }

            // Con alergia a pollo
            if (in_array('Pollo', $data['allergies'], true)) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_BAF_ALERGIA_POLLO_SOBREPESO;
                        break;
                }
            }

            // Con alergia a pescado
            if (in_array('Pescado', $data['allergies'], true)) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO_SOBREPESO;
                        break;
                }
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 12 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1) {

            // Sin alergias específicas
            if (empty($data['allergies'])) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_BAF_SOBREPESO;
                        break;
                }
            }

            // Con alergia a pollo
            if (in_array('Pollo', $data['allergies'], true)) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT5KGM1A_ESTILIZADO_BAF_ALERGIA_POLLO_SOBREPESO;
                        break;
                }
            }

            // Con alergia a pescado
            if (in_array('Pescado', $data['allergies'], true)) {
                switch ((int)$data['feeding']) {
                    case 1: case 2: case 4: case 5:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT10KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT10KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO_SOBREPESO;
                        break;
                }
            }

            $product = new Product((int)$recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.64;
        }

        if ((int)$data['type'] === 1 && in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1 && $size === 2 && $age >= 12 &&
            in_array('Pescado azul', $data['allergies'], true) &&
            (int)$data['feeding'] === 3) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT10KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO_SOBREPESO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1 && $size === 2 && $age >= 12 &&
            in_array('Pescado azul', $data['allergies'], true) &&
            (int)$data['feeding'] === 4) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_ADULT10KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO_SOBREPESO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && empty($data['allergies'])) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_BAF;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 4) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_COCINADO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 1) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_BAF;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 1 && (int)$data['feeding'] === 4) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_GUAUCAT_CRUDO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 4: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && in_array('Pescado azul', $data['allergies'], true)) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 3 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            (int)$data['esterilized'] === 2 && (int)$data['feeding'] === 4 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 2 && $age >= 108 &&
            (int)$data['esterilized'] === 1 && in_array('Pescado azul', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_PIENSO_SOBREPESO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_BAF_SOBREPESO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO_SOBREPESO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_BAF_ALERGIA_POLLO_SOBREPESO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO_SOBREPESO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO_SOBREPESO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_BAF_ALERGIA_POLLO_SOBREPESO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO_SOBREPESO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 1 && $age >= 108 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 2 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_BAF_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR5KGM9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO_SOBREPESO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 2 && $age >= 108 &&
            in_array($data['physical_condition'], ['Gordito'], true) &&
            (int)$data['esterilized'] === 1 &&
            in_array('Pescado azul', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_PIENSO_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_BAF_ALERGIA_PESCADO_SOBREPESO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_PETIT_SENIOR10KG9A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO_SOBREPESO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 4.06;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 2) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_PIENSO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_GUAUCAT_COCINADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_GUAUCAT_CRUDO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            isset($data['activity']) && $data['activity'] === 'trabajo') {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            isset($data['activity']) && $data['activity'] === 'trabajo' &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 1: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            (!isset($data['activity']) || $data['activity'] !== 'trabajo')) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_PIENSO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_GUAUCAT_COCINADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_GUAUCAT_CRUDO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 1 &&
            (!isset($data['activity']) || $data['activity'] !== 'trabajo') &&
            in_array('Pollo', $data['allergies'], true)) {

            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 3 && $age < 12 &&
            (int)$data['esterilized'] === 0 &&
            in_array('Pescado', $data['allergies'], true) &&
            (!isset($data['activity']) || $data['activity'] !== 'trabajo')) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    break;
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_CACHORRO25KG3M1A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 12 &&
            (int)$data['esterilized'] === 0 &&
            (isset($data['activity']) && $data['activity'] === 'trabajo') &&
            empty($data['allergies'])) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO;
                    break;
                case 2: // Guau&Cat cocinado
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 12 &&
            (int)$data['esterilized'] === 1 &&
            (isset($data['activity']) && $data['activity'] === 'trabajo') &&
            empty($data['allergies'])) {

            switch ((int)$data['feeding']) {
                case 1: case 2: case 5:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO;
                    break;
                case 2: // Guau&Cat cocinado
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        if ((int)$data['type'] === 1 && $size === 3 && $age >= 12 &&
            (int)$data['esterilized'] === 0 &&
            in_array('pescado', $data['allergies'])) {

            $isWorking = (isset($data['activity']) && $data['activity'] === 'trabajo');

            switch ((int)$data['feeding']) {
                case 1: case 5:
                    $recommended = $isWorking
                        ? ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO
                        : ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    break;
                case 2:
                    $recommended = $isWorking
                        ? ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO
                        : ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = $isWorking
                        ? ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO
                        : ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = $isWorking
                        ? ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO
                        : ProfileadvMenuConstants::MENU_MITJA_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.60;
        }

        // Mitjà sénior (>9 anys), no esterilitzat
        if ((int)$data['type'] === 1 && $size === 3 && $age >= 9 * 12 &&
        (int)$data['esterilized'] === 0) {

            $hasPolloAllergy   = in_array('pollo', $data['allergies']);
            $hasPescadoAllergy = in_array('pescado', $data['allergies']);

            switch ((int)$data['feeding']) {
                case 1: case 5: // pienso o deshidratado
                    if ($hasPolloAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO;
                    } elseif ($hasPescadoAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_PIENSO;
                    }
                    break;
                case 2: // Guau&Cat cocinado
                    if ($hasPolloAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    } elseif ($hasPescadoAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO;
                    }
                    break;
                case 3: // BARF
                    if ($hasPolloAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                    } elseif ($hasPescadoAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_BAF;
                    }
                    break;
                case 4: // Guau&Cat crudo
                    if ($hasPolloAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    } elseif ($hasPescadoAllergy) {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    } else {
                        $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO;
                    }
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_PIENSO;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 5.80; // ajustar según política de precios
            }

            // Mitjà sénior (>9 anys), sobrepeso
            if ((int)$data['type'] === 1 && $size === 3 && $age >= 9 * 12 &&
            $data['overweight'] && (int)$data['esterilized'] === $esterFlag) {

            switch ((int)$data['feeding']) {
                case 2: // Guau&Cat cocinado
                    $recommended = $esterFlag
                        ? ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO_SOBREPESO
                        : ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                    break;
                case 3: // BARF
                    $recommended = $esterFlag
                        ? ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_ESTILIZADO_BAF_SOBREPESO
                        : ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_BAF_SOBREPESO;
                    break;
                case 4: // Guau&Cat crudo
                    $recommended = $esterFlag
                        ? ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_ESTILIZADO_GUAUCAT_CRUDO_SOBREPESO
                        : ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                    break;
                default:
                    // En caso de pienso/deshidratado o no contemplado
                    $recommended = $esterFlag
                        ? ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_ESTILIZADO_PIENSO_SOBREPESO
                        : ProfileadvMenuConstants::MENU_MITJA_SENIOR25KG9A_NOESTERILIZADO_PIENSO_SOBREPESO;
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 6.20; // ajuste orientativo
        }

        // Gran tamaño, cachorro (3m-1a), alergias
        if ((int)$data['type'] === 1 && $size === 4 && $age >= 3 && $age <= 12 &&
        ($data['allergy_pollo'] || $data['allergy_pescado'])) {

            if ($data['allergy_pollo']) {
                if ((int)$data['work'] === 1) {
                    switch ((int)$data['feeding']) {
                        case 2: // Guau&Cat cocinado
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                            break;
                        case 3: // BARF
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                            break;
                        case 4: // Guau&Cat crudo
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                            break;
                        default:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
                    }
                } else {
                    switch ((int)$data['feeding']) {
                        case 2:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                            break;
                        case 3:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_BAF_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                            break;
                        case 4:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                            break;
                        default:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PIENSO_ALERGIA_POLLO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO;
                    }
                }
            } elseif ($data['allergy_pescado']) {
                if ((int)$data['work'] === 1) {
                    switch ((int)$data['feeding']) {
                        case 2:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                            break;
                        case 3:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                            break;
                        case 4:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                            break;
                        default:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
                    }
                } else {
                    switch ((int)$data['feeding']) {
                        case 2:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                            break;
                        case 3:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                            break;
                        case 4:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                            break;
                        default:
                            $recommended = $esterFlag
                                ? ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PIENSO_ALERGIA_PESCADO
                                : ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                    }
                }
            }

            $product = new Product($recommended, true, (int)Context::getContext()->language->id);
            $product_recommend['price'] = 9.95; // Precio orientativo
        }

        // Gran (>25 kg), cachorro (3 m‑1 a), esterilizado, peludo trabajo, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age >= 3 && $age <= 12 && $esterFlag && 
        $data['work'] == 1 && $data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2: // Guau&Cat cocinado
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3: // BARF
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    break;
                case 4: // Guau&Cat crudo
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default: // pienso/cocinado estándar
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // Gran (>25 kg), cachorro (3 m‑1 a), esterilizado, alergia pescado, sin trabajo
        if ((int)$data['type'] === 1 && $size === 4 && $age >= 3 && $age <= 12 && $esterFlag && 
        $data['allergy_pescado'] && $data['work'] == 0) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // Gran (>25 kg), cachorro (3 m‑1 a), esterilizado, sin alergias, sin condición
        // (se añade la alimentación habitual)
        if ((int)$data['type'] === 1 && $size === 4 && $age >= 3 && $age <= 12 && $esterFlag && !$data['allergy_pollo'] && !$data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_COCINADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_GUAUCAT_CRUDO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_CACHORRO25KG1A_ESTILIZADO_PIENSO;
            }
        }

        // Gran (>25 kg), adulto (>1 a), sin esterilizar, sin condición
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && !$esterFlag && !$data['allergy_pollo'] && !$data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_BAF;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PIENSO;
            }
        }

        // Resto del flujo: creación del producto, precio de ejemplo, etc.
        $product = new Product($recommended, true, (int)Context::getContext()->language->id);
        $product_recommend['price'] = 12.95;

        // Gran (>25kg), adulto (>1a), NO esterilizado, peludo trabajo, alergia pollo
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && !$esterFlag && $data['work'] == 1 && $data['allergy_pollo']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
            }
        }

        // Gran (>25kg), adulto (>1a), esterilizado, peludo trabajo, alergia pollo
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $esterFlag && $data['work'] == 1 && $data['allergy_pollo']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_POLLO;
            }
        }

        // Gran (>25kg), adulto (>1a), esterilizado, sin trabajo, alergia pollo
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $esterFlag && $data['allergy_pollo'] && $data['work'] == 0) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PIENSO_ALERGIA_POLLO;
            }
        }

        // Gran (>25kg), adulto (>1a), NO esterilizado, peludo trabajo, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && !$esterFlag && $data['work'] == 1 && $data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // Gran (>25kg), adulto (>1a), esterilizado, peludo trabajo, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $esterFlag && $data['work'] == 1 && $data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PELUDO_TRABAJO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // Gran (>25kg), adulto (>1a), esterilizado, sin trabajo, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $esterFlag && $data['allergy_pescado'] && $data['work'] == 0) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // GRAN >25kg, adulto, esterilizado, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $age <= 108 && $esterFlag && $data['allergy_pescado']) {
            switch ((int)$data['feeding']) {
                case 2:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                    break;
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
            }
        }

        // GRAN >25kg, adulto, sobrepeso
        if ((int)$data['type'] === 1 && $size === 4 && $age > 12 && $age <= 108 && $data['overweight']) {
            if ($esterFlag) {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_BAF_SOBREPESO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_ESTILIZADO_PIENSO_SOBREPESO;
                }
            } else {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_BAF_SOBREPESO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_ADULT25KG1A_NOESTERILIZADO_PIENSO_SOBREPESO;
                }
            }
        }

        // GRAN >25kg, senior (>9 años)
        if ((int)$data['type'] === 1 && $size === 4 && $age > 108 && !$data['allergy_pollo']) {
            if ($esterFlag) {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_BAF;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_CRUDO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_PIENSO;
                }
            } else {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_BAF;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_PIENSO;
                }
            }
        }

        // GRAN >25kg, senior, alergia pollo
        if ((int)$data['type'] === 1 && $size === 4 && $age > 108 && $data['allergy_pollo']) {
            if ($esterFlag) {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_PIENSO_ALERGIA_POLLO;
                }
            } else {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_BAF_ALERGIA_POLLO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_PIENSO_ALERGIA_POLLO;
                }
            }
        }

        // GRAN >25kg, senior, esterilizado, alergia pollo
        if ((int)$data['type'] === 1 && $size === 4 && $age > 108 && $esterFlag && $data['allergy_pollo']) {
            switch ((int)$data['feeding']) {
                case 3:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_BAF_ALERGIA_POLLO;
                    break;
                case 4:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_POLLO;
                    break;
                default:
                    $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_POLLO;
            }
        }

        // GRAN >25kg, senior, alergia pescado
        if ((int)$data['type'] === 1 && $size === 4 && $age > 108 && $data['allergy_pescado']) {
            if ($esterFlag) {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_BAF_ALERGIA_PESCADO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_PIENSO_ALERGIA_PESCADO;
                }
            } else {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_ALERGIA_PESCADO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_BAF_ALERGIA_PESCADO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_ALERGIA_PESCADO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_PIENSO_ALERGIA_PESCADO;
                }
            }
        }

        // GRAN >25kg, senior, sobrepeso
        if ((int)$data['type'] === 1 && $size === 4 && $age > 108 && $data['overweight']) {
            if ($esterFlag) {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_BAF_SOBREPESO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_ESTILIZADO_PIENSO_SOBREPESO;
                }
            } else {
                switch ((int)$data['feeding']) {
                    case 2:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_COCINADO_SOBREPESO;
                        break;
                    case 3:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_BAF_SOBREPESO;
                        break;
                    case 4:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_GUAUCAT_CRUDO_SOBREPESO;
                        break;
                    default:
                        $recommended = ProfileadvMenuConstants::MENU_GRAN_SENIOR25KG9A_NOESTERILIZADO_PIENSO_SOBREPESO;
                }
            }
        }

        if (isset($data['recommended_menu'])) {
            $product = new Product((int)$data['recommended_menu'], true, (int)Context::getContext()->language->id);
        } elseif ((int)$data['feeding'] === 3 && (int) $data['type'] === 1) { //Recommend barf for dogs
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

    public function calculateAge(array $data): ?int
    {
        if (empty($data['birth'])) {
            return null;
        }

        try {
            $birthDate = new DateTime($data['birth']);
            $currentDate = new DateTime();
            $interval = $birthDate->diff($currentDate);
            return $interval->y;
        } catch (Exception $e) {
            // Log error or handle accordingly
            return null;
        }
    }
}
