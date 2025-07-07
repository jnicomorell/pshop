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

{extends file="helpers/form/form.tpl"}
{block name="field"}

    {if $input.type == 'block_radio_buttons_custom'}


    <div class="col-lg-6 {$input.name|escape:'htmlall':'UTF-8'}">
        <div class="panel">


            <table class="table mitrocops-table-td">

                <tbody>


                <tr class="alt_row">
                    <td>
                        <input type="radio" value="vcount" id="showw" name="showw"
                                {if $input.values.style == "vcount"} checked="checked" {/if}>
                    </td>
                    <td>
                        <img src="../modules/profileadv/views/img/vertical.png" />
                    </td>
                </tr>
                <tr class="alt_row">
                    <td>
                        <input type="radio" value="hcount" id="showw" name="showw"
                                {if $input.values.style == "hcount"} checked="checked" {/if}>
                    </td>
                    <td>
                        <img src="../modules/profileadv/views/img/horizontal.png" />
                    </td>

                </tr>



                </tbody>
            </table>
        </div>

        {if isset($input.desc) && !empty($input.desc)}
            <p class="help-block">
                {$input.desc|escape:'htmlall':'UTF-8'}
            </p>
        {/if}
    </div>



    {elseif $input.type == 'checkbox_custom_blocks'}
        <div class="col-lg-9 {$input.name|escape:'htmlall':'UTF-8'}">

            {foreach $input.values.query as $value}
                {assign var=id_checkbox value=$value[$input.values.id]}
                <div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">

                    {strip}
                        <label for="{$id_checkbox|escape:'htmlall':'UTF-8'}">
                            <input type="checkbox" name="{$id_checkbox|escape:'htmlall':'UTF-8'}" id="{$id_checkbox|escape:'htmlall':'UTF-8'}"
                                   class="{if isset($input.class)}{$input.class}{/if}"{if isset($value.val)}
                            value="{$value.val|escape:'htmlall':'UTF-8'}"{/if}{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} checked="checked"{/if} />
                            {$value[$input.values.name]|escape:'htmlall':'UTF-8'}
                        </label>
                    {/strip}
                </div>
            {/foreach}

            {if isset($input.desc) && !empty($input.desc)}
                <p class="help-block">
                    {$input.desc|escape:'htmlall':'UTF-8'}
                </p>
            {/if}
        </div>
        {else}

		{$smarty.block.parent}
	{/if}
{/block}





