<?php
require_once _PS_MODULE_DIR_.'profileadv/controllers/front/addpet.php';

class ProfileadvAddFirstpetModuleFrontController extends ProfileadvAddpetModuleFrontController
{
    public function init()
    {
        parent::init();
        // Guests are allowed on this controller
        $this->auth = false;
        $this->guestAllowed = true;
    }

    public function initContent()
    {
        if (Context::getContext()->customer->isLogged()) {
            Tools::redirect($this->context->link->getModuleLink('profileadv', 'addpet'));
        }

        parent::initContent();
    }
}
