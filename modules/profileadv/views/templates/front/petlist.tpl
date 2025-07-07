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

    {if $profileadvis17 == 1}

        <a href="{$link->getPageLink('my-account', true)|escape:'html'}">{l s='Mi cuenta' mod='profileadv'}</a>
        <span class="navigation-pipe"> > </span>{l s='Mis mascotas' mod='profileadv'}
    {/if}

    <div class="row">
        {if isset($smarty.get.error) && $smarty.get.error === '1'}
            <div class="alert alert-error" style="background: #ff0000; color: #FFFFFF">
                <p>{l s='Error formato' mod='profileadv'}</p>
            </div>
            <div class="alert alert-info">
                {l s='Formatos permitidos' mod='profileadv'} *.jpg; *.jpeg; *.png; *.gif.
                <i class="fa fa-check" aria-hidden="true"></i>
            </div>
        {/if}
        {if (isset($profileadvaction) && $profileadvaction == "delete") && $profileadvactionresult}
            <div class="alert alert-warning" role="alert">
                <i class="fa fa-check-circle" aria-hidden="true"></i>
                {l s='Mascota eliminada correctamente' mod='profileadv'}
            </div>
        {elseif (isset($profileadvaction) && $profileadvaction == "delete") && !$profileadvactionresult}
            <div class="alert alert-danger" role="alert">
                <i class="fa fa-times-circle" aria-hidden="true"></i>
                {l s='Error al eliminar la mascota, vuelva a intentarlo más tarde' mod='profileadv'}
            </div>
        {/if}
    </div>

    <div class="row" style="margin-top: 2%">
        <div style="display: block;" id="petlist">
            {foreach from=$profileadvpetlist item=row}
                <!-- Team member -->
                <div class="col-12 col-sm-6 col-md-6 col-lg-4 pet-item">
                    <div class="image-flip">
                        <div class="mainflip">
                            <div class="frontside">
                                <div class="card">
                                    {if !empty($row.message)}
                                        <div style="float: left;">
                                            <button type="button" class="btn btn-warning popover-message" data-toggle="tooltip"
                                                data-placement="bottom" title="{$row.message}">
                                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    {/if}
                                    <div class="card-body text-center">
                                        <p style="margin-top: 5%;"><img class=" img-fluid"
                                                src="/img/pets/{$row.img|escape:'htmlall':'UTF-8'}" alt="{$row.name}"></p>
                                        <h4 class="card-title">{$row.name} -
                                            {if $row.ageyears > 0} {$row.ageyears}
                                                {l s='years' mod='profileadv'}
                                            {else}{$row.agemonths}
                                                {l s='meses' mod='profilead'}
                                            {/if}
                                        </h4>
                                        <div class="card-text">
                                            {if $row.amount > 0}
                                                <div class="alert alert-success" role="alert">
                                                    {l s='Ración diaria recomendada de' mod='profileadv'}
                                                    <strong>{$row.amount}{l s='gr' mod='profileadv'}</strong>
                                                </div>
                                            {else}
                                                <div class="alert alert-warning" role="alert">
                                                    <strong>{l s='Nos pondremos en contacto contigo :)' mod='profileadv'}</strong>
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="pet-actions">
                                            <div class="row" style="display: inline-flex;">
                                                <div class="col-4">
                                                    <a href="" class="" data-toggle="collapse"
                                                        data-target="#collapse{$row@iteration}" aria-expanded="true"
                                                        aria-controls="collapse{$row@iteration}"><i class="fa fa-list-alt"></i>
                                                        {l s='Datos' mod='profileadv'}</a>
                                                </div>
                                                <div class="col-4">
                                                    <a href="{$profileadveditpeturl}?pet={$row.reference}" class=""><i
                                                            class="fa fa-pencil-square-o"></i>
                                                        {l s='Editar' mod='profileadv'}</a>
                                                </div>
                                                <div class="col-4">
                                                    <a href="{$profileadvdeletepeturl}reference={$row.reference}" class=""><i
                                                            class="fa fa-trash-o"></i> {l s='Eliminar' mod='profileadv'}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="collapse{$row@iteration}" data-parent="#petlist" class="collapse show"
                                    aria-labelledby="heading{$row@iteration}" data-parent="#accordion" style="margin: 5% 0%;">
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Sexo' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.genre}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='F. Nacimiento' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.birth|date_format:"%D"}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Raza' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.breed|html_entity_decode}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Peso' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.weight} {l s='kg' mod='profileadv'}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Alimentación' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.feeding|html_entity_decode}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Esterilizado' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.esterilized|html_entity_decode}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Actividad' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.activity|html_entity_decode}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Condición física' mod='profileadv'}:</span><span
                                                    class="list-value">
                                                    {$row.physical_condition|html_entity_decode}</span>
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Patologías' mod='profileadv'}:</span>
                                                    {if !empty($row.pathology)}
                                                        <ul>
                                                            {foreach from=$row.pathology item=activity}
                                                                <li>
                                                                    <span class="list-value">{$activity|html_entity_decode}</span>
                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    {else}
                                                        <span class="list-value">{l s='Ninguna' mod='profileadv'}</span>
                                                    {/if}
                                            </li>
                                            <li class="list-group-item"><i class="fa fa-caret-right" aria-hidden="true"></i>
                                                <span class="list-label">{l s='Alergias' mod='profileadv'}:</span>
                                                    {if !empty($row.allergies)}
                                                        <ul>
                                                            {foreach from=$row.allergies item=activity}
                                                                <li>
                                                                    <span class="list-value">{$activity|html_entity_decode}</span>
                                                                </li>
                                                            {/foreach}
                                                        </ul>
                                                    {else}
                                                        <span class="list-value">{l s='Ninguna' mod='profileadv'}</span>
                                                    {/if}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ./Team member -->
            {/foreach}
            {* Add new pets*}
            <div class="col-12 col-sm-6 col-md-5 col-lg-4">
                <div class="image-flip">
                    <div class="mainflip">
                        <div class="frontside new-pet-card">
                            <div class="card">
                                <div class="card-body text-center">
                                    <p style="margin-top: 15%;"><a href="{$profileadvnewpet}"><img class=" img-fluid"
                                                src="/modules/profileadv/views/img/add-more.png?v=20250221"
                                                alt="Añadir nuevo animal"></a>
                                    </p>
                                    <p class="add-new-pet-label">
                                        {l s='Añadir una nueva mascota' mod='profileadv'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}