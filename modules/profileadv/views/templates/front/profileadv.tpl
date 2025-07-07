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

{if $profileadvis16 == 1 && $profileadvis17 ==0}
    {capture name=path}<a href="{$profileadvmy_account|escape:'htmlall':'UTF-8'}">{l s='My account' mod='profileadv'}</a>
        <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='User Profile Advanced' mod='profileadv'}{/capture}
{/if}


{if $profileadvis17 == 1}

    <a href="{$profileadvmy_account|escape:'htmlall':'UTF-8'}">{l s='My account' mod='profileadv'}</a>
        <span class="navigation-pipe"> > </span>{l s='User Profile Advanced' mod='profileadv'}
{/if}





<form method="post" action="{$profileadvajax_profile_url nofilter}" enctype="multipart/form-data"
	 id="user_profile_photo" name="user_profile_photo" 
	 {if $profileadvis_chrome == 0}onsubmit="return false;"{/if}>
	 
<input type="hidden" name="action" value="addimage" />

{if $profileadvstatus_error == 1}
    <div class="bootstrap">
        <div class="alert alert-warning">
            <button type="button" data-dismiss="alert" class="close">×</button>
            <strong>{l s='Error' mod='profileadv'}:</strong>&nbsp;{$profileadvmessage_error|escape:'htmlall':'UTF-8'}
            &zwnj;</div>
    </div>
{/if}

<div class="b-info-block">
	<div class="b-body">

		<dl>
			<dt class="check-box">
				<label class="check-box">{l s='Show my profile on the site' mod='profileadv'}</label>
			</dt>
			<dd>
					<input type="checkbox" name="show_my_profile" id="show_my_profile" class="check-box"
						   {if $profileadvis_show == 1}checked="checked"{/if} 
					onclick="profileadv_change(1)"/>
			</dd>
		</dl>
	
		<dl class="b-photo-ed">
			<dt><label for="n3">{l s='Main Photo:' mod='profileadv'}</label></dt>
				<dd>
					<div class="b-avatar">
						<a href="javascript:void(0)" onclick="profileadv_change(1)" 
						   title="{l s='Edit/Change Photo' mod='profileadv'}">
						<img alt="" class="profile-adv-user-img"  id="profile-adv-user-img"
							 src="{$profileadvavatar_thumb|escape:'htmlall':'UTF-8'}"
							 {if $profileadvexist_avatar} style="border:none" {/if}>
						</a>
					</div>
					
					<div class="b-edit">
						<a href="javascript:void(0)" onclick="profileadv_change(1)"
						   id="profileadv_edit_button" title="{l s='Edit/Change Photo' mod='profileadv'}">
						   {l s='Edit/Change' mod='profileadv'}</a>
						   
						   <input type="file" name="profileadvimg" id="profileadvimg" style="display:none" />
							
					</div>
					<div class="clr"><!-- --></div>
                    <div class="b-guide">
                        {l s='Allow formats' mod='profileadv'} *.jpg; *.jpeg; *.png; *.gif.
					</div>

                    {if $profileadvis_demo == 1}
                    <div class="bootstrap">
                        <div class="alert alert-warning">
                            <button type="button" data-dismiss="alert" class="close">×</button>
                            <strong>Warning</strong><br>
                            Feature disabled on the demo mode
                            &zwnj;</div>
                    </div>
                    {/if}

                </dd>


		</dl>
		
		<div class="b-buttons-save display-none" id="profileadv_btn_cancel">
			<div class="b-submit b-btn">
				<label class="bold">
					<input type="submit" value="{l s='Save Changes' mod='profileadv'}" class="txt"
					       title="{l s='Save Changes' mod='profileadv'}"  />
				</label>
			</div>
			<div class="b-cancel b-btn margin-left-5" >
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


{if $profileadvis_chrome == 0}
{literal}
<script type="text/javascript">

$(document).ready(function() {
	$(".b-body")
	.ajaxStart(function(){
		$(this).css('opacity','0.5');
	})
	.ajaxComplete(function(){
		$(this).css('opacity','1');
	});
	var options = {
		beforeSubmit:  showRequest,
		success:       showResponse,
		url:        '{/literal}{$profileadvajax_profile_url nofilter}{literal}',  // your upload script
		dataType:  'json'
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
		alert('{/literal}{l s='Please select a file.' mod='profileadv'}{literal}');
		return false;
	} 
	*/
	return true;
} 

function showResponse(data, statusText)  {
	//alert(data);
	if (data.status == 'success') {
		if (data.params.avatar_thumb != '') {
			
	
			var is_show = data.params.is_show;
			if(is_show == 1)
				$('#show_my_profile').attr('checked','checked');
			else
				$('#show_my_profile').attr('checked','');
			
			var fileToUploadValue = $("input[name=profileadvimg]").fieldValue();
			if (fileToUploadValue[0]) {
		
			var count = Math.random();
			document.getElementById('profile-adv-user-img').src = "";
			document.getElementById('profile-adv-user-img').src = data.params.avatar_thumb+"?re=" + count;
			$('#profile-adv-user-img').css('border','none');
			
			
			// small image
			var count1 = Math.random();
			$('#profile-adv-user-img-small').remove();
			var ph =  '<img style="margin-left:5px;"'+ 
			'height="20" src="{/literal}'+data.params.avatar_thumb+'?re='+count1+'{literal}"'+
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