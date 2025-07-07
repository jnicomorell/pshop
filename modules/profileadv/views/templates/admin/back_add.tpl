<div class="admin-edit-pet">
    <div class="card" style="background: #FFF;">
        <div class="card-header">
            <h2>{l s="Añadir una nueva mascota" mod='profileadv'}</h2>
        </div>
        <div class="card-body clearfix">
            <form method="post" action="{$link->getAdminLink('AdminProfileAdvAdd')|escape:'htmlall':'utf-8'}"
                id="pet_edit_data" name="pet_edit_data">
                <div class="form-row">
                    <div class="form-check">
                        <div class="form-group col-md-4">
                            <label for="inputAmount">{l s='Pet Amount' mod='profileadv'}</label>
                            <input autocomplete="off" type="number" class="form-control" id="inputAmount"
                                name="pet-amount" placeholder="0">
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-md-4">
                            <label for="inputName">{l s='Pet name' mod='profileadv'}</label>
                            <input autocomplete="off" type="text" class="form-control" id="inputName" name="pet-name"
                                required>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-md-4">
                            <label for="inputBirth">{l s='Birth date' mod='profileadv'}</label>
                            <input autocomplete="off" type="date" class="form-control" id="inputBirth" name="pet-birth"
                                max="{$profileadvcurrentdate}" min="{$profileadvmaxolddate}" required>
                        </div>
                    </div>
                </div>
                <div class="form-check">
                    <div class="form-group col-md-4">
                        <label for="inputType">{l s='Type' mod='profileadv'}</label>
                        <select id="inputType" class="form-control" name="pet-type" required onchange="ShowBreedList()">
                            {foreach from=$profileadvtypelist item=item key=key}
                                <option value="{$key}">
                                    {$item}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputGenre">{l s='Genre' mod='profileadv'}</label>
                            <select id="inputGenre" class="form-control" name="pet-genre" required>
                                {foreach from=$profileadvgenrelist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputPhyisicalCondition">{l s='Physical Condition' mod='profileadv'}</label>
                            <select id="inputPhyisicalCondition" class="form-control" name="pet-physical-condition"
                                required>
                                {foreach from=$profileadvphysicalconditionlist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputWeight">{l s='Weight' mod='profileadv'}</label>
                            <input autocomplete="off" type="number" class="form-control" id="inputWeight"
                                name="pet-weight" step="0.01" max="70" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputDesiredWeight">{l s='Desired Weight' mod='profileadv'}</label>
                            <input autocomplete="off" type="number" class="form-control" id="inputDesiredWeight"
                                name="pet-desired-weight" step="0.01" max="70" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputEsterilized">{l s='Esterilized' mod='profileadv'}</label>
                            <select id="inputEsterilized" class="form-control" name="pet-esterilized" required>
                                {foreach from=$profileadvesterilizedlist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputBreed">{l s='Breed' mod='profileadv'}</label>
                            <select id="inputDogBreed" class="form-control" name="pet-breed-dog" required>
                                {foreach from=$profileadvdogbreedlist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                            <select id="inputCatBreed" class="form-control" name="pet-breed-cat" style="display: none;"
                                required>
                                {foreach from=$profileadvcatbreedlist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputActivity">{l s='Activity' mod='profileadv'}</label>
                            <select id="inputActivity" class="form-control" name="pet-activity" required>
                                {foreach from=$profileadvactivitylist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputFeeding">{l s='Feeding' mod='profileadv'}</label>
                            <select id="inputFeeding" class="form-control" name="pet-feeding" required>
                                {foreach from=$profileadvfeedinglist item=item key=key}
                                    <option value="{$key}">
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check" style="clear: both;">
                        <div class="form-group col-12" style="padding: 1%;">
                            <label for="inputPathologies"
                                style="display: block;">{l s='Pathologies' mod='profileadv'}</label>
                            {foreach from=$profileadvpathologieslist item=item key=key}
                                <input type="checkbox" class="form-check-input" id="inputPathology_{$key}"
                                    name="pet-pathology[]" value="{$key}">
                                <label class="form-check-label" for="inputPathology_{$key}"
                                    style="text-align: left;margin: 1% 2% 0% .2%;">{$item}</label>
                            {/foreach}
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12" style="padding: 1%;">
                            <label for="inputAllergies"
                                style="display: block;">{l s='Allergies' mod='profileadv'}</label>
                            {foreach from=$profileadvallergieslist item=item key=key}
                                <input type="checkbox" class="form-check-input" id="inputAllergies_{$key}"
                                    name="pet-allergies[]" value="{$key}">
                                <label class="form-check-label" for="inputAllergies_{$key}"
                                    style="text-align: left;margin: 1% 2% 0% .2%;">{$item}</label>
                            {/foreach}
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group">
                            <textarea class="form-control" id="pet-message" name="pet-message" rows="3"></textarea>
                            <label for="pet-message">{l s='Comment' mod='profileadv'}</label>
                        </div>
                    </div>
                </div>
                <center><button type="submit" class="btn btn-primary btn-lg btn-block"
                        style="float: right;width: 15%;margin-right: 1%;display:block;">{l s='Save' mod='profileadv'}</button>
                </center>
                <input type="hidden" name="pet-customer"
                    value="{if isset($smarty.get.customer)}{$smarty.get.customer}{/if}" />
                <input type="hidden" name="action" value="addpet" />
            </form>
        </div>
    </div>
</div>