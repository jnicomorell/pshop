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

include_once(_PS_MODULE_DIR_ . 'profileadv/controllers/AgeCalculator.php');

class petList extends Module
{
    const DEFAULT_DOG_IMG = '527785806447bde994fa8.jpg';
    const DEFAULT_CAT_IMG = '1519567836646631bfccd61.jpg';

    public function __construct()
    {
        parent::__construct();

        $this->_name = "profileadv";

        $this->_step = (int)Configuration::get($this->_name . 'perpage_shoppers');

        //$this->_http_host = Tools::getShopDomainSsl(true, true) . __BASE_URI__;

        $this->initContext();
    }

    private function initContext()
    {
        $this->context = Context::getContext();
    }

    public function getPetsListFromCustomer($id_customer, $limit = 0)
    {

        $sql = 'SELECT a2c.*
		            FROM `' . _DB_PREFIX_ . 'avatar2customer` a2c
		            WHERE a2c.active = 1 AND a2c.id_customer = ' . (int)$id_customer . '
                    ORDER BY id DESC';
        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        $pets = Db::getInstance()->ExecuteS($sql);
        $i = 0;
        foreach ($pets as $_item_customer) {
            $pets[$i]['img'] = $_item_customer['avatar_thumb'];
            $pets[$i]['reference'] = $_item_customer['reference'];
            $pets[$i]['type'] = json_decode(html_entity_decode($_item_customer['type']));
            $pets[$i]['esterilized'] = json_decode(html_entity_decode($_item_customer['esterilized']));
            $pets[$i]['name'] = $_item_customer['name'];
            $pets[$i]['genre'] = json_decode(html_entity_decode($_item_customer['genre']));
            $pets[$i]['birth'] = $_item_customer['birth'];
            $pets[$i]['breed'] = json_decode(html_entity_decode($_item_customer['breed']));
            $pets[$i]['weight'] = $_item_customer['weight'];
            $pets[$i]['feeding'] = json_decode(html_entity_decode($_item_customer['feeding']));
            $pets[$i]['physical_condition'] = json_decode(html_entity_decode($_item_customer['physical_condition']));
            $pets[$i]['activity'] = json_decode(html_entity_decode($_item_customer['activity']));
            $pets[$i]['pathology'] = json_decode(html_entity_decode($_item_customer['pathology']));
            $pets[$i]['allergies'] = json_decode(html_entity_decode($_item_customer['allergies']));
            $pets[$i]['amount'] = $_item_customer['amount'];
            $pets[$i]['text'] = html_entity_decode($_item_customer['comment']);
            $pets[$i]['active'] = $_item_customer['active'];
            $pets[$i]['ageyears'] = AgeCalculator::calculateAgeInYears($pets[$i]['birth']);
            $pets[$i]['agemonths'] = AgeCalculator::calculateAgeInMonths($pets[$i]['birth']);
            $pets[$i]['edit_url'] = $this->context->link->getAdminLink('Adminprofileadv', false) . '?token=' . Tools::getAdminToken('Adminprofileadv' . intval(Tab::getIdFromClassName('Adminprofileadv')) . intval($this->context->cookie->id_employee)) . '&reference=' . $_item_customer['reference'];
            $i++;
        }

        return array(
            'pets' => $pets
        );
    }

    public function deletePet(array $data)
    {

        $id_customer = $data['id_customer'];
        $ref_pet = $data['ref_pet'];

        //Delete image1
        
        $pet = new profileAdvanced();
        $pet = $pet->getPetDataFromReference($ref_pet, $id_customer);
        $img = isset($pet['avatar_thumb']) ? $pet['avatar_thumb'] : false;

        if ($img && ($img !== self::DEFAULT_DOG_IMG && $img !== self::DEFAULT_CAT_IMG)) { //Avoid delete default image
            unlink(dirname(__FILE__) . '/../../../img/pets/' . $img);
        }

        $query = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer 
            set active = 0                      
        WHERE `id_customer` = ' . (int)$id_customer . ' AND `reference` = "' . pSQL($ref_pet) . '"';

        if (Db::getInstance()->Execute($query)) {
            return true;
        } else {
            return false;
        }
    }

}
