<?php

require_once(_PS_MODULE_DIR_ . 'profileadv/controllers/front/ProfileadvFrontController.php');

use PrestaShop\PrestaShop\Adapter\Tools;

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
class ProfileadvPetlistModuleFrontController extends ProfileadvFrontController
{
    public $auth = true;
    public $guestAllowed = false;
    public $ssl = true;

    public $name_module = 'profileadv';
    public $action = false;
    public $actionResult = false;
    public $translationList;

    public function init()
    {
        $this->action = isset($_GET["action"]) ? pSQL($_GET["action"]) : $this->action;
        $this->actionResult = isset($this->action) ? $this->invokeAction(isset($_GET["action"]) ? pSQL($_GET["action"]) : $this->action) : false;

        $this->addProfileadvJs = true;
        $this->loadTranslations();

        parent::init();
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cookie = Context::getContext()->cookie;
        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : 0;
        if (!$is_logged)
            Tools::redirect('authentication.php');


        $this->name_module = 'profileadv';

        $petList = $this->getPetListFromCustomer();

        if (count($petList) > 0) {

            for ($i = 0; $i < count($petList); $i++) {
                //Get correct pet breed input data
                $petBreed = "";

                switch ($petList[$i]['type']) {
                    case '1':
                        $petBreed = "dog";
                        break;
                    case '2':
                        $petBreed = "cat";
                        break;
                }

                //Add translated strings
                $petList[$i]['type'] = $this->findTranslatedDataByParameters('type', $petList[$i]['type']);
                $petList[$i]['genre'] = $this->findTranslatedDataByParameters('genre', $petList[$i]['genre']);
                $petList[$i]['physical_condition'] = $this->findTranslatedDataByParameters('physical-condition', $petList[$i]['physical_condition']);
                $petList[$i]['esterilized'] = $this->findTranslatedDataByParameters('esterilized', $petList[$i]['esterilized']);
                $petList[$i]['breed'] = $this->findTranslatedDataByParameters($petBreed, $petList[$i]['breed']);
                $petList[$i]['activity'] = $this->findTranslatedDataByParameters('activity', $petList[$i]['activity']);
                $petList[$i]['feeding'] = $this->findTranslatedDataByParameters('feeding', $petList[$i]['feeding']);
                for ($x = 0; $x < count($petList[$i]['pathology']); $x++) {
                    $petList[$i]['pathology'][$x] = $this->findTranslatedDataByParameters('pathologies', $petList[$i]['pathology'][$x]);
                }
                for ($z = 0; $z < count($petList[$i]['allergies']); $z++) {
                    $petList[$i]['allergies'][$z] = $this->findTranslatedDataByParameters('allergies', $petList[$i]['allergies'][$z]);
                }
            }
        }
        $this->context->smarty->assign($this->name_module . 'petlist', $petList);

        $editPetUrl = "shopperaccount";
        $this->context->smarty->assign($this->name_module . 'editpeturl', $editPetUrl);

        $deletePetUrl = "calculadora?action=delete&";
        $this->context->smarty->assign($this->name_module . 'deletepeturl', $deletePetUrl);

        $editPetUrl = "update";
        $editPetUrl = $this->context->link->getModuleLink('profileadv', 'editpet');
        $this->context->smarty->assign($this->name_module . 'editpeturl', $editPetUrl);

        $addNewPet = $this->context->link->getModuleLink('profileadv', 'addpet');
        $this->context->smarty->assign($this->name_module . 'newpet', $addNewPet);

        if ($this->action) {
            $this->context->smarty->assign($this->name_module . 'action', $this->action);
            $this->context->smarty->assign($this->name_module . 'actionresult', $this->actionResult);
        }

        $this->setTemplate('module:' . $this->name_module . '/views/templates/front/petlist.tpl');
    }

    public function getPetListFromCustomer()
    {
        $this->name_module = 'profileadv';

        include_once(_PS_MODULE_DIR_ . $this->name_module . '/classes/petlist.class.php');

        $petList = new petList();

        $petList = $petList->getPetsListFromCustomer((int)$this->context->cookie->id_customer);

        return $petList['pets'];
    }

    private function invokeAction($action = false)
    {

        include_once(_PS_MODULE_DIR_ . $this->name_module . '/classes/petlist.class.php');
        $petList = new petList();

        switch ($action) {
            case 'delete':
                $deleted = false;

                if (isset($_POST['source']) && $_POST['source'] === "back") {
                    $deleted = isset($_POST['reference']) ? $petList->deletePet(array('id_customer' => (int)$_POST['id_customer'], 'ref_pet' => $_POST['reference'])) : false;
                } else {

                    $deleted = isset($_GET['reference']) ? $petList->deletePet(array('id_customer' => (int)$this->context->cookie->id_customer, 'ref_pet' => $_GET['reference'])) : false;
                }
                return $deleted;
                break;
        }
    }

    public function setMedia()
    {
        parent::setMedia();
    }
}
