<?php
require_once _PS_MODULE_DIR_ . 'profileadv/controllers/AgeCalculator.php';

class calculateAmount
{
    public $auth = true;
    public $guestAllowed = false;
    public $ssl = true;
    private $translationList = array(), $petId = false, $source;

    public function __construct($source = 'wizard')
    {
        if ($source !== "wizard") {
            include(dirname(__FILE__) . '/../../config/config.inc.php');
        }

        require_once _PS_MODULE_DIR_ . 'profileadv/classes/TranslationManager.php';
        $iso = Context::getContext()->language ? Context::getContext()->language->iso_code : 'es';
        $this->translationList = ProfileadvTranslationManager::getDataTranslations($iso);
        $this->petId = isset($_GET['pet']) ? $_GET['pet'] : false;
        $this->source = $source;
    }

    public function init()
    {
        if ($this->source != "wizard") { // only for direct calls 
            $name_module = 'profileadv';
            require(_PS_MODULE_DIR_ . $name_module . '/classes/profileadvanced.class.php');

            $list = array();
            $pet = new profileAdvanced();


            if (isset($this->petId) && $this->petId) {
                $list = $pet->getPetDataFromId($this->petId);
            } else {
                $list = $pet->getAllPetsData();
            }

            //Convert array index name to "pet-*"
            for ($i = 0; $i < count($list); $i++) {
                $list[$i]['newdata']['pet-type'] = $list[$i]['type'];
                $list[$i]['newdata']['pet-esterilized'] = $list[$i]['esterilized'];
                $list[$i]['newdata']['pet-name'] = $list[$i]['name'];
                $list[$i]['newdata']['pet-genre'] = $list[$i]['genre'];
                $list[$i]['newdata']['pet-birth'] = $list[$i]['birth'];
                $list[$i]['newdata']['pet-breed'] = $list[$i]['breed'];
                $list[$i]['newdata']['pet-weight'] = $list[$i]['weight'];
                $list[$i]['newdata']['pet-desired-weight'] = $list[$i]['desired_weight'];
                $list[$i]['newdata']['pet-feeding'] = $list[$i]['feeding'];
                $list[$i]['newdata']['pet-activity'] = $list[$i]['activity'];
                $list[$i]['newdata']['pet-physical-condition'] = $list[$i]['physical_condition'];
                $list[$i]['newdata']['pet-pathology'] = $list[$i]['pathology'];
                $list[$i]['newdata']['pet-allergies'] = $list[$i]['allergies'];
                $list[$i]['newdata']['pet-amount'] = $list[$i]['amount'];

                $listNew = $this->calculateDailyEatAmount($list);

                if (((int)$listNew['pet-amount'] !== (int)$list[$i]['amount']) && (int)$listNew['pet-amount'] > 0) {
                    $pet->updateDailyAmount($list[$i]['id'], $listNew['pet-amount']);
                }
            }
        }
    }

    public function calculateDailyEatAmount(array $data): array
    {
        $data['age_years'] = AgeCalculator::calculateAgeInYears($data['pet-birth']);
        $data['age_months'] = AgeCalculator::calculateAgeInMonths($data['pet-birth'], true);
        $data = $this->getDailyRate($data);

        $data['pet-amount'] = ($data['dailyrate'] * $data['pet-desired-weight']) * 1000;
        $data['pet-amount'] = $this->roundUpTo($data['pet-amount'], 10);

        return $data;
    }

