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

{if $profileadvpadv_right == 1}

<div id="profileadv_block_right" class="block  {if $profileadvis17 == 1}padding-17 block-categories hidden-sm-down{/if} {if $profileadvis16 == 1}blockmanufacturer16{else}blockmanufacturer{/if}">
		<h4  class="title_block {if $profileadvis17 == 1}text-uppercase{/if}" {if $profileadvis16 != 1}align="center"{/if}>
			<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}"
			   title="View all shoppers"
				>{l s='Shoppers' mod='profileadv'}</a>
		</h4>
		<div class="block_content">
			{if count($profileadvcustomers_block)>0}
			<ul class="shoppers-block-items">
			{foreach from=$profileadvcustomers_block item=customer name=myLoop}
	    		<li>
	    			<img src="{$customer.avatar_thumb|escape:'htmlall':'UTF-8'}"
						{if $customer.exist_avatar == 0}class="profile-adv-user-img"{/if}
	    		   	   		  title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
	    		   	   		  alt = "{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}" />
	    			<a href="{$profileadvshopper_url|escape:'htmlall':'UTF-8'}{$customer.id_customer|escape:'htmlall':'UTF-8'}"
	    		   	   title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}">
	    		   	   	 {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}
	    		   	   </a>
	    		   	 <div class="clr"></div>
	    		</li>
	    	{/foreach}
	    	</ul>
	    	{else}
	    		<div class="padding-10">
					{l s='There are not Shoppers yet.' mod='profileadv'}
				</div>
	    	{/if}
	    	<div class="shoppers-block-view-all {if $profileadvis17 == 1}button-small-profileadv{/if}">
	    		<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}" title="{l s='View all shoppers' mod='profileadv'}" class="exclusive_large">
	    		   	   	 {l s='View all shoppers' mod='profileadv'}
	    		   	   </a>
	    	</div>
		</div>
</div>

{/if}