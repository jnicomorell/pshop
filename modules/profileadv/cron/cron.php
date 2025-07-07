<?php

require_once dirname(__FILE__) . '/../../../config/config.inc.php';
require_once dirname(__FILE__) . '/../classes/profileadvanced.class.php';
require_once dirname(__FILE__) . '/../controllers/front/ajaxprofileadv.php';

class CrmAccountValidationCron
{
    private $action;
    private $token = 'u6cZoiewr6sdcfjsdf';
    private $newPetData = [];
    private $plantillaEmail;
    private $emailsendState;
    private $profileAdv;
    private $sendEmail = true;

    const DEFAULT_RECOMMENDED_PRODUCT_DOG_250 = 3886;
    const DEFAULT_RECOMMENDED_PRODUCT_DOG_500 = 3901;
    const DEFAULT_RECOMMENDED_PRODUCT_DOG_1000 = 3936;
    const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_250 = 3899;
    const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_500 = 3924;
    const DEFAULT_RECOMMENDED_BARF_PRODUCT_DOG_1000 = 3949;
    const DEFAULT_RECOMMENDED_PRODUCT_CAT = 129;
    const DEFAULT_RECOMMENDED_BARF_PRODUCT_CAT = 130;

    public function __construct()
    {
        if (!isset($_GET['token']) || $_GET['token'] !== $this->token) {
            die('Token inválido');
        }
        $this->action = isset($_GET['action']) ? $_GET['action'] : '';

        $this->profileAdv = new profileAdvanced();
    }

    public function init()
    {
        echo 'Acción: ' . $this->action . '<br>';
        echo 'Token: ' . $this->token . '<br>';

        switch ($this->action) {
            case 'cada1hora':
                $end   = date('Y-m-d H:i:s');
                $start = date('Y-m-d H:i:s', strtotime('-1 hour -30 minutes'));
                $this->emailsendState = 1;
                $list  = $this->profileAdv->getNotValidatedPetsBetweenDates($end, $start, $this->emailsendState);
                $this->plantillaEmail = 'remember_1';
                $this->enviarCorreoCliente($list);
                break;
            case 'cada24hs':
                // $end   = date('Y-m-d H:i:s', strtotime('-1 hour'));
                $end   = date('Y-m-d H:i:s', strtotime('-24 hour'));
                $start = date('Y-m-d H:i:s', strtotime('-48 hour'));
                $this->emailsendState = 2;
                $list  = $this->profileAdv->getNotValidatedPetsBetweenDates($end, $start, $this->emailsendState);
                $this->plantillaEmail = 'remember_24';
                $this->enviarCorreoCliente($list);
                break;
            case 'cada48hs':
                $end   = date('Y-m-d H:i:s', strtotime('-48 hour'));
                $start = date('Y-m-d H:i:s', strtotime('-72 hour'));
                $this->emailsendState = 3;
                $list  = $this->profileAdv->getNotValidatedPetsBetweenDates($end, $start, $this->emailsendState);
                $this->plantillaEmail = 'remember_48';
                $this->enviarCorreoCliente($list);
                break;
            case 'cada72hs':
                $end   = date('Y-m-d H:i:s', strtotime('-72 hour'));
                $start = date('Y-m-d H:i:s', strtotime('-120 hour'));
                $this->emailsendState = 4;
                $list  = $this->profileAdv->getNotValidatedPetsBetweenDates($end, $start, $this->emailsendState);
                $this->plantillaEmail = 'remember_72';
                $this->enviarCorreoCliente($list);
                break;
            case 'cada120hs':
                $end   = date('Y-m-d H:i:s', strtotime('-120 hour'));
                $start = date('Y-m-d H:i:s', strtotime('-360 hour'));
                $this->emailsendState = 5;
                $list  = $this->profileAdv->getNotValidatedPetsBetweenDates($end, $start, $this->emailsendState);
                $this->plantillaEmail = 'remember_120';
                $this->enviarCorreoCliente($list);
                break;
            default:
                echo 'Acción no válida.';
                break;
        }
    }

