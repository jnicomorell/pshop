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

{if $profileadvpadv_home == 1}

<div id="left_column">
<div id="profileadv_block_left" class="block {if $profileadvis17 == 1}block-categories{/if} blockmanufacturer16">
		<h4  class="title_block {if $profileadvis17 == 1}text-uppercase h6{/if}">
			<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}"
			   >{l s='Shoppers' mod='profileadv'}</a>
		</h4>
		<div class="block_content">
			{if count($profileadvcustomers_block)>0}
			<ul class="shoppers-block-items home-shoppers">
			{foreach from=$profileadvcustomers_block item=customer name=myLoop}
	    		<li class="float-left border-bottom-none">
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
                <div class="clr"></div>
	    	{else}
	    		<div class="padding-10">
					{l s='There are not Shoppers yet.' mod='profileadv'}
				</div>
	    	{/if}
	    	
		</div>
</div>
</div>
{/if}