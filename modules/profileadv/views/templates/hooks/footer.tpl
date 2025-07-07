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

{if $profileadvpadv_footer == 1}

{if $profileadvis17 == 1}
    <div class="clear"></div>
{/if}


        {if $profileadvis17 == 1}
            <div class="col-xs-12 col-sm-3 wrapper links">
        {else}
            <section class="profileadv_block_footer footer-block col-xs-12 col-sm-3">
        {/if}


		<h4 {if $profileadvis17 == 1}class="h3 hidden-sm-down"{/if}>
			<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}"
			   title="{l s='Shoppers' mod='profileadv'}"
				>{l s='Shoppers' mod='profileadv'}</a>
		</h4>

    {if $profileadvis17 == 1}
        <div data-toggle="collapse" data-target="#profileadv_block_footer17" class="title clearfix hidden-md-up">
            <span class="h3">{l s='Shoppers' mod='profileadv'}</span>
                        <span class="pull-xs-right">
                          <span class="navbar-toggler collapse-icons">
                            <i class="material-icons add">&#xE313;</i>
                            <i class="material-icons remove">&#xE316;</i>
                          </span>
                        </span>
        </div>
    {/if}

		<div class="block_content block-items-data toggle-footer {if $profileadvis17 == 1}collapse{/if}" {if $profileadvis17 == 1}id="profileadv_block_footer17"{/if}>
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
	    	<div class="shoppers-block-view-all">
	    		<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}" title="{l s='View all shoppers' mod='profileadv'}" class="exclusive_large {if $profileadvis17 == 1}button-small-profileadv{/if}">
	    		   	   	 {l s='View all shoppers' mod='profileadv'}
	    		   	   </a>
	    	</div>
		</div>
        {if $profileadvis17 == 1}
            </div>
        {else}
            </section>
        {/if}

{/if}


{literal}
<script type="text/javascript">

{/literal}{if $profileadvislogged != 0}{literal}
document.addEventListener("DOMContentLoaded", function(event) {
$('document').ready( function() {
	var count1 = Math.random();
	var ph =  '<img style="margin-left:5px; {/literal}{if !$profileadvexist_avatar}{literal} border:1px solid #C4C4C4; {/literal}{/if}{literal}"'+
					'height="20" src="{/literal}{$profileadvavatar_thumb|escape:'htmlall':'UTF-8'}?re=' + count1+'{literal}"'+
					'id="profile-adv-user-img-small" />';



    if($('#header_user_info span'))
        $('#header_user_info span').append(ph);

    // for PS 1.6 >
    if($('.header_user_info')){
        $('.header_user_info .account').append(ph);

    }

    // for ps 1.7
    if($('.user-info')) {
        $('.user-info:last').append(ph);
    }




});
});
{/literal}{/if}{literal}	
</script>
{/literal}