    public function enviarCorreoCliente($clientes)
    {
        echo '------------------------<br>';
        echo 'Estado Email: ' . $this->emailsendState . '<br>';
        echo '------------------------<br>';
        if (empty($clientes)) {
            echo 'No hay clientes para enviar correo.';
            return;
        }
        echo '------------------------<br>';
        echo 'Clientes: ' . count($clientes) . '<br>';
        echo '------------------------<br>';


        foreach ($clientes as $cliente) {
            $this->newPetData = array(
                'id' => $cliente['id'],
                'id_customer' => $cliente['id_customer'],
                'avatar' => $cliente['avatar'],
                'avatar_thumb' => $cliente['avatar_thumb'],
                'is_show' => $cliente['is_show'],
                'reference' => $cliente['reference'],
                'type' => $cliente['type'],
                'esterilized' => $cliente['esterilized'],
                'name' => $cliente['name'],
                'genre' => $cliente['genre'],
                'birth' => $cliente['birth'],
                'breed' => $cliente['breed'],
                'weight' => $cliente['weight'],
                'desired_weight' => $cliente['desired_weight'],
                'feeding' => $cliente['feeding'],
                'activity' => $cliente['activity'],
                'physical_condition' => $cliente['physical_condition'],
                'pathology' => $cliente['pathology'],
                'allergies' => $cliente['allergies'],
                'amount' => $cliente['amount'],
                'is_amount_blocked' => $cliente['is_amount_blocked'],
                'comment' => $cliente['comment'],
                'message' => $cliente['message'],
                'is_validated' => $cliente['is_validated'],
                'validate_token' => $cliente['validate_token'],
                'sended_email' => $cliente['sended_email'],
                'date_add' => $cliente['date_add'],
                'date_upd' => $cliente['date_upd'],
                'active' => $cliente['active']
            );

            // dump('--------------- INICIA EL PROCESO DE ENVIO DE EMAIL ------------------');

            // dump('variable $this->newPetData ');
            // dump($this->newPetData);

            if ($this->newPetData['id_customer'] === 1) {
                $email_customer = $this->newPetData['message'];
                $this->newPetData['is_guest'] = true;
            } else {
                $customer = new Customer($this->newPetData['id_customer']);
                if (!Validate::isLoadedObject($customer)) {
                    // dump('El cliente no existe');
                    $this->sendEmail = false;
                    continue; // Salto al siguiente cliente si el cliente no existe
                }
                // si pertenece al grupo de clientes con ID 4
                $groups = $customer->getGroups();
                // dump('Grupos del cliente: ');
                // dump($groups);
                if (is_array($groups) && in_array(4, $groups)) {
                    // dump('El cliente pertenece al grupo de clientes con ID 4');
                    if ($this->emailsendState !== 5) {
                        $this->sendEmail = false;
                        // dump('El cliente pertenece al grupo de clientes con ID 4 y el estado no es 5');
                    }
                    $this->sendEmail = true;
                    continue; // Salto al siguiente cliente si pertenece al grupo de clientes con ID 4
                }
                $email_customer = $customer->email;
                $this->newPetData['is_guest'] = false;
            }

            // dump('Cliente: ' . $this->newPetData['id_customer']);

            $recommended_product = $this->getRecommendedProduct($this->newPetData);

            // dump('Producto recomendado: ');
            // dump($recommended_product);

            // dump($this->sendEmail);
            if ($this->sendEmail) {
                $subject = $this->getEmailSubject();
                Mail::Send(
                    (int)(Configuration::get('PS_LANG_DEFAULT')),
                    $this->plantillaEmail,
                    $subject,
                    array(
                        '{pet-name}' => $this->newPetData['name'],
                        '{pet-reference}' => $this->newPetData['reference'],
                        '{pet-amount}' => $this->newPetData['amount'],
                        '{pet-amount-month}' => ($this->newPetData['amount'] / 1000) * 30,
                        '{pet-recommended-product-name}' => $recommended_product['name'],
                        '{pet-recommended-product-url}' => $recommended_product['link'],
                        '{pet-url}' => 'https://guauandcat.com/carrito?action=show' . $this->getUTMParameters(),
                        '{pet-amount-cost-daily}' => number_format((($recommended_product['daily_price']  * $this->newPetData['amount'])), 2, ",", ","),
                        '{pet-amount-cost-monthly}' => number_format((($recommended_product['monthly_price'] * $this->newPetData['amount'])), 2, ",", ","),
                        '{pet-isguest}' => $this->newPetData['is_guest'],
                        // '{validator-url}' => 'https://guauandcat.com/validate-pet-profile?reference=' . $this->newPetData['reference'] . '&token=' . $obj->getValidationTokenFromReference($this->newPetData['pet-reference']) . ''
                    ),
                    $email_customer,
                    //'informatica@guauandcat.com',
                    NULL, //receiver name
                    'hola@guauandcat.com', //from email address
                    NULL,  //from name
                    NULL,
                    NULL,
                    _PS_MODULE_DIR_ . 'profileadv/mails'
                );

                $this->profileAdv->updatePetCustomerSendedEmail($this->newPetData['reference'], $this->emailsendState);
            } else {
                // dump('No se enviará el correo al cliente: ' . $email_customer . ', por pertenecer al grupo con ID4');
                // continue; // Salto al siguiente cliente si no se enviará el correo
            }

            $this->sendEmail = true; // Reiniciar la variable para el siguiente cliente
        }
    }

