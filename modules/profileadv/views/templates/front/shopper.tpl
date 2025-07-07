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

{foreach from=$profileadvcustomer item=customer name=myLoop}


    {if $profileadvis16 == 1 && $profileadvis17 ==0}
        {capture name=path}<a href="{$profileadvshoppers_url|escape:'htmlall':'UTF-8'}">{l s='All Shoppers' mod='profileadv'}</a>
            <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
            {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}
        {/capture}
     {else}
        {capture name=path}
            {l s='All Shoppers' mod='profileadv'}
        {/capture}
    {/if}










<div class="b-product-item" id="top" >
					<div class="b-photo">
						<div class="block-photo">
							<img title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}" alt="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
								 src="{$customer.avatar_thumb|escape:'htmlall':'UTF-8'}"
								 {if $customer.exist_avatar == 0}class="photo"{else}class="border-none"{/if}>
							<div class="data">
								<div>
									<strong>{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}</strong>
								</div>
								<div class="margin-top-5">
									<i>
									   {if strlen($customer.gender_txt)>0}{$customer.gender_txt|escape:'htmlall':'UTF-8'}{/if}
									   {if $customer.stats.age != "--"}
									   {if strlen($customer.stats.age)>0}{$customer.stats.age|escape:'htmlall':'UTF-8'} {l s='years' mod='profileadv'}{/if}
									   {/if}
									</i>
								</div>
							</div>
						</div>
					</div>
					
					
					
	<div class="b-description">
					
		<h1 class="fn">{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}</h1>
					
		<div class="main-features">{l s='Main Information' mod='profileadv'}</div>


		<div class="data-of-item">
			<b>{l s='Registration date:' mod='profileadv'}</b> {$customer.date_add|escape:'htmlall':'UTF-8'}<br/><br/>
			{if isset($customer.stats.last_visit)}<b>{l s='Last visit:' mod='profileadv'}</b> {$customer.stats.last_visit|escape:'htmlall':'UTF-8'}{/if}
		</div>

		<div class="share-catalog margin-left-0">

			<!-- Place this tag where you want the +1 button to render -->
			<g:plusone size="small" href="http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" count="false"></g:plusone>
			
			<!-- Place this tag after the last plusone tag -->
            {literal}
                <script type="text/javascript">
                    (function() {
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                    })();
                </script>
            {/literal}


                {literal}
                    <script async defer src="//assets.pinterest.com/js/pinit.js"></script>
                {/literal}

                <a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&media={$customer.avatar_thumb|escape:'htmlall':'UTF-8'}&description={$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}"
                   data-pin-shape="round"></a>

                <a href="https://www.facebook.com/share.php?u=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Facebook">
                    <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323398.png" alt="Facebook">
                </a>

				<a href="https://twitter.com/?status={$meta_title|escape:'htmlall':'UTF-8'} : http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Twitter">
				       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323517.png" alt="Twitter">
				</a>

				
				<a href="https://www.google.com/bookmarks/mark?op=add&amp;bkmk=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Google Bookmarks">
				      <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1293027456.png" alt="Google Bookmarks">
				</a>

                {*
				<a href="http{if $profileadvis_ssl == 1}s{/if}://bookmarks.yahoo.com/toolbar/savebm?opener=tb&amp;u=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;t={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Yahoo! Bookmarks">
				       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1293094139.png" alt="Yahoo! Bookmarks">
				</a>
                *}
				<a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="LinkedIn">
				       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323420.png" alt="LinkedIn">
				</a>

                <a target="_blank" title="Digg" rel="nofollow" href="https://digg.com/submit?phase=2&amp;url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" >
                    <img alt="Digg"  src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323357.png"/>
                </a>
                {*
				<a href="https://reddit.com/submit?url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Reddit">
				      <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323468.png" alt="Reddit">
				</a>
                *}
				<a href="https://del.icio.us/post?url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" rel="nofollow" target="_blank" title="Del.icio.us">
				       <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323295.png" alt="Del.icio.us">
				</a>

                <a target="_blank" title="StumbleUpon" rel="nofollow" href="https://www.stumbleupon.com/submit?url=http{if $profileadvis_ssl == 1}s{/if}://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&amp;title={$meta_title|escape:'htmlall':'UTF-8'}">
                    <img alt="StumbleUpon" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/share/1292323491.png"/>
                </a>
			</div>						
	</div>
				
<div class="clr"><!-- --></div>

<!--  tab -->
<div class="b-tab b-tab-16-profile-page">
	<ul>
		<li class="current">
			<a href="{$profileadvshopper_url|escape:'htmlall':'UTF-8'}{$customer.id_customer|escape:'htmlall':'UTF-8'}">
			   	{l s='Profile' mod='profileadv'}</a>
		</li>
		
	</ul>
</div>
<!--  end tab  -->
				
				
<div class="b-tab-wrapper">
	<div class="b-details">
		<div class="items margin-top-10">

			<table class="title-first">
				<tr class="odd">
					<td>
						<b>{l s='Addresses for' mod='profileadv'} 
						<a title="{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}" class="lnk-for-title-dr"
						   href="{$profileadvshopper_url|escape:'htmlall':'UTF-8'}{$customer.id_customer|escape:'htmlall':'UTF-8'}">{$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}</a>:
						</b>
					</td>
				</tr>
			</table>
			{if count($customer.addresses)>0}
			{foreach from=$customer.addresses item=address name=ItemMyLoop}
			<table class="margin-top-10 title-first">
				<tr class="even">
					<td class="width-33">
						<b class="font-size-12">
							{l s='Location #' mod='profileadv'}{$smarty.foreach.ItemMyLoop.index+1|escape:'htmlall':'UTF-8'}:
						</b>
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<table class="title-first border-none">
				<tr class="even">
					<td style="line-height: 1.3em;">
						{if strlen($address.country)>0}{$address.country|escape:'htmlall':'UTF-8'}, <br>{/if}
						{if strlen($address.city)>0}{$address.city|escape:'htmlall':'UTF-8'}, <br/>{/if}
						{if strlen($address.postcode)>0}{$address.postcode|escape:'htmlall':'UTF-8'}{/if}
					</td>
				</tr>
			</table>
			
			
			{/foreach}
			{else}
			<table class="title-first border-none margin-top-10">
				<tr class="even">
					<td>
						<b class="font-size-12">{l s='Addresses Not Found.' mod='profileadv'}</b>			
					</td>
				</tr>
			</table>
			{/if}

		</div>	
	</div>
</div>

		<div class="b-inside-pages">
			<div class="b-column-c" style="padding-bottom:0px">
				<div class="b-bottom-line {if $profileadvis16 == 1}b-bottom-line-16{/if}" style="border-left:none" >
					<div class="b-go-top">
						<a href="#top">{l s='Back to top' mod='profileadv'}</a>
					</div>
				</div>
			</div>
		</div>		
</div>

{/foreach}