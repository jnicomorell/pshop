<div class="admin-edit-pet">
  <div class="card" style="background: #FFF;">
    <div class="card-header">
      <h2>{l s="Editar los datos de " mod='profileadv'} <strong>{$pet_data.name|default:''}</strong></h2>
    </div>
    <div class="card-body clearfix">
      {if isset($pet_data.allergies) && count($pet_data.allergies) > 0}
        <div class="row">
          <div class="alert alert-warning" role="alert">
            {l s="Mascota con alergias" mod='profileadv'}
          </div>
        </div>
      {/if}
      {if isset($pet_data.pathology) && count($pet_data.pathology) > 0}
        <div class="row">
          <div class="alert alert-warning" role="alert">
            {l s="Mascota con patologías" mod='profileadv'}
          </div>
        </div>
      {/if}
      <div class="row">
        <div style="display: block;">
          <form method="post" action="{$link->getAdminLink('Adminprofileadv')|escape:'htmlall':'utf-8'}"
            id="pet_edit_data" name="pet_edit_data">
            <div class="b-info-block">
              <div class="b-body">
                <dl class="b-photo-ed">
                  <dt></dt>
                  <dd style="text-align: center;">
                    <p><img class="img-fluid pet-img"
                        style="max-width: 600px; max-height: 300px; object-fit: cover; border-radius: 10%;"
                        src="/img/pets/{if isset($pet_data.avatar_thumb)}{$pet_data.avatar_thumb|escape:'htmlall':'UTF-8'}{else}-{/if}" alt="{if isset($pet_data.name)}{$pet_data.name}{else}-{/if}">
                    </p>
                  </dd>
                </dl>
              </div>
            </div>
            <div class="row">
              <div class="form-check">
                <div class="form-group col-md-2" style="background: #92d097; color: #FFF;">
                  <label for="pet-amount">{l s='Amount' mod='profileadv'}</label>
                  <input type="number" class="form-control" id="pet-amount" name="pet-amount" step="0.01"
                    value="{if isset($pet_data.amount)}{$pet_data.amount}{else}-{/if}" required {if isset($pet_data.is_amount_blocked) && $pet_data.is_amount_blocked === 1} readonly{/if}>
                </div>
              </div>
              <div class="form-check">
                <div class="form-group col-md-2">
                  <input type="checkbox" class="form-check-input" id="pet-amount-blocked" name="pet-amount-blocked"
                    {if isset($pet_data.is_amount_blocked) && $pet_data.is_amount_blocked === 1} checked{/if}>
                  <label class="form-check-label" for="pet-amount-blocked">{l s='Bloquear' mod='profileadv'}
                    <a type="button" data-toggle="tooltip" data-placement="top"
                      title="Si marcas esta opción, prevalecerá la cantidad indicada en la casilla de la izquierda para esta mascota (el programa no calculará automáticamente la cantidad, tampoco en el perfil del cliente).">
                      <i class="material-icons" style="color: red;">new_releases</i></a>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-check">
                <div class="form-group col-md-4">
                  <label for="pet-name">{l s='Pet name' mod='profileadv'}</label>
                  <input type="text" class="form-control" id="pet-name" name="pet-name" value="{$pet_data.name|default:''}"
                    required>
                </div>
              </div>
              <div class="form-check">
                <div class="form-group col-md-4">
                  <label for="inputBirth">{l s='Birth date' mod='profileadv'}</label>
                  <input type="date" class="form-control" id="inputBirth" name="pet-birth" value="{if isset($pet_data.birth)}{$pet_data.birth}{else}-{/if}"
                    max="{$profileadvcurrentdate}" min="{$profileadvmaxolddate}" required>
                </div>
              </div>
            </div>
            <div class="form-check">
              <div class="form-group col-md-4">
                <label for="inputType">{l s='Type' mod='profileadv'}</label>
                <select id="inputType" class="form-control" name="pet-type" required onchange="ShowBreedList()">
                  {foreach from=$profileadvtypelist item=item key=key}
                    <option value="{$key}" {if isset($pet_data.type) && $pet_data.type == $key} selected="true" {/if}>
                      {$item}</option>
                  {/foreach}
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-check">
                <div class="form-group col-xs-12 col-md-4">
                  <label for="inputGenre">{l s='Genre' mod='profileadv'}</label>
                  <select id="inputGenre" class="form-control" name="pet-genre" required>
                    {foreach from=$profileadvgenrelist item=item key=key}
                      <option value="{$key}" {if isset($pet_data.genre) && $pet_data.genre == $key} selected="true" {/if}>
                        {$item}</option>
                    {/foreach}
                  </select>
                </div>
              </div>
              <div class="form-check" style="display: none;">
                <div class="form-group col-xs-12 col-md-2">
                  <label for="inputPhyisicalCondition">{l s='Physical Condition' mod='profileadv'}</label>
                  <select id="inputPhyisicalCondition" class="form-control" name="pet-physical-condition" required>
                    {foreach from=$profileadvphysicalconditionlist item=item key=key}
                      <option value="{$key}" {if isset($pet_data.physical_condition) && $pet_data.physical_condition == $key} selected="true" {/if}>
                        {$item}</option>
                    {/foreach}
                  </select>
                </div>
              </div>
              <div class="form-check">
                {if isset($pet_data.weight) && isset($pet_data.desired_weight) && $pet_data.weight > 0 && $pet_data.desired_weight > 0}
                  {assign var="desired_weight_percentage" value=(((100 * $pet_data.weight) / $pet_data.desired_weight)-100)}
                  {assign var="desired_weight_percentage" value=$desired_weight_percentage|string_format:"%.2f"}
                  <div class="form-group col-xs-12 col-md-2"
                    style="color: #FFF; background:{if $desired_weight_percentage > 10 || $desired_weight_percentage < -10} #bd473e{else}#92d097{/if}">

                    <label for="inputWeight">{l s='Weight' mod='profileadv'}</label>
                    <a type="button" data-toggle="tooltip" data-placement="top" style="color: #FFF;"
                        title="El peso de {if isset($pet_data.name)}{$pet_data.name}{else}-{/if} es un ' {$desired_weight_percentage}% ' {if $desired_weight_percentage > 10}SUPERIOR{else if $desired_weight_percentage < -10}INFERIOR{/if} respecto al peso ideal de la mascota">
                      ({$desired_weight_percentage}% -
                      {if $desired_weight_percentage > 10}SUPERIOR
                      {else if $desired_weight_percentage < -10}INFERIOR
                      {else}SE
                      CONSIDERA ACEPTABLE{/if})
                    </a>
                  {else}
                    <div class="form-group col-xs-12 col-md-2">
                      <label for="inputWeight">{l s='Weight' mod='profileadv'}</label>
                    {/if}
                    <input type="number" class="form-control" id="inputWeight" name="pet-weight" step="0.01" max="70"
                      value="{if isset($pet_data.weight)}{$pet_data.weight}{else}-{/if}" required>
                  </div>
                </div>
                <div class="form-check">
                  <div class="form-group col-xs-12 col-md-2">
                    <label for="inputDesiredWeight">{l s='Desired Weight' mod='profileadv'}</label>
                    <input type="number" class="form-control" id="inputDesiredWeight" name="pet-desired-weight"
                      step="0.01" max="70" value="{if isset($pet_data.desired_weight)}{$pet_data.desired_weight}{else}-{/if}" required>
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-check">
                  <div class="form-group col-xs-12 col-md-4">
                    <label for="inputEsterilized">{l s='Esterilized' mod='profileadv'}</label>
                    <select id="inputEsterilized" class="form-control" name="pet-esterilized" required>
                      {foreach from=$profileadvesterilizedlist item=item key=key}
                        <option value="{$key}" {if isset($pet_data.esterilized) && $pet_data.esterilized == $key} selected="true" {/if}>
                          {$item}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-check">
                  <div class="form-group col-xs-12 col-md-4">
                    <label for="inputBreed">{l s='Breed' mod='profileadv'}</label>
                    <select id="inputDogBreed" class="form-control" name="pet-breed-dog" required
                      {if isset($pet_data.type) && $pet_data.type == 2}style="display: none;" {/if}>
                      {foreach from=$profileadvdogbreedlist item=item key=key}
                        <option value="{$key}" {if isset($pet_data.breed) && $pet_data.breed == $key} selected="true" {/if}>
                          {$item}</option>
                      {/foreach}
                    </select>
                    <select id="inputCatBreed" class="form-control" name="pet-breed-cat"
                      {if isset($pet_data.type) && $pet_data.type == 1}style="display: none;" {/if} required>
                      {foreach from=$profileadvcatbreedlist item=item key=key}
                        <option value="{$key}" {if isset($pet_data.breed) && $pet_data.breed == $key} selected="true" {/if}>
                          {$item}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
                <div class="form-check">
                  <div class="form-group col-xs-12 col-md-4">
                    <label for="inputActivity">{l s='Activity' mod='profileadv'}</label>
                    <select id="inputActivity" class="form-control" name="pet-activity" required>
                      {foreach from=$profileadvactivitylist item=item key=key}
                        <option value="{$key}" {if isset($pet_data.activity) && $pet_data.activity == $key} selected="true" {/if}>
                          {$item}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-check">
                  <div class="form-group col-xs-12 col-md-4">
                    <label for="inputFeeding">{l s='Feeding' mod='profileadv'}</label>
                    <select id="inputFeeding" class="form-control" name="pet-feeding" required>
                      {foreach from=$profileadvfeedinglist item=item key=key}
                        <option value="{$key}" {if isset($pet_data.feeding) && $pet_data.feeding == $key} selected="true" {/if}>
                          {$item}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
                <div class="form-check">
                  <div class="form-group col-xs-12" style="padding: 1%;">
                    <label for="inputPathologies" style="display: block;">{l s='Pathologies' mod='profileadv'}</label>
                    {foreach from=$profileadvpathologieslist item=item key=key}
                      <input type="checkbox" class="form-check-input" id="inputPathology_{$key}" name="pet-pathology[]"
                        value="{$key}" {if isset($pet_data.pathology) && $key|in_array:$pet_data.pathology} checked="true" {/if}>
                      <label class="form-check-label" for="inputPathology_{$key}"
                        style="text-align: left;margin: 1% 2% 0% .2%;">{$item}</label>
                    {/foreach}
                  </div>
                </div>
                <div class="form-check">
                  <div class="form-group col-xs-12" style="padding: 1%;">
                    <label for="inputAllergies" style="display: block;">{l s='Allergies' mod='profileadv'}</label>
                    {foreach from=$profileadvallergieslist item=item key=key}
                      <input type="checkbox" class="form-check-input" id="inputAllergies_{$key}" name="pet-allergies[]"
                        value="{$key}" {if isset($pet_data.allergies) && $key|in_array:$pet_data.allergies} checked="true" {/if}>
                      <label class="form-check-label" for="inputAllergies_{$key}"
                        style="text-align: left;margin: 1% 2% 0% .2%;">{$item}</label>
                    {/foreach}
                  </div>
                </div>
              </div>
              <div class="form-check">
                <div class="form-group">
                  <label for="pet-message">{l s='Comment' mod='profileadv'}</label>
                  <textarea class="form-control" id="pet-message" name="pet-message"
                    rows="3">{if isset($pet_data.message)}{$pet_data.message}{else}-{/if}</textarea>
                </div>
              </div>
              <center>
                <a type="button" class="btn btn-primary btn-lg btn-block" onclick="notifyAmountToCustomer()"
                  style="float: right;width: 15%;margin-right: 1%;display:block;"
                  id="submit-btn">{l s='Save' mod='profileadv'}</a>
              </center>
              <input type="hidden" name="pet-reference" id="pet-reference" value="{if isset($pet_data.reference)}{$pet_data.reference}{else}-{/if}" />
              <input type="hidden" name="pet-prev-amount" id="pet-prev-amount" value="{if isset($pet_data.amount)}{$pet_data.amount}{else}-{/if}" />
              <input type="hidden" name="pet-customer" id="pet-customer" value="{$profileadvcustomerData['id']}" />
              <input type="hidden" name="pet-customer-name" id="pet-customer-name"
                value="{$profileadvcustomerData['name']}" />
              <input type="hidden" name="pet-customer-phone" id="pet-customer-phone"
                value="{$profileadvcustomerData['phone']}" />
              <input type="hidden" name="pet-customer-risk" id="pet-customer-risk"
                value="{$profileadvcustomerData['id_risk']}" />
              <input type="hidden" name="pet-employee" id="pet-employee" value="{$profileadvemployeeData['id']}" />
              <input type="hidden" name="pet-employee-name" id="pet-employee-name"
                value="{$profileadvemployeeData['name']}" />
              <input type="hidden" name="pet-active" id="pet-active"
                value="{if isset($pet_data.active)}{$pet_data.active}{else}-{/if}" />
              <input type="hidden" name="pet-is-validated" id="pet-is-validated"
                value="{if isset($pet_data.is_validated)}{$pet_data.is_validated}{else}-{/if}" />
              <input type="hidden" name="action" value="editpet" />
          </form>
        </div>
      </div>
    </div>
  </div>
</div>