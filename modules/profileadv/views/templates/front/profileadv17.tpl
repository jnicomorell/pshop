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

    {if $profileadvis16 == 1 && $profileadvis17 ==0}
        {capture name=path}<a href="{$profileadvmy_account|escape:'htmlall':'UTF-8'}">{l s='Mi cuenta' mod='profileadv'}</a>
            <span
            class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='User Profile Advanced' mod='profileadv'}{/capture}
    {/if}


    {if $profileadvis17 == 1}

        <a href="{$profileadvmy_account|escape:'htmlall':'UTF-8'}">{l s='Mi cuenta' mod='profileadv'}</a>
        <span class="navigation-pipe"> > </span>{l s='User Profile Advanced' mod='profileadv'}
    {/if}

    <form method="post" action="{$profileadvajax_profile_url nofilter}" enctype="multipart/form-data"
        id="user_profile_photo" name="user_profile_photo" {if $profileadvis_chrome == 0}onsubmit="return false;" {/if}>

        <input type="hidden" name="action" value="addimage" />

        {if $profileadvstatus_error == 1}
            <div class="bootstrap">
                <div class="alert alert-warning">
                    <button type="button" data-dismiss="alert" class="close">×</button>
                    <strong>{l s='Error' mod='profileadv'}:</strong>&nbsp;{$profileadvmessage_error|escape:'htmlall':'UTF-8'}
                    &zwnj;
                </div>
            </div>
        {/if}

        <div class="b-info-block">
            <div class="b-body">

                <dl class="b-photo-ed">
                    <dt><label for="n3">{l s='Main Photo:' mod='profileadv'}</label></dt>
                    <dd>
                        <div class="b-avatar">
                            <a href="javascript:void(0)" onclick="profileadv_change(1)"
                                title="{l s='Edit/Change Photo' mod='profileadv'}">
                                <img alt="" class="profile-adv-user-img" id="profile-adv-user-img"
                                    src="{$profileadvavatar_thumb|escape:'htmlall':'UTF-8'}" {if $profileadvexist_avatar}
                                    style="border:none" {/if}>
                            </a>
                        </div>

                        <div class="b-edit">
                            <a href="javascript:void(0)" onclick="profileadv_change(1)" id="profileadv_edit_button"
                                title="{l s='Edit/Change Photo' mod='profileadv'}">
                                {l s='Edit/Change' mod='profileadv'}</a>

                            <input type="file" name="profileadvimg" id="profileadvimg" style="display:none" />

                        </div>
                        <div class="clr">
                            <!-- -->
                        </div>
                        <div class="b-guide">
                            {l s='Allow formats' mod='profileadv'} *.jpg; *.jpeg; *.png; *.gif.
                        </div>

                        {if $profileadvis_demo == 1}
                            <div class="bootstrap">
                                <div class="alert alert-warning">
                                    <button type="button" data-dismiss="alert" class="close">×</button>
                                    <strong>Warning</strong><br>
                                    Feature disabled on the demo mode
                                    &zwnj;
                                </div>
                            </div>
                        {/if}

                    </dd>


                </dl>

                <div class="b-buttons-save display-none" id="profileadv_btn_cancel">
                    <div class="b-submit b-btn">
                        <label class="bold">
                            <input type="submit" value="{l s='Save Changes' mod='profileadv'}" class="txt"
                                title="{l s='Save Changes' mod='profileadv'}" />
                        </label>
                    </div>
                    <div class="b-cancel b-btn margin-left-5">
                        <label class="bold">
                            <input type="submit" href="javascript:void(0)" onclick="profileadv_change(0);return false;"
                                value="{l s='Cancel' mod='profileadv'}" class="txt"
                                title="{l s='Cancel' mod='profileadv'}" />
                        </label>
                    </div>
                </div>

            </div>

        </div>

    </form>
                        
    {foreach from=$profileadvpetlist item=item}
        {foreach from=$item item=row}
            <div class="card" style="width: 18rem;">
                <img class="card-img-top img-responsive" src="{$profileadvavatar_thumb|escape:'htmlall':'UTF-8'}" alt="{$row.name}" style="width: 100%;"> 
                <div class="card-body">
                    <h5 class="card-title">{$row.name}</h5>
                    <td class="recomm_amount">{$row.amount}</td>
                    <div class="row" style="text-align: center;">
                            <div class="col-4">
                                <i class="fa fa-list-alt" aria-hidden="true"></i>
                            </div>
                            <div class="col-4">
                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            </div>
                            <div class="col-4">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </div>
                    </div>
                </div>
            </div>
            <table class="table table-hover table-responsive">
                <thead>
                    <tr>
                        <th scope="col">{l s='Genre' mod='profileadv'}</th>
                        <th scope="col">
                            {l s='Birthday' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Breed' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Weight' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Feeding' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Activity' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Physical Condition' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Pathology' mod='profileadv'}</th>
                        <th scope="col">


                            {l s='Allergies' mod='profileadv'}</th>
                        <th scope="col">
                    </tr>
                </thead>
                <tbody> {$profileadvpetlist|@var_dump}
                    <tr>
                        <td> {$row.genre}</td>
                        <td>{$row.birth}</td>
                        <td> {$row.breed}</td>
                        <td>
                            {$row.weight}</td>
                        <td>
                            {$row.feeding}</td>
                        <td>
                            {$row.activity}</td>
                        <td>
                            {$row.physical_condition}</td>
                        <td>
                            {$row.pathology|nl2br}</td>
                        <td>
                            {$row.allergies|nl2br}</td>
                    </tr>
                </tbody>
            </table>
        {/foreach}

    {/foreach}

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

                    /*
         $('#user_profile_photo').bind('submit', function(e) {
         //e.preventDefault(); // <-- important
         $(this).ajaxSubmit(options);
         });
         */
                });



                function showRequest(formData, jqForm, options) {
                    var fileToUploadValue = $("input[name=profileadvimg]").fieldValue();

                /*if (!fileToUploadValue[0]) {
                alert('
{/literal}
                {l s='Please select a file.' mod='profileadv'}
                {literal}');
        return false;
    }
    */
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
                if (fileToUploadValue[0]) {

                    var count = Math.random();
                    document.getElementById('profile-adv-user-img').src = "";
                    document.getElementById('profile-adv-user-img').src = data.params.avatar_thumb + "?re=" + count;
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