    public function getRecommendedProduct(array $data)
    {
        switch ((int)$data['type']) {
            case 1:
                switch ($data['desired_weight']) {
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
                }
                break;
            case 2:
                $size = 1; //Cats by default
                break;
        }

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
        } elseif ((int)$data['feeding'] === 3 && (int) $data['type'] === 2) { //Recommend barf for cats
            $recommended = self::DEFAULT_RECOMMENDED_BARF_PRODUCT_CAT;
        } elseif ((int) $data['type'] === 2) { //Cat default menu
            $recommended = self::DEFAULT_RECOMMENDED_PRODUCT_CAT;
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
        }

        $product = new Product((int)$recommended, false, (int)Context::getContext()->language->id);

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

    private function getUTMParameters()
    {
        switch ($this->emailsendState) {
            case 1:
                return '?utm_source=email&utm_medium=nclient&utm_campaign=wkflw_nclient&utm_content=mail1_oyehumano';
            case 2:
                return '?utm_source=email&utm_medium=nclient&utm_campaign=wkflw_nclient&utm_content=mail2_resolverdudas';
            case 3:
                return '?utm_source=email&utm_medium=nclient&utm_campaign=wkflw_nclient&utm_content=mail3_variedadmenu';
            case 4:
                return '?utm_source=email&utm_medium=nclient&utm_campaign=wkflw_nclient&utm_content=mail4_expertosgarantia';
            case 5:
                return '?utm_source=email&utm_medium=nclient&utm_campaign=wkflw_nclient&utm_content=mail5_rescatarmenu';
            default:
                return '';
        }
    }

    private function getEmailSubject()
    {
        switch ($this->emailsendState) {
            case 1:
                return $this->newPetData['name'] . ' puede comer mejor. Y tú pagar menos.';
            case 2:
                return 'Darle lo mejor es más fácil de lo que crees';
            case 3:
                return '¿Buscas descuentos? ¡Tenemos algo mejor!';
            case 4:
                return '¿Tienes dudas? Aquí no mordemos...';
            case 5:
                return '¡No abandones a ' . $this->newPetData['name'] . '!';
            default:
                return '';
        }
    }
}

$cron = new CrmAccountValidationCron();
$cron->init();
