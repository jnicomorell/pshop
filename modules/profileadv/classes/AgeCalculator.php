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

class AgeCalculator
{
    /**
     * Calculate age in years from a birth date
     *
     * @param string $birth
     *
     * @return int
     */
    public static function calculateAgeInYears(string $birth): int
    {
        $birth = new DateTime(date('Y/m/d', strtotime($birth)));
        $now = new DateTime(date('Y/m/d', time()));
        $interval = $now->diff($birth);
        return $interval->y;
    }

    /**
     * Calculate age in months. When $total is true the result
     * will be the total amount of months from birth.
     * Otherwise only the remaining months within the year
     * are returned.
     *
     * @param string $birth
     * @param bool   $total
     *
     * @return int
     */
    public static function calculateAgeInMonths(string $birth, bool $total = false): int
    {
        $birth = new DateTime(date('Y/m/d', strtotime($birth)));
        $now = new DateTime(date('Y/m/d', time()));
        $interval = $now->diff($birth);
        if ($total) {
            return ($interval->y * 12) + $interval->m;
        }
        return $interval->m;
    }
}
