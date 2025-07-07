<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class whatsappApi
{

    private $action, $api_token, $instance, $token, $employee;

    public function __construct()
    {

        $this->token = 'y42eotpy543a';
        if (!$_POST['token'] || $_POST['token'] !== $this->token) {
            die;
        }

        $this->action = $_POST['action'];

        $this->employee = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : false;

        if ($this->employee === 22) { //Marta
            $this->api_token = 'u243kcsoljvsgms7';
            $this->instance = 'instance45019';
        } elseif ($this->employee === 23) { //Maria
            $this->api_token = 'y7wkte1ntf6er4ef';
            $this->instance = 'instance83333';
        } elseif ($this->employee === 24) { //Marina
            $this->api_token = 'u243kcsoljvsgms7';
            $this->instance = 'instance45019';
        } elseif ($this->employee === 28) { //Laia
            $this->api_token = '0ksf69haqvtb2q2f';
            $this->instance = 'instance44166';
        } elseif ($this->employee === 29) { //Meritxell
            $this->api_token = '0ksf69haqvtb2q2f';
            $this->instance = 'instance44166';
        } elseif ($this->employee === 37) { //Lola
            $this->api_token = 'u243kcsoljvsgms7';
            $this->instance = 'instance45019';
        } elseif ($this->employee === 26) {
            $this->api_token = '0ksf69haqvtb2q2f';
            $this->instance = 'instance44166';
        } elseif ($this->employee === 1) {
            $this->api_token = '0ksf69haqvtb2q2f';
            $this->instance = 'instance44166';
        }

        $this->init();
    }

    private function init()
    {

        switch ($this->action) {
            case 'send-amount-text':
                $this->sendText();
                break;
            case 'check-whatsapp':
                $this->checkWhatsapp();
                break;
        }
    }

    private function sendText()
    {

        $data = array(
            "customer_phone" => $_POST['customer_phone'],
            "customer_name" => $_POST['customer_name'],
            "customer_message" => $this->getPredefinedMessage($_POST),
            "employee_name" => $_POST['employee_name'],
            "employee_id" => (int)$_POST['employee_id'],
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/" . $this->instance . "/messages/chat",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{
                                    "token": "' . $this->api_token . '",
                                    "to": "' . $data['customer_phone'] . '",
                                    "body": "' . $data['customer_message'] . '"
                                }',
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }

    /**
     * Check if number is a valid WhatsApp contact
     *
     * @return void
     */
    private function checkWhatsapp()
    {

        $data = array(
            "customer_phone" => (string) $_POST['customer_phone'],
        );

        $params = array(
            'token' => $this->api_token,
            'chatId' => str_replace("+", "", $data['customer_phone']),
            'nocache' => ''
        );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/" . $this->instance . "/contacts/check?" . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }

    private function getPredefinedMessage(array $data)
    {
        switch ($data['customer_predefined_message']) {
            case 1:
                if ($data['pet_prev_amount'] > 0) {
                    return '¡Hola ' . $data['customer_name'] . '! Soy ' . $data['employee_name'] . ' de Guau&Cat. LA CANTIDAD DIARIA DE TU PELUDO HA CAMBIADO 💙 Te informamos que, a partir de ahora, a ' . $data['pet'] . ' le tocará una cantidad de *' . $data['pet_current_amount'] . ' g/día* (hasta ahora comía unos ' . $data['pet_prev_amount'] . ' g/día). Si tienes cualquier pregunta, estamos a tu disposición. Para revisar los datos de tu mascota, puedes acceder al siguiente enlace --> https://guauandcat.com/calculadora';
                } else {
                    return '¡Hola ' . $data['customer_name'] . '! Soy ' . $data['employee_name'] . ' de Guau&Cat. LA CANTIDAD DIARIA DE TU PELUDO HA CAMBIADO 💙 Te informamos que, a partir de ahora, a ' . $data['pet'] . ' le tocará una cantidad de *' . $data['pet_current_amount'] . ' g/día*. Si tienes cualquier pregunta, estamos a tu disposición. Para revisar los datos de tu mascota, puedes acceder al siguiente enlace --> https://guauandcat.com/calculadora';
                }
        }
    }
}

$api = new whatsappApi();
