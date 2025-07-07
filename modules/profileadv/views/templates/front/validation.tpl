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
    {if !$is_guest}
        {if !$validated}
            <div class="alert alert-danger" role="alert">
                A simple danger alert—check it out!
            </div>
        {else}
            <section class="text-center" style="margin: 10%;">
                <div class="profileadv-add-header">
                    <h1 class="text-title" style="margin: 0 15%">{l s='validation-login-title' mod='profileadv'} <span>
                            {$pet_data['name']}</span> {l s='validation-login-title2' mod='profileadv'}</h1>
                    <p class="text-subtitle" style="margin-top: 5%; color: #2B2B2B">
                        {l s='validation-login-subtitle' mod='profileadv'}</p>
                </div>
                <div class="text-center" style="padding: 0% 20%; margin-top:5%">
                    <div class="navigation-buttons">
                        <a class="btn btn-primary next"
                            href="{$link->getPageLink('my-account', true)}">{l s='Login' mod='profileadv'}</a>
                    </div>
                </div>
            </section>
        {/if}
    {else}
        <section class="text-center" style="margin: 10%;">
            <div class="profileadv-add-header">
                <h1 class="text-title" style="margin: 0 15%"><span>
                        {$pet_data['name']}</span>{l s='validation-register-title' mod='profileadv'}</h1>
                <p class="text-subtitle" style="margin-top: 5%; color: #2B2B2B">
                    {l s='validation-register-subtitle' mod='profileadv'}</p>
            </div>
            <div class="text-center" style="padding: 0% 20%; margin-top:5%">
                <div class="navigation-buttons">
                    <a class="btn btn-primary next"
                        href="{$link->getPageLink('registration', true)}">{l s='Register' mod='profileadv'}</a>
                </div>
            </div>
        </section>
    {/if}

{/block}