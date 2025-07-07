<?php

use PrestaShop\PrestaShop\Adapter\Validate;

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

class ValidateData
{
    public $valid = false;

    public function isValidData(array $data)
    {

        //Check weight
        $this->valid = $this->isValidWeight($data['pet-weight']);
        // Check birth data
        $this->valid = $this->isValidBirth($data['pet-birth']);

        return $this->valid;
    }

    private function isValidWeight(float $weight)
    {

        $min_weight = 0.01;
        $max_weight = 90;

        if ($weight >= $min_weight && $weight <= $max_weight) {
            return true;
        }

        return false;
    }

    private function isValidBirth(string $date)
    {

        //Max 25 years for a pet
        $maxOldDate = date("Y-m-d", strtotime("-25 year"));

        if (Validate::isDate($date) &&  $date >= $maxOldDate) {
            return true;
        }

        return false;
    }
}