    private function getDailyRate(array $data): array
    {
        $name_module = 'profileadv';

        $data['dailyrate'] = 0; //Default 

        $petBreedWeight = require(_PS_MODULE_DIR_ . $name_module . '/pet_breed_weight.php');

        $iso_code =  isset($cookie->id_lang) ? Language::getIsoById((int)$cookie->id_lang) : 'es';

        //Retrieve array data filtered by breed
        $breedData = 0;
        for ($i = 0; $i < count($petBreedWeight); $i++) {
            foreach ($petBreedWeight as $index => $pet) {
                //Convert to accessible array from json data
                if (((int)$pet['breed'] === (int)$data['pet-breed']) && ((int)$pet['type'] === (int)$data['pet-type'])) {
                    $breedData = $petBreedWeight[$index];
                    break;
                }
            }
        }

        $type = (int)$data['pet-type'];
        $size = (int)$breedData['size']; //All cats will be size 1 by default


        /* 2025-02-12 --> Se modifican los tamaños dependiendo del peso*/
        $desired_weight = floatval($data['pet-desired-weight']);

        $reserved_breeds = [47, 125, 126, 127, 128, 129, 274, 275, 276, 333, 353]; //Galgos y Podencos
        if (in_array($data['pet-breed'], $reserved_breeds, true)) {
            $size = 6;
        } else {
            switch ((int)$type) {
                case 1:
                    switch ($desired_weight) {
                        case ($desired_weight < 5):
                            $size = 1;
                            break;
                        case ($desired_weight >= 5 && $desired_weight < 14):
                            $size = 2;
                            break;
                        case ($desired_weight >= 14 && $desired_weight < 25):
                            $size = 3;
                            break;
                        case ($desired_weight >= 25 && $desired_weight < 50):
                            $size = 4;
                            break;
                        case ($desired_weight > 50):
                            $size = 5;
                            break;
                    }
                    break;
                case 2:
                    $size = 1; //Cats by default
                    break;
            }
        }

        $age = (int)$data['age_months'];
        switch (true) {
            case ($age >= 13 && $age < 120) && $type === 1: //PERROS ADULTOS
                $age = 13;
                break;
            case $age >= 120 && $type === 1: //PERROS SÉNIOR
                $age = 14;
                break;
            case $age >= 6 && $type === 2: //GATOS ADULTOS Y SÉNIOR
                $age = 7;
                break;
            default:
                $age = (int)$data['age_months'];
                break;
        }

        $pathology = array();
        $allergies = array();
        $esterilized = (int)$data['pet-esterilized'];
        $activity = (int)$data['pet-activity'];
        $physical_condition = (int)$data['pet-physical-condition'];

        //Get pathologies
        foreach ($data['pet-pathology'] as $pathol) {
            array_push($pathology, $pathol);
        }

        //Get allergies
        foreach ($data['pet-allergies'] as $aller) {
            array_push($allergies, $aller);
        }

        //2023-11-06 -> Confirmado con Jordi que, por ahora, no se hacen excepciones para las patologías
        /*if (in_array("3", $pathology) || in_array("4", $pathology) || in_array("5", $pathology)) { 
            $data['text'] = htmlentities($this->translationList['dailyratio']['messages'][$iso_code][2]);
            $data['reason'] = 3;
        } else*/
        if ($age < 3) { //CACHORROS
            $data['text'] = htmlentities($this->translationList['dailyratio']['messages'][$iso_code]['3']);
            $data['reason'] = 2;
        } elseif ((int)$size === 5) { //GIGANTES
            $data['text'] = htmlentities($this->translationList['dailyratio']['messages'][$iso_code]['1']);
            $data['reason'] = 1;
        } else {

            require_once _PS_MODULE_DIR_.'profileadv/classes/MenuConstants.php';
            $daily_ratios = require(_PS_MODULE_DIR_ . $name_module . '/pet_daily_ratios.php');
            $conditionData = $daily_ratios['type'][$type]['age'][$age]['size'][$size]['activity'][$activity]['physical_condition'][$physical_condition];
            $data['dailyrate'] = (float) $conditionData['ratio'];
            $data['recommended_menu'] = $conditionData['menu'];
        }

        return $data;
    }

    private function roundUpTo($number, $increments)
    {
        $increments = 1 / $increments;
        return (ceil($number * $increments) / $increments);
    }

}

$pet = new calculateAmount();
$pet->init();
