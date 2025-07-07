<div id="mipets-product">
    <div class="row">
        <div class="col-12">
            <div class="panel panel-primary">
                <div class="panel panel-sm">
                    {if $petList|@count gt 0}
                        <table class="table text-center">
                            <tbody>
                                {foreach from=$petList item=curr}
                                    <tr {if $curr@iteration > 2}class="hidden-row hidden" {/if}>
                                        <td onclick="window.open('/calculadora', '_blank')">
                                            <img class="img-fluid" src="/img/pets/{$curr['img']}" />
                                        </td>
                                        <td onclick="window.open('/calculadora', '_blank')">
                                            {$curr['name']}
                                        </td>
                                        </td>
                                        <td><strong>{if $curr['daily_cost'] > 0}{$curr['daily_cost']}{l s='€/día' mod='profileadv'}{else}-{/if}</strong>
                                        </td>
                                        <td
                                            style="{if $curr['amount'] > 0}color: #72c279;{else}color: #e08f95;{/if} font-weight: bold; ">
                                            <p class="badge badge-secondary" data-toggle="modal" data-target="#{$curr['id']}">
                                                {if $curr['daily_amount'] > 0}{$curr['daily_amount']} {l s='días' mod='profileadv'}{else}-{/if}
                                            </p>
                                        </td>
                                    </tr>
                                    <!-- Modal -->
                                    <div class="modal fade" id="{$curr['id']}" tabindex="-1" role="dialog"
                                        aria-labelledby="{$curr['id']}Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered  modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <p style="margin-bottom:1rem" class="text-center">
                                                        {l s='¡Has seleccionado' mod='profileadv'}
                                                        <strong>{($current_product->weight_kg/1000)|string_format:"%.2f"}{l s='Kg!' mod='profileadv'}</strong>
                                                    </p>
                                                    <p class="text-center">
                                                        {l s='Con esta cantidad alimentarás a tu mascota durante' mod='profileadv'}
                                                        <strong>{$curr['daily_amount']}
                                                            {l s='días' mod='profileadv'}</strong>
                                                        {l s='aproximadamente.' mod='profileadv'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {if $curr@iteration === 1 && $petList|@count gt 2}
                                        <div class="panel-body text-center">
                                            <div class="alert" role="alert">
                                                <button onclick="showAllPetsRows()" type="button"
                                                    class="btn btn-info all-pets-btn">{l s="Ver todas mis mascotas" mod='profileadv'}</button>
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="panel-body text-center">
                            <div class="alert" role="alert">
                                <button onclick="window.open('/calculadora', '_blank')" type="button"
                                    class="btn btn-info" style="white-space: normal;">{l s="Calcula el coste diario de tu mascota" mod='profileadv'}</button>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>