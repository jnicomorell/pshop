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
<li>

	<a href="{$profileadvshopperaccount_url|escape:'htmlall':'UTF-8'}?action=show-list"
	   title="{l s='User Profile Advanced' mod='profileadv'}" rel="nofollow">
        <img class="icon" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/logo.gif" />
	   {l s='User Profile Advanced' mod='profileadv'}

	</a>
</li>
<!-- MODULE User Profile Advanced -->
{/if}
