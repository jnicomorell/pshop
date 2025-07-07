<?php
require_once(_PS_MODULE_DIR_ . 'profileadv/controllers/front/ajaxprofileadv.php');
require_once(_PS_MODULE_DIR_ . 'profileadv/controllers/front/ProfileadvFrontController.php');
include_once(_PS_MODULE_DIR_ . 'profileadv/classes/profileadvanced.class.php');
include_once(_PS_MODULE_DIR_ . 'profileadv/classes/AgeCalculator.php');
include_once(_PS_MODULE_DIR_ . 'profileadv/profileadv.php');

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
class ProfileadvAddFirstpetModuleFrontController extends ProfileadvFrontController
{
    public $auth = false;
    public $guestAllowed = true;
    public $ssl = true;

    private $showdata;

    public $translationList;

    public function init()
    {
        $this->showdata = pSQL(isset($_GET['showdata'])) ? pSQL($_GET['showdata']) : false;
        $this->addCustomInputFileAssets = true;
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

        $name_module = 'profileadv';

        $cookie = Context::getContext()->cookie;
        $is_logged = isset($cookie->id_customer) ? $cookie->id_customer : false;
        if ($is_logged) {
            header('location:' . $this->context->link->getModuleLink('profileadv', 'addpet'));
        }

        $obj = new profileAdvanced();
        if (isset($this->showdata) && $this->showdata === "1") {

            $getPetData = $obj->getPetDataFromReference(pSQL($_GET['reference']), false);
            $customer = new Customer($getPetData['id_customer']);
            $petData = array(
                "reference" => $getPetData['reference'],
                "img" => $getPetData['avatar_thumb'],
                "type" => $getPetData['type'],
                "name" => $getPetData['name'],
                "genre" => $getPetData['genre'],
                "birth" => $getPetData['birth'],
                "age" => AgeCalculator::calculateAgeInMonths($getPetData['birth'], true),
                "breed" => $getPetData['breed'],
                "esterilized" => $getPetData['esterilized'],
                "weight" => $getPetData['weight'],
                "feeding" => $getPetData['feeding'],
                "activity" => $getPetData['activity'],
                "physical_condition" => $getPetData['physical_condition'],
                "pathology" => json_decode(html_entity_decode($getPetData['pathology'])),
                "allergies" => json_decode(html_entity_decode($getPetData['allergies'])),
                "comment" => $getPetData['comment'],
                "amount" => $getPetData['amount'],
                "amount_blocked" => $getPetData['is_amount_blocked'],
                "id_customer" => validate::isLoadedObject($customer) ? $customer->id : $getPetData['id_customer'],
                "customer_email" => validate::isLoadedObject($customer) ? $customer->email : $getPetData['message'],
            );
            //Get correct pet breed input data
            $petBreed = "";

            switch ($petData['type']) {
                case '1':
                    $petBreed = "dog";
                    break;
                case '2':
                    $petBreed = "cat";
                    break;
            }

            //Add translated strings
            $petData['type'] = $this->findTranslatedDataByParameters('type', $petData['type']);
            $petData['genre'] = $this->findTranslatedDataByParameters('genre', $petData['genre']);
            $petData['physical_condition'] = $this->findTranslatedDataByParameters('physical-condition', $petData['physical_condition']);
            $petData['esterilized'] = $this->findTranslatedDataByParameters('esterilized', $petData['esterilized']);
            $petData['breed'] = $this->findTranslatedDataByParameters($petBreed, $petData['breed']);
            $petData['activity'] = $this->findTranslatedDataByParameters('activity', $petData['activity']);
            $petData['feeding'] = $this->findTranslatedDataByParameters('feeding', $petData['feeding']);
            for ($i = 0; $i < count($petData['pathology']); $i++) {
                $petData['pathology'][$i] = $this->findTranslatedDataByParameters('pathologies', $petData['pathology'][$i]);
            }
            for ($i = 0; $i < count($petData['allergies']); $i++) {
                $petData['allergies'][$i] = $this->findTranslatedDataByParameters('allergies', $petData['allergies'][$i]);
            }
            $this->context->smarty->assign($name_module . 'newpetdata', $petData);

            //Caroussel products
            $product_list = Product::getProducts((int)Context::getContext()->language->id, 0, (10), 'id_product', 'ASC', 90);

            $link = new Link();

            for ($i = 0; $i < count($product_list); $i++) {

                $product = new Product((int)$product_list[$i]['id_product']);

                if (Validate::isLoadedObject($product)) {

                    // Get Product URL
                    $product_list[$i]['link'] = $link->getProductLink($product);

                    $img = $product->getCover($product->id);
                    $product_list[$i]['image'] =  $link->getImageLink((string)$product->link_rewrite[(int)Context::getContext()->language->id], (int)$img['id_image'], 'home_default');
                }
            }

            $profile = new ProfileadvAjaxprofileadvModuleFrontController();

            $product_recommend =  $profile->getRecommendedProduct($getPetData);

            $this->context->smarty->assign($name_module . 'product_recommend', $product_recommend);
            $this->context->smarty->assign($name_module . 'product_list', $product_list);
        } else {

            $currentDate = date('Y-m-d');
            $maxOldDate = date("Y-m-d", strtotime("-25 year"));

            $this->assignTranslations($this->translationList, $name_module);
            $this->context->smarty->assign($name_module . 'currentdate', $currentDate);
            $this->context->smarty->assign($name_module . 'maxolddate', $maxOldDate);

            $obj_profileadv = new profileadv();
            $_data_translate = $obj_profileadv->translateItems();

            $obj_profileadv->setSEOUrls();

            $data_urls = $obj->getSEOURLs();
            $my_account = $data_urls['pet_list'];
            $this->assignBrowserDetection();


            $this->context->smarty->assign($name_module . 'is16', 1);


            if (version_compare(_PS_VERSION_, '1.7', '>')) {
                $this->context->smarty->tpl_vars['page']->value['meta']['title'] = $_data_translate['meta_title_myaccount'];
                $this->context->smarty->tpl_vars['page']->value['meta']['description'] = $_data_translate['meta_description_myaccount'];
                $this->context->smarty->tpl_vars['page']->value['meta']['keywords'] = $_data_translate['meta_keywords_myaccount'];
            }

            $this->context->smarty->assign('meta_title', $_data_translate['meta_title_myaccount']);
            $this->context->smarty->assign('meta_description', $_data_translate['meta_description_myaccount']);
            $this->context->smarty->assign('meta_keywords', $_data_translate['meta_keywords_myaccount']);

            $this->context->smarty->assign(array(
                $name_module . 'my_account' => $my_account
            ));
        }

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:' . $name_module . '/views/templates/front/addfirstpet.tpl');
        }
    }

}
