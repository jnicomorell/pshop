{*
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
*}

{if $profileadvid_customer != 0}
    <!-- MODULE User Profile Advanced -->
    <a rel="nofollow" href="{$profileadvpetlist_url|escape:'htmlall':'UTF-8'}?action=show-list"
        {if $profileadvis17 == 1}class="col-lg-4 col-md-6 col-sm-6 col-xs-12" {/if}
        title="{l s='User Profile Advanced' mod='profileadv'}">
        <span class="link-item">
            <i class="fa-solid fa-dog" style="padding-bottom: 1rem;"></i>
            <span>{l s='Mis mascotas' mod='profileadv'}</span>
        </span>
    </a>
    <!-- MODULE User Profile Advanced -->
{/if}