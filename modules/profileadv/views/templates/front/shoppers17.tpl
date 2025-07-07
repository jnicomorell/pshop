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

{extends file='page.tpl'}

{block name="page_content"}

    {if $profileadvis16 == 1}
        {capture name=path}
            {l s='All Shoppers' mod='profileadv'}
        {/capture}
        <h1 class="page-heading">{$meta_title|escape:'htmlall':'UTF-8'}</h1>
    {else}
        {$meta_title|escape:'htmlall':'UTF-8'}
    {/if}




    <div class="b-inside-pages">

        <div id="top" class="b-column-c">

            <div class="b-wrapper">

                <div class="b-tab b-tab16">
                    <ul>
                        <li class="current"><a href="#">{l s='Shoppers' mod='profileadv'} ({$profileadvdata_count_customers|escape:'htmlall':'UTF-8'})</a></li>
                    </ul>
                </div>

                <div class="b-search-friends">
                    <form action="#" onsubmit="return false;" method="post">
                        <fieldset>
                            <input type="image" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/profileadv/views/img/btn/btn-searh.gif" onclick="search();">
                            <input type="text" value="{l s='Find in Shoppers List' mod='profileadv'}"
                                   onfocus="{literal}if(this.value == '{/literal}{l s='Find in Shoppers List' mod='profileadv'}{literal}') {this.value='';};{/literal}" onblur="{literal}if(this.value == '') {this.value='{/literal}{l s='Find in Shoppers List' mod='profileadv'}{literal}';};{/literal}"
                                   id="search-shoppers" class="txt txt-16">
                            <a href="javascript:void(0)"
                               onclick="go_page_shoppers(0,0,'')"
                               class="clear-search-shoppers">{l s='Clear search' mod='profileadv'}</a>
                        </fieldset>
                    </form>
                </div>

                <div class="b-friends-list">
                    {if count($profileadvcustomers)>0}
                        <ul>
                            {foreach from=$profileadvcustomers item=customer name=myLoop}
                                {if $smarty.foreach.myLoop.index%4 == 0}
                                    {*<ul>*}
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
                                    <div class="clr"><!-- --></div>
                                {/if}
                            {/foreach}
                        </ul>
                    {else}
                        <div style="text-align:center;padding: 0px 20px 20px 20px;">
                            {l s='Shoppers not found' mod='profileadv'}
                        </div>
                    {/if}

                </div>


            </div>

            <div class="b-bottom-line b-bottom-line-16">
                <!--pager-->
                <div id="page_nav" class="b-pager-16">
                    {$profileadvpaging nofilter}
                </div>
                <!--end of pager-->
                <div class="b-go-top">
                    <a href="#top">{l s='Back to top' mod='profileadv'}</a>
                </div>
            </div>
        </div>

    </div>


{literal}
    <script type="text/javascript">

        var ajax_url_profileadv = '{/literal}{$profileadvajax_profile_url nofilter}{literal}';

        function go_page_shoppers(page,is_search,query){

            if(is_search == 0)
                $('.clear-search-shoppers').hide();

            $('.b-friends-list').css('opacity',0.5);
            $.post(ajax_url_profileadv, {
                        action:'pagenav',
                        page : page,
                        is_search : is_search,
                        q: query
                    },
                    function (data) {
                        if (data.status == 'success') {

                            $('.b-friends-list').css('opacity',1);

                            $('.b-friends-list').html('');
                            $('.b-friends-list').prepend(data.params.content);

                            $('#page_nav').html('');
                            $('#page_nav').prepend(data.params.page_nav);

                        } else {
                            $('.b-friends-list').css('opacity',1);
                            alert(data.message);
                        }

                    }, 'json');

        }

        function search(){
            $('.b-friends-list').css('opacity',0.5);
            var query = $('#search-shoppers').val();
            $.post(ajax_url_profileadv, {
                        action:'pagenav',
                        is_search : 1,
                        q : query
                    },
                    function (data) {
                        if (data.status == 'success') {

                            $('.b-friends-list').css('opacity',1);

                            $('.b-friends-list').html('');
                            $('.b-friends-list').prepend(data.params.content);

                            $('#page_nav').html('');
                            $('#page_nav').prepend(data.params.page_nav);

                            $('.clear-search-shoppers').show();

                        } else {
                            $('.b-friends-list').css('opacity',1);
                            alert(data.message);
                        }

                    }, 'json');

        }
    </script>
{/literal}

{/block}
