<?php

require_once dirname(__file__) . '/../../../../config/config.inc.php';

class profileAdvAjaxController
{

    private $action;
    private $params;

    public function __construct()
    {
        $this->params = $_POST;
        $this->action = $this->params['action'];

        $this->init();
    }

    private function init()
    {
        switch ((int) $this->action) {
            case 1:
                $this->ajaxProcessUpdatePetCustomerSendedEmail();
                break;
        }
    }

    private function ajaxProcessUpdatePetCustomerSendedEmail(): void
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET sended_email = ' . pSQL($this->params['email_sended']) . ' WHERE reference = "' . pSQL($this->params['reference']) . '"';

        $result = Db::getInstance()->execute($sql) ? true : false;
        echo $result;
    }
}

$init = new profileAdvAjaxController();
