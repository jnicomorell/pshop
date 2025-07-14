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

class AdminProfileAdvListController extends ModuleAdminController
{
    public $name;
    public $_helperlist;

    public function __construct()
    {
        $this->name = 'profileadv';
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $this->translationList = require_once(_PS_MODULE_DIR_ . '/profileadv/translations/translations.php');

        parent::initContent();
    }


    public function renderList()
    {
        include_once(_PS_MODULE_DIR_ . $this->name . "/classes/profileadvanced.class.php");

        $obj = new profileAdvanced();
        if (Tools::getValue('petName')) {
            $pets = $obj->getListPetDataFromName(Tools::getValue('petName'));
        } elseif (Tools::getValue('customerEmail')) {
            $pets = $obj->getListPetDataFromCustomerEmail(Tools::getValue('customerEmail'));
        } elseif (Tools::isSubmit('noAmountData')) {
            $pets = $obj->getListPetDataWithoutAmount();
        } elseif (Tools::isSubmit('noCustomerData')) {
            $pets = $obj->getPetListToValidate();
        } else {
            $pets = $obj->getInitialListPetData();
        }

        //Get pets with no amount
        $petsWAmount = count($obj->getListPetDataWithoutAmount());
        //Retreive pets without customer account associated
        $petsWCustomer = count($obj->getPetListToValidate());
        $showPetsWCustomer = false;

        if (!Tools::isSubmit('noCustomerData')) { //Only for pets with registered customers
            for ($i = 0; $i < count($pets); $i++) {
                $customer = new Customer((int)$pets[$i]['id_customer']);
                $pets[$i]['customer'] = $customer->firstname . " " . $customer->lastname;
                $pets[$i]['customer_href'] = Context::getContext()->link->getAdminLink('AdminCustomers', true, ['id_customer' => (int)$customer->id, 'viewcustomer' => '']);
            }
        } else {
            for ($i = 0; $i < count($pets); $i++) {
                $pets[$i]['pet_href'] = $this->context->link->getModuleLink('profileadv', 'addpet', array('reference' => $pets[$i]['reference'], 'showdata' => 1));
            }
            $showPetsWCustomer = true;
        }
        $this->context->smarty->assign('pets', $pets);
        $this->context->smarty->assign('petsWAmount', $petsWAmount);
        $this->context->smarty->assign('showPetsWCustomer', $showPetsWCustomer);
        $this->context->smarty->assign('petsWCustomer', $petsWCustomer);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'profileadv/views/templates/admin/back_list.tpl');
    }
}
