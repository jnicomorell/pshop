<?php
require_once _PS_MODULE_DIR_.'profileadv/controllers/front/addpet.php';

class ProfileadvAddFirstpetModuleFrontController extends ProfileadvAddpetModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        // Force guest access and redirect logged in users to the regular addpet controller
        $this->forceGuest = true;
        $this->redirectLogged = true;
    }
}
