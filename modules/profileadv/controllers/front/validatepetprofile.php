<?php

include_once(_PS_MODULE_DIR_ . "profileadv/classes/profileadvanced.class.php");

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
class ProfileadvValidatePetProfileModuleFrontController extends ModuleFrontController
{
    private $pet_reference;
    private $validate_token;
    private $profile;

    private $validated;
    private $is_guest = false;
    private $customer;

    public function init()
    {
        $this->pet_reference = Tools::getValue('reference') ? pSQL(Tools::getValue('reference')) : false;
        $this->validate_token = Tools::getValue('token') ? pSQL(Tools::getValue('token')) : false;

        $this->profile = new profileAdvanced();

        if (!$this->pet_reference || !$this->validate_token) {
            Tools::redirect('/');
        }

        parent::init();
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->validated = false;

        if ($this->verifyPetValidation()) {
            //Check if is guest or customer
            if (!$this->getIdCustomerFromPets()) {
                //If is a guest, pet profile will not be validated until has been registered as customer
                $this->is_guest = true;
            } else if ($this->profile->customerIdExists($this->getIdCustomerFromPets())) {
                $this->customer = $this->getIdCustomerFromPets();
                $this->validated = $this->validatePetProfile();
            }
        } else {
            //If reference and token are not correct
            Tools::redirect('index.php?controller=my-account');
        }

        $this->context->smarty->assign(
            array(
                'validated' => $this->validated,
                'is_guest' => $this->is_guest,
                'pet_data' => $this->retrievePetDataFromReference()
            )
        );

        $this->setTemplate('module:profileadv/views/templates/front/validation.tpl');
    }

    private function verifyPetValidation(): bool
    {
        return $this->profile->checkValidationTokenFromReference($this->pet_reference, $this->validate_token);
    }

    private function validatePetProfile(): bool
    {
        return $this->profile->validatePetFromReference($this->pet_reference, $this->customer);
    }

    private function getIdCustomerFromPets(): mixed
    {
        return $this->profile->getIdCustomerFromPetReference($this->pet_reference);
    }

    private function retrievePetDataFromReference(): mixed
    {
        return $this->profile->getPetDataFromReference($this->pet_reference);
    }

    public function validatePetProfileAfterResgistration(int $id_customer){
        return $this->profile->getListPetDataFromCustomerEmailOnMessage($id_customer);
    }
}
