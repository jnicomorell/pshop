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

{if count($profileadvcustomers)>0}
{foreach from=$profileadvcustomers item=customer name=myLoop}
					{if $smarty.foreach.myLoop.index%4 == 0}
						<ul>
					{/if}
							<li>
								<a href="{$profileadvshopper_url|escape:'htmlall':'UTF-8'}{$customer.id_customer|escape:'htmlall':'UTF-8'}"
								   title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}">
									<img height="75" width="75" alt="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
										 title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
										 src="{$customer.avatar_thumb|escape:'htmlall':'UTF-8'}"
										 {if $customer.exist_avatar == 0}class="profile-adv-user-img"{/if}>
								</a>
								<div class="b-name">
									<a href="{$profileadvshopper_url|escape:'htmlall':'UTF-8'}{$customer.id_customer|escape:'htmlall':'UTF-8'}"
									   title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
									   >{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}</a>
								</div>
								<div class="b-from">
								{if $customer.country}
									{$customer.country|escape:'htmlall':'UTF-8'}
								{/if}
								</div>
							</li>
							
					
					{if $smarty.foreach.myLoop.last}
						</ul>
						<div class="clr"><!-- --></div>
					{/if}
{/foreach}
{else}
<div style="text-align:center;padding: 0px 20px 20px 20px;">{l s='Shoppers not found' mod='profileadv'}</div>

{/if}