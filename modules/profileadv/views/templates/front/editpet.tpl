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

    <a href="/module/profileadv/petlist">{l s='My Pets' mod='profileadv'}</a>
    <span class="navigation-pipe"> >
    </span>{$profileadveditpetdata.name}

    <div style="margin-top: 2%">
        <div style="display: block;">
            <form method="post" action="{$profileadvajax_profile_url nofilter}" enctype="multipart/form-data"
                id="user_profile_photo" name="user_profile_photo" {if $profileadvis_chrome == 0}onsubmit="return false;"
                {/if}>

                <input type="hidden" name="action" value="editpet" />
                <input type="hidden" name="pet-reference" value="{$profileadveditpetdata.reference}" />
                <input type="hidden" name="pet-amount" value="{$profileadveditpetdata.amount}" />
                <input type="hidden" name="pet-message" value="{$profileadveditpetdata.message}" />

                <div class="b-info-block">
                    <div class="b-body">
                        <dl class="b-photo-ed">
                            <dt></dt>
                            <dd class="image">
                                <p><img class=" img-fluid"
                                        src="/img/pets/{$profileadveditpetdata.img|escape:'htmlall':'UTF-8'}"
                                        alt="{$profileadveditpetdata.name}"></p>
                                <div class="b-avatar">
                                    <a href="javascript:void(0)" onclick="profileadv_change(1)"
                                        title="{l s='Edit/Change Photo' mod='profileadv'}">
                                    </a>
                                </div>

                                <div class="image-options">
                                    <a href="javascript:void(0)" onclick="profileadv_change(1)" id="profileadv_edit_button"
                                        title="{l s='Edit/Change Photo' mod='profileadv'}">
                                        {l s='Modificar imagen' mod='profileadv'}</a> <i class="fa fa-pencil-square-o"
                                        aria-hidden="true"></i>

                                    <input type="file" name="profileadvimg" id="profileadvimg" style="display:none" />

                                </div>
                                <div class="alert alert-info">
                                    {l s='Formatos permitidos' mod='profileadv'} *.jpg; *.jpeg; *.png; *.gif.
                                    <i class="fa fa-check" aria-hidden="true"></i>
                                </div>
                            </dd>
                        </dl>

                    </div>

                </div>
                <div class="form-group row">
                    <div class="form-check">
                        <div class="form-group col-md-6">
                            <label for="inputName">{l s='Pet name' mod='profileadv'}</label>
                            <input type="text" class="form-control" id="inputName" name="pet-name"
                                value="{$profileadveditpetdata.name}" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-md-6">
                            <label for="inputBirth">{l s='Birth date' mod='profileadv'}</label>
                            <input type="date" class="form-control" id="inputBirth" name="pet-birth"
                                value="{$profileadveditpetdata.birth}" max="{$profileadvcurrentdate}"
                                min="{$profileadvmaxolddate}" required>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputType">{l s='Type' mod='profileadv'}</label>
                            <select id="inputType" class="form-control" name="pet-type" required>
                                {foreach from=$profileadvtypelist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.type == $key} selected="true" {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputGenre">{l s='Genre' mod='profileadv'}</label>
                            <select id="inputGenre" class="form-control" name="pet-genre" required>
                                {foreach from=$profileadvgenrelist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.genre == $key} selected="true" {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check" style="display: none;">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputPhyisicalCondition">{l s='Physical Condition' mod='profileadv'}</label>
                            <select id="inputPhyisicalCondition" class="form-control" name="pet-physical-condition"
                                required>
                                {foreach from=$profileadvphysicalconditionlist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.physical_condition == $key}
                                        selected="true" {/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputWeight">{l s='Weight' mod='profileadv'}</label>
                            <input type="number" class="form-control" id="inputWeight" name="pet-weight" step="0.01"
                                max="70" value="{$profileadveditpetdata.weight}" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-2">
                            <label for="inputDesiredWeight">{l s='Desired Weight' mod='profileadv'}</label>
                            <input type="number" class="form-control" id="inputDesiredWeight" name="pet-desired-weight" step="0.01"
                                max="70" value="{$profileadveditpetdata.desiredweight}" required>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputEsterilized">{l s='Esterilized' mod='profileadv'}</label>
                            <select id="inputEsterilized" class="form-control" name="pet-esterilized" required>
                                {foreach from=$profileadvesterilizedlist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.esterilized == $key} selected="true"
                                        {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputBreed">{l s='Breed' mod='profileadv'}</label>
                            <select id="inputDogBreed" class="form-control" name="pet-breed-dog"
                                {if $profileadveditpetdata.type == 2}style="display: none;" {/if}>
                                {foreach from=$profileadvdogbreedlist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.breed == $key} selected="true" {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                            <select id="inputCatBreed" class="form-control" name="pet-breed-cat"
                                {if $profileadveditpetdata.type == 1}style="display: none;" {/if} required>
                                {foreach from=$profileadvcatbreedlist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.breed == $key} selected="true" {/if}>
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
                                    <option value="{$key}" {if $profileadveditpetdata.activity == $key} selected="true" {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="form-check">
                        <div class="form-group col-12 col-md-4">
                            <label for="inputFeeding">{l s='Feeding' mod='profileadv'}</label>
                            <select id="inputFeeding" class="form-control" name="pet-feeding" required>
                                {foreach from=$profileadvfeedinglist item=item key=key}
                                    <option value="{$key}" {if $profileadveditpetdata.feeding == $key} selected="true" {/if}>
                                        {$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col" style="width: 45%;float: left;margin-right: 2%;padding: 2%;">
                        <div class="form-check">
                            <p style="color: #000;">{l s='Allergies' mod='profileadv'}</p>
                            <ul class="list-group list-group-flush">
                                {foreach from=$profileadvallergieslist item=item key=key}
                                    <li class="list-group-item">
                                        <input type="checkbox" class="form-check-input" id="inputAllergies_{$key}"
                                            name="pet-allergies[]" value="{$key}"
                                            {if $key|in_array:$profileadveditpetdata.allergies} checked="true" {/if}>
                                        <label class="form-check-label" for="inputAllergies_{$key}"
                                            style="text-align: left;">{$item}</label>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                    <div class="col" style="width: 45%;float: left;margin-left: 2%;padding: 2%;">
                        <div class="form-check">
                            <p style="color: #000;">{l s='Pathologies' mod='profileadv'}</p>
                            <ul class="list-group list-group-flush">
                                {foreach from=$profileadvpathologieslist item=item key=key}
                                    <li class="list-group-item">
                                        <input type="checkbox" class="form-check-input" id="inputPathology_{$key}"
                                            name="pet-pathology[]" value="{$key}"
                                            {if $key|in_array:$profileadveditpetdata.pathologies} checked="true" {/if}>
                                        <label class="form-check-label" for="inputPathology_{$key}"
                                            style="text-align: left;">{$item}</label>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                    <center><button type="submit" class="btn btn-primary btn-lg btn-block" onClick="submitClicked()"
                            id="submit-button" style="width: 50%;margin-left: 5%; display:block;font-weight: bold;
                            background-color: #00B4DD;
                            border-radius: 20px;
                            padding: 5px 15px;
                            color: white;
                            font-size: 1.1rem;text-transform: none;">{l s='Modificar' mod='profileadv'}</button>
                    </center>
                </div>
            </form>
        </div>
    </div>

    {if $profileadvis_chrome == 0}

        {literal}
            <script type="text/javascript">
                $(document).ready(function() {
                    $(".b-body")
                        .ajaxStart(function() {
                            $(this).css('opacity', '0.5');
                        })
                        .ajaxComplete(function() {
                            $(this).css('opacity', '1');
                        });
                    var options = {
                        beforeSubmit: showRequest,
                        success: showResponse,
                        url: '
            {/literal}{$profileadvajax_profile_url nofilter}
            {literal}',  // your upload script
                        dataType: 'json'
                    };

                    $('#user_profile_photo').submit(function() {
                        $(this).ajaxSubmit(options);
                        return false;
                    });

                });

                function showRequest(formData, jqForm, options) {
                    var fileToUploadValue = $("input[name=profileadvimg]").fieldValue();

                    return true;
                }

                function showResponse(data, statusText) {
                    //alert(data);
                    if (data.status == 'success') {
                        if (data.params.avatar_thumb != '') {


                            var is_show = data.params.is_show;
                            if (is_show == 1)
                                $('#show_my_profile').attr('checked', 'checked');
                            else
                                $('#show_my_profile').attr('checked', '');

                            var fileToUploadValue = $("input[name=profileadvimg]").fieldValue();
                            if (fileToUploadValue) {

                                var count = Math.random();
                                document.getElementById('profile-adv-user-img').src = "";
                                document.getElementById('profile-adv-user-img').src = data.params.avatar_thumb +
                                    "?re=" + count;
                                $('#profile-adv-user-img').css('border', 'none');

                                // small image
                                var count1 = Math.random();
                                $('#profile-adv-user-img-small').remove();
                                var ph = '<img style="margin-left:5px;"' +
                                    'height="20" src="
                    {/literal}'+data.params.avatar_thumb+'?re='+count1+'
                    {literal}"'+
                                'id="profile-adv-user-img-small" />';
                                $('#header_user_info span').append(ph);
                            }

                            $('.FileName').html('');

                            profileadv_change(0);

                        } else {
                            alert(data.message);
                        }
                    } else {
                        alert(data.message);
                    }
                }
            </script>

        {/literal}
    {/if}
{/block}