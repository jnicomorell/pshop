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
    {if isset($logged) && $logged}
        <a href="/calculadora">{l s='My Pets' mod='profileadv'}</a>
        <span class="navigation-pipe"> ></span>
    {/if}
    {if isset($smarty.get.showdata) && $smarty.get.showdata == 1}
        {$profileadvnewpetdata.name}
    {else}
        {l s='Add new pet' mod='profileadv'}
    {/if}

    {if isset($smarty.get.showdata) && $smarty.get.showdata  == 1}

        <div id="pet-data-info">
            <div class="row">
                <div class="col-xs-12">
                    {if $profileadvnewpetdata.amount > 0}
                        {if $profileadvnewpetdata.amount_blocked}
                            <div class="text-center mt-2 alert alert-warning" role="alert"
                                style="color: #25668e;border: 1px solid #25668e">
                                {l s='Amount blocked' mod='profileadv'}
                            </div>
                        {/if}
                    {else}
                        <div class="alert alert-warning" role="alert" style="padding: 2%; margin: 10px 10px 0px 10px;">
                            <strong>{$profileadvnewpetdata.comment|html_entity_decode}</strong>
                        </div>
                    {/if}
                </div>
            </div>
            {if $profileadvnewpetdata.amount > 0}
                <div class="contenedor-background">
                    <div class="container">
                        <div class="row title-desktop">
                            <div class="col-md-12">
                                <h1 class="text-center mt-0">
                                    <span class="fw-500">{l s='Title-resume-pre' mod='profileadv'}</span>
                                    <span class="fw-700">{l s='Title-resume-post' mod='profileadv'}</span>
                                </h1>
                            </div>
                        </div>
                        <div class="row title-mobile">
                            <div class="col-md-12">
                                <h1 class="text-center mt-0" style="font-size: 22px;">
                                    <span class="fw-700">{l s='Title-resume-mobile' mod='profileadv'}</span>
                                </h1>
                            </div>
                        </div>
                        <div class="row resume-amount content-pet-data resume-amount-desktop mt-3">
                            <div class="col-md-6 column-pet-data resume-amount-data" style="margin-right: 20px;">
                                <div class="col-md-5 text-center blue-background col-pet-info">
                                    <img class="img-fluid rounded mb-2" width="200" height="200"
                                        src="/img/pets/{$profileadvnewpetdata.img|html_entity_decode}"
                                        alt="{$profileadvnewpetdata.name}">
                                    <span class="pet-name-span font-weight-bold mt-5">
                                        {$profileadvnewpetdata.name}
                                    </span>
                                    <br>
                                    <span class="pet-raza-span font-weight-normal mt-2">
                                        {if $profileadvnewpetdata.breed}
                                            {$profileadvnewpetdata.breed}
                                        {/if}
                                    </span>
                                    <br>
                                    <span class="pet-age-span font-weight-normal mt-2">
                                        {if $profileadvnewpetdata.age}
                                            {$profileadvnewpetdata.age} {l s='age' mod='profileadv'}
                                        {/if}
                                    </span>
                                    |
                                    <span class="pet-weight-span font-weight-normal mt-2">
                                        {if $profileadvnewpetdata.weight}
                                            {$profileadvnewpetdata.weight} kg
                                        {/if}
                                    </span>
                                </div>
                                <div class="col-md-7 col-pet-info">
                                    <div class="col-md-12 mt-1 mt-lg-2 col-pet-price">
                                        <div class="fw-500" style="margin-bottom: 10px;">
                                            {l s='ration recommend' mod='profileadv'}
                                        </div>
                                        <br>
                                        <div class="pet-amount-value font-weight-bold fw-700">
                                            <span>
                                                {$profileadvnewpetdata.amount}g
                                            </span>
                                            {l s='/día*' mod='profileadv'}
                                        </div>
                                        <br>

                                        <div class="fw-500" style="margin-bottom: 10px;">
                                            {l s='amount cost' mod='profileadv'}
                                        </div>
                                        <br>
                                        <div class="pet-amount-value font-weight-bold fw-700">
                                            {assign var="daily_amount" value=($profileadvnewpetdata.amount*$profileadvproduct_recommend['daily_price'])}
                                            <span>
                                                {$daily_amount|string_format:"%.2f"}€
                                            </span>
                                            {l s='/día*' mod='profileadv'}
                                        </div>
                                        <br>
                                        {assign var="monthly_sum" value=($profileadvnewpetdata.amount*$profileadvproduct_recommend['monthly_price'])}
                                        <span>
                                            {$monthly_sum|string_format:"%.2f"}{l s='€/mes' mod='profileadv'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 column-pet-data resume-amount-recommend">
                                <div class="col-md-6 p-0">
                                    <a href="{$profileadvproduct_recommend['link']}" target="_blank">
                                        <img src="https://{$profileadvproduct_recommend['image']}"
                                            alt="{$profileadvproduct_recommend['name']}" style="border-radius: 10px;
                                    margin: 0 !important;
                                    height: 317px;
                                    width: 100%;" class="img-fluid rounded mb-2">
                                    </a>
                                </div>
                                <div class="col-md-6" style="height: 100%;">
                                    <div class="col-md-12 description-recommend">
                                        <div>
                                            <p style="font-weight: 500;">
                                                {l s='menu recommend' mod='profileadv'}
                                            </p>
                                            <p class="font-weight-bold">
                                                {$profileadvproduct_recommend['name']}
                                            </p>
                                            <p class="font-weight-normal">
                                                {l s='text-description-menu-pre' mod='profileadv'}
                                                {$profileadvproduct_recommend['name']}
                                                {l s='text-description-menu-post' mod='profileadv'}
                                            </p>
                                        </div>
                                        <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                                            <input type="hidden" name="token" value="{$static_token}">
                                            <input type="hidden" name="id_product"
                                                value="{$profileadvproduct_recommend['id_product']}" id="product_page_product_id">
                                            <input type="hidden" name="id_customization" value="0" id="product_customization_id">
                                            <input type="hidden" name="qty" value="1">
                                            <button type="button" class="btn next btn-resumen" data-button-action="add-to-cart"
                                                type="submit">
                                                {l s='Purchase' mod='profileadv'}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row description-desktop">
                            <div class="col-md-12">
                                <p class="text-center mt-3" style="color: #686868;">{l s='text-pet-resume' mod='profileadv'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                {* MOBILE *}
                {* <div class="row resume-amount resume-amount-mobile">
                    <div class="col-xs-12 resume-amount-data mt-2 blue-background">
                        <div class="row">
                            <div class="col-xs-12 text-center mt-2">
                                <img class="img-fluid rounded" src="/img/pets/{$profileadvnewpetdata.img|html_entity_decode}"
                                    alt="{$profileadvnewpetdata.name}">
                            </div>
                            <div class="col-xs-12">
                                <div class="row">
                                    <div class="col-xs-12 mt-1 mt-lg-2 text-center">
                                        <p class="font-italic">
                                            {l s='ration recommend' mod='profileadv'}
                                        </p>
                                        <p class="pet-amount-value font-weight-bold">
                                            {$profileadvnewpetdata.amount}{l s='g/día' mod='profileadv'}
                                        </p>
                                        {assign var="monthly_amount" value=($profileadvnewpetdata.amount/1000)*30}
                                        <p class="pet-sum-month font-weight-normal">
                                            {$monthly_amount|string_format:"%.2f"}{l s='Kg/mes aprox' mod='profileadv'}
                                        </p>
                                    </div>
                                </div>
                                <hr style="border-top: 1px solid #FFF;">
                                <div class="row">
                                    <div class="col-xs-12 mt-1 mt-lg-2 text-center">
                                        <p class="font-italic">
                                            {l s='amount cost' mod='profileadv'}
                                        </p>
                                        {assign var="monthly_sum" value=($monthly_amount*$profileadvproduct_recommend['price'])}
                                        <p class="pet-amount-cost-value font-weight-bold">
                                            {$monthly_sum|string_format:"%.2f"}{l s='€/mes' mod='profileadv'}</p>
                                        {assign var="daily_amount" value=($monthly_sum/30)}
                                        <p class="pet-sum-cost-month font-weight-normal">
                                            {$daily_amount|string_format:"%.2f"}{l s='€/día aprox' mod='profileadv'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 resume-amount-recommend blue-background mt-5">
                        <div class="row mt-2">
                            <div class="col-xs-12">
                                <div class="row mt-5">
                                    <div class="col-md-12">
                                        <p style="font-weight: 500;" class="px-1">
                                            {l s='menu recommend' mod='profileadv'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <a href="{$profileadvproduct_recommend['link']}" target="_blank"><img
                                            src="https://{$profileadvproduct_recommend['image']}"
                                            alt="{$profileadvproduct_recommend['name']}">
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mt-2">
                                    <p style="font-weight: 500;" class="px-1">
                                        <a class="btn btn-primary button-white"
                                            href="{$profileadvproduct_recommend['link']}">{l s='I want it' mod='profileadv'}</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> *}
                <div class="row resume-amount resume-amount-mobile">
                    <div class="row resume-amount content-pet-data resume-amount-mobile">
                        <div class="column-pet-data resume-amount-data">
                            <div class="text-center blue-background col-pet-info">
                                <img class="img-fluid rounded mb-2" width="140" height="140"
                                    src="/img/pets/{$profileadvnewpetdata.img|html_entity_decode}"
                                    alt="{$profileadvnewpetdata.name}">
                                <div class="text-justify">
                                    <span class="pet-name-span font-weight-bold fw-700 mt-5">
                                        {$profileadvnewpetdata.name}
                                    </span>
                                    <br>
                                    <span class="pet-raza-span font-weight-normal fw-500 mt-2">
                                        {if $profileadvnewpetdata.breed}
                                            {$profileadvnewpetdata.breed}
                                        {/if}
                                    </span>
                                    <br>
                                    <span class="pet-age-span font-weight-normal fw-300 mt-2">
                                        {if $profileadvnewpetdata.age}
                                            {$profileadvnewpetdata.age} {l s='age' mod='profileadv'}
                                        {/if}
                                    </span>
                                    <span style="color: black;">
                                        |
                                    </span>
                                    <span class="pet-weight-span font-weight-normal fw-300 mt-2">
                                        {if $profileadvnewpetdata.weight}
                                            {$profileadvnewpetdata.weight} kg
                                        {/if}
                                    </span>
                                </div>
                            </div>
                            <div class="col-pet-info">
                                <div class="col-md-12 mt-1 mt-lg-2 col-pet-price">
                                    <div class="fw-500" style="margin-bottom: 10px;">
                                        {l s='ration recommend' mod='profileadv'}
                                    </div>
                                    <br>
                                    <div class="pet-amount-value font-weight-bold fw-700">
                                        <span>
                                            {$profileadvnewpetdata.amount}g
                                        </span>
                                        {l s='/día*' mod='profileadv'}
                                    </div>
                                    <br>

                                    <div class="fw-500" style="margin-bottom: 10px;">
                                        {l s='amount cost' mod='profileadv'}
                                    </div>
                                    <br>
                                    <div class="pet-amount-value font-weight-bold fw-700">
                                        {assign var="daily_amount" value=($profileadvnewpetdata.amount*$profileadvproduct_recommend['daily_price'])}
                                        <span>
                                            {$daily_amount|string_format:"%.2f"}€
                                        </span>
                                        {l s='/día*' mod='profileadv'}
                                    </div>
                                    <br>
                                    {assign var="monthly_sum" value=($profileadvnewpetdata.amount*$profileadvproduct_recommend['monthly_price'])}
                                    <span>
                                        {$monthly_sum|string_format:"%.2f"}{l s='€/mes' mod='profileadv'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="column-pet-data resume-amount-recommend">
                            <div class=" p-0">
                                <a href="{$profileadvproduct_recommend['link']}" target="_blank">
                                    <img src="https://{$profileadvproduct_recommend['image']}"
                                        alt="{$profileadvproduct_recommend['name']}" style="border-radius: 10px;
                            margin: 0 !important;
                            width: 100%;" class="img-fluid rounded mb-2">
                                </a>
                            </div>
                            <div class="description-recommend text-center mt-2">
                                <div>
                                    <p style="font-weight: 500;">
                                        {l s='menu recommend' mod='profileadv'}
                                    </p>
                                    <p class="font-weight-bold" style="font-size: 20px;">
                                        {$profileadvproduct_recommend['name']}
                                    </p>
                                    <p class="font-weight-normal">
                                        {l s='text-description-menu-pre' mod='profileadv'}
                                        {$profileadvproduct_recommend['name']}
                                        {l s='text-description-menu-post' mod='profileadv'}
                                    </p>
                                </div>
                                <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                                    <input type="hidden" name="token" value="{$static_token}">
                                    <input type="hidden" name="id_product" value="{$profileadvproduct_recommend['id_product']}"
                                        id="product_page_product_id">
                                    <input type="hidden" name="id_customization" value="0" id="product_customization_id">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="button" class="btn next btn-resumen" data-button-action="add-to-cart"
                                        type="submit">
                                        {l s='Purchase' mod='profileadv'}
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-center" style="color: #686868;line-height: 30px;">
                            {l s='text-pet-resume' mod='profileadv'}</p>
                    </div>
                </div>
            {/if}
            <div class="row mt-5">
                <div class="col-md-12">
                    <lt class="lt--mac-os">
                        <lt class="lt-highlighter__wrapper">
                            <lt class="lt-highlighter__scroll-element">
                            </lt>
                        </lt>
                    </lt>
                    <a class="btn btn-primary button-blue" href="/calculadora">{l s='Show my pets' mod='profileadv'}</a>
                </div>
            </div>
            <div class="row mt-5 blue-background tips">
                <div class="col-md-12">
                    <p class="text-center text-title pt-2 pb-2 text-white">
                        {l s='Title-tips-1' mod='profileadv'}{$profileadvnewpetdata.name}{l s='Title-tips-2' mod='profileadv'}
                    </p>
                    <div class="row">
                        <div class="col-xs-6 col-md-3">
                            <div class="card">
                                <img class="card-img-top" src="/modules/profileadv/views/img/tips/1.png" alt="Card image cap">
                                <div class="card-body">
                                    <p class="mt-2 text-white">{l s='tip-1' mod='profileadv'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="card">
                                <img class="card-img-top" src="/modules/profileadv/views/img/tips/2.png" alt="Card image cap">
                                <div class="card-body">
                                    <p class="mt-2 text-white">{l s='tip-2' mod='profileadv'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="card">
                                <img class="card-img-top" src="/modules/profileadv/views/img/tips/3.png" alt="Card image cap">
                                <div class="card-body">
                                    <p class="mt-2 text-white">{l s='tip-3' mod='profileadv'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="card">
                                <img class="card-img-top" src="/modules/profileadv/views/img/tips/4.png" alt="Card image cap">
                                <div class="card-body">
                                    <p class="mt-2 text-white">{l s='tip-4' mod='profileadv'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-5 help">
                <div class="col-md-12">
                    <p class="text-center text-title mt-2 text-black">
                        {l s='Title-help' mod='profileadv'}
                    </p>
                    <p class="text-center mt-2">
                        {l s='text-help' mod='profileadv'}
                    </p>
                    <a class="btn btn-primary button-blue mt-2"
                        href="https://wa.me/34692194283/?text=">{l s='Contact' mod='profileadv'}</a>
                </div>
            </div>
            <div class="row mt-5 blue-background carousel">
                <div class="col-md-12">
                    <p class="text-center text-title pt-2 pb-2 text-white">{l s='Carousel title' mod='profileadv'}</p>
                    <center>
                        <div id="carousel">
                            {foreach from=$profileadvproduct_list item=item}
                                <div class="carousel-cell">
                                    <a href="{$item.link}" target="_blank"><img src="https://{$item.image}" alt="{$item.name}"></a>
                                </div>
                            {/foreach}
                        </div>
                    </center>
                </div>
            </div>
        </div>
        {if $profileadvnewpetdata.is_new}
            {literal}
                <script>
                    let id_pet = new String("{/literal}{$profileadvnewpetdata.reference}{literal}");
                    let id_customer = parseInt({/literal}{$profileadvnewpetdata.id_customer}{literal});
                    let customer_email = new String("{/literal}{$profileadvnewpetdata.customer_email}{literal}");

                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        event: "new_pet",
                        ecommerce: {
                            items: [{
                                id_pet: id_pet.toString(),
                                id_customer: id_customer,
                                customer_email: customer_email.toString()
                            }]
                        }
                    });
                </script>
            {/literal}
        {/if}
    {else}
        <div class="row" id="add-pet-form">
            <div class="container">
                <div style="display: block;">
                    <form method="post" action="{$profileadvajax_profile_url nofilter}" enctype="multipart/form-data"
                        id="user_profile_photo" name="user_profile_photo" {if $profileadvis_chrome == 0}onsubmit="return false;"
                        {/if}>

                        <input type="hidden" name="action" value="addpet" />

                        {if isset($profileadvstatus_error) && $profileadvstatus_error == 1}
                            <div class="bootstrap">
                                <div class="alert alert-warning">
                                    <button type="button" type="button" data-dismiss="alert" class="close">×</button>
                                    <strong>{l s='Error' mod='profileadv'}:</strong>&nbsp;{$profileadvmessage_error|html_entity_decode}
                                    &zwnj;
                                </div>
                            </div>
                        {/if}

                        <section class="pet-type text-center" data-step='1'>
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-500">{l s='Step1-title-pre' mod='profileadv'}</span>
                                    <span class="fw-700">{l s='Step1-title-post' mod='profileadv'}</span>
                                </h1>
                                <p class="fw-700 black mt-2">{l s='Step1-subtitle' mod='profileadv'}
                                </p>
                            </div>

                            <img class="img-fluid inputType mt-2 mr-1"
                                src="/modules/profileadv/views/img/wizard/type/dog-default.png" alt="Perro" data-value="1">
                            <img class="img-fluid inputType mt-2 ml-1"
                                src="/modules/profileadv/views/img/wizard/type/cat-default.png" alt="Gato" data-value="2">

                            <input type="hidden" id="inputType" class="form-control" name="pet-type">
                        </section>

                        <!-- section -->

                        <section data-step='2' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-700">{l s='Step2-title-pre' mod='profileadv'}</span>
                                    <span class="fw-500">{l s='Step2-title-post' mod='profileadv'}</span>
                                </h1>
                                <p class="fw-700 black mt-2">{l s='Step2-subtitle' mod='profileadv'}</p>
                            </div>
                            <div class="text-center" style="padding: 0% 20%;">
                                <div class="row" style="padding: 0% 20%;">
                                    <input type="text" class="form-control" id="inputName" name="pet-name" required style="">
                                </div>
                                <div class="row select-images">
                                    <p class="input-title select-genre">
                                        {l s='is' mod='profileadv'}</p>
                                    <div class="input-images">
                                        <img class="img-fluid inputGenre mr-md-1"
                                            src="/modules/profileadv/views/img/wizard/genre/wizard_female.png" alt="Guapa"
                                            data-value="2">
                                        <img class="img-fluid inputGenre ml-md-1"
                                            src="/modules/profileadv/views/img/wizard/genre/wizard_male.png" alt="Guapo"
                                            data-value="1">
                                    </div>
                                    <input type="hidden" id="inputGenre" class="form-control" name="pet-genre">
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous"
                                    data-step='1'>{l s='Previous' mod='profileadv'}</button>
                                <button type="button" class="btn next" data-step="3">{l s='Next' mod='profileadv'}</button>
                            </div>
                        </section>

                        <!-- section -->

                        <section data-step='3' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-500">{l s='Step3-title-pre' mod='profileadv'}</span>
                                    <br>
                                    <span class="fw-700">{l s='Step3-title-post' mod='profileadv'}</span>
                                </h1>
                                <p class="fw-700 black mt-2"><span class="pet-name-span"></span>
                                    {l s='Step3-subtitle' mod='profileadv'}</p>
                            </div>
                            <div class="text-center">
                                <div class="row">
                                    <input type="date" class="form-control" id="inputBirth" name="pet-birth"
                                        max="{$profileadvcurrentdate}" min="{$profileadvmaxolddate}" required>
                                </div>
                                <div class="row select-button">
                                    <p class="input-title">
                                        {l s='...' mod='profileadv'}</p>
                                    <select class="form-control pet-esterilized" id="inputEsterilized" name="pet-esterilized">
                                        <option value="1">{l s='Is esterelized' mod='profileadv'}</option>
                                        <option value="2">{l s='Is not esterilized' mod='profileadv'}</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <p class="input-title select-genre"> {l s='Breed is' mod='profileadv'}</p>
                                    <select id="inputDogBreed" class="form-control pet-breed" name="pet-breed-dog">
                                        {foreach from=$profileadvdogbreedlist item=item key=key}
                                            <option value="{$key}">{$item}</option>
                                        {/foreach}
                                    </select>
                                    <select id="inputCatBreed" class="form-control pet-breed" name="pet-breed-cat"
                                        style="display: none;">
                                        {foreach from=$profileadvcatbreedlist item=item key=key}
                                            <option value="{$key}">{$item}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="row">
                                    <p class="input-title"> {l s='pet characteristic' mod='profileadv'}</p>
                                    <select class="form-control">
                                        <option>{l s='Headstrong' mod='profileadv'}</option>
                                        <option>{l s='Indulged' mod='profileadv'}</option>
                                        <option>{l s='Glutton' mod='profileadv'}</option>
                                        <option>{l s='Faithful' mod='profileadv'}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous"
                                    data-step='2'>{l s='Previous' mod='profileadv'}</button>
                                <button type="button" class="btn next" data-step="4">{l s='Next' mod='profileadv'}</button>
                            </div>
                        </section>

                        <!-- section -->

                        <section data-step='4' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-500">{l s='Step4-title-pre' mod='profileadv'}</span>
                                    <span class="fw-700">{l s='Step4-title-post' mod='profileadv'}</span>
                                </h1>
                                <p class="fw-500 black mt-2">{l s='Step4-subtitle' mod='profileadv'}</p>
                            </div>
                            <div class="text-center mt-3" style="padding: 0% 20%;">
                                <div class="row" style="padding: 0 30%;">
                                    <p class="input-title fw-700"> {l s='Weight is' mod='profileadv'} <span
                                            class="pet-name-span"></span>{l s='?' mod='profileadv'} (kg)</p>
                                    <input type="number" class="form-control" id="inputWeight" name="pet-weight" step="0.01"
                                        max='90' required>
                                </div>

                                <div class="row" style="padding: 0 20%;">
                                    <p class="input-title fw-700"> {l s='Desired Weight pre' mod='profileadv'} <span
                                            class="pet-name-span"></span>{l s='Desired weight post' mod='profileadv'}
                                        (kg)</p>
                                    <div class="wrap-button select-button" id="desired-weight-options">
                                        <button type="button" data-value="1">{l s='Gain' mod='profileadv'}</button>
                                        <button type="button" data-value="1">{l s='Lose' mod='profileadv'}</button>
                                        <button type="button" data-value="3">{l s='Perfect' mod='profileadv'}</button>
                                    </div>
                                </div>
                                <div class="row mt-2 hidden" id="desired-weight-row" style="padding: 0 30%;">
                                    <input type="number" class="form-control" id="inputDesiredWeight" name="pet-desired-weight"
                                        step="0.01" max='90' required>
                                    <p class="mt-2 blue-label">{l s='Desired weight label' mod='profileadv'}</p>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous"
                                    data-step='3'>{l s='Previous' mod='profileadv'}</button>
                                <button type="button" class="btn next" data-step="5">{l s='Next' mod='profileadv'}</button>
                            </div>
                        </section>

                        <!-- section -->

                        <section data-step='5' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-700">{l s='Step5-title-pre' mod='profileadv'}</span>
                                    <br>
                                    <span class="fw-500">{l s='Step5-title-post' mod='profileadv'}</span>
                                </h1>
                            </div>
                            <div class="text-center">
                                <div class="row">
                                    <p class="input-title">
                                        {l s='Activity' mod='profileadv'} <span
                                            class="pet-name-span"></span>{l s='?' mod='profileadv'}</p>
                                    <div class="activity-cards">
                                        <div class="row">
                                            <div class="col-xs-6 col-md-6 col-lg-3">
                                                <div class="card" data-value='4'>
                                                    <img class="card-img-top img-fluid"
                                                        src="/modules/profileadv/views/img/wizard/activity/1_dog.png"
                                                        alt="{l s='-1 hora' mod='profileadv'}">
                                                    <div class="card-body mt-1">
                                                        <p class="card-text">
                                                            <span>{l s='Activity1-span' mod='profileadv'}</span>{l s='Activity1' mod='profileadv'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-md-6 col-lg-3">
                                                <div class="card" data-value='3'>
                                                    <img class="card-img-top img-fluid"
                                                        src="/modules/profileadv/views/img/wizard/activity/2_dog.png"
                                                        alt="{l s='-1 hora' mod='profileadv'}">
                                                    <div class="card-body mt-1">
                                                        <p class="card-text">
                                                            <span>{l s='Activity2-span' mod='profileadv'}</span>{l s='Activity2' mod='profileadv'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-md-6 col-lg-3">
                                                <div class="card" data-value='2'>
                                                    <img class="card-img-top img-fluid"
                                                        src="/modules/profileadv/views/img/wizard/activity/3_dog.png"
                                                        alt="{l s='-1 hora' mod='profileadv'}">
                                                    <div class="card-body mt-1">
                                                        <p class="card-text">
                                                            <span>{l s='Activity3-span' mod='profileadv'}</span>{l s='Activity3' mod='profileadv'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-md-6 col-lg-3">
                                                <div class="card" data-value='1'>
                                                    <img class="card-img-top img-fluid"
                                                        src="/modules/profileadv/views/img/wizard/activity/4_dog.png"
                                                        alt="{l s='-1 hora' mod='profileadv'}">
                                                    <div class="card-body mt-1">
                                                        <p class="card-text">
                                                            <span>{l s='Activity4-span' mod='profileadv'}</span>{l s='Activity4' mod='profileadv'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="inputActivity" class="form-control" name="pet-activity">
                                    </div>
                                </div>
                                <div class="row">
                                    <p class="input-title">
                                        <span>{l s='Feeding' mod='profileadv'} </span><span class="pet-name-span"></span>
                                        <span>{l s='is...' mod='profileadv'}</span>
                                    </p>
                                    <div class="pet-feeding">
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='1'
                                                src="/modules/profileadv/views/img/wizard/feeding/1.png"
                                                alt="{l s='Feed' mod='profileadv'}">
                                        </p>
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='2'
                                                src="/modules/profileadv/views/img/wizard/feeding/2.png"
                                                alt="{l s='Cooked' mod='profileadv'}">
                                        </p>
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='3'
                                                src="/modules/profileadv/views/img/wizard/feeding/3.png"
                                                alt="{l s='Barf' mod='profileadv'}">
                                        </p>
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='4'
                                                src="/modules/profileadv/views/img/wizard/feeding/4.png"
                                                alt="{l s='Wet' mod='profileadv'}">
                                        </p>
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='5'
                                                src="/modules/profileadv/views/img/wizard/feeding/5.png"
                                                alt="{l s='Dehydrated' mod='profileadv'}">
                                        </p>
                                        <p>
                                            <img class="img-fluid img-feeding" data-value='6'
                                                src="/modules/profileadv/views/img/wizard/feeding/6.png"
                                                alt="{l s='Lactation' mod='profileadv'}">
                                        </p>
                                        <input type="hidden" id="inputFeeding" class="form-control" name="pet-feeding">
                                    </div>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous"
                                    data-step='4'>{l s='Previous' mod='profileadv'}</button>
                                <button type="button" class="btn next" data-step="6">{l s='Next' mod='profileadv'}</button>
                            </div>
                        </section>

                        <!-- section -->

                        <section data-step='6' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    <span class="fw-500">{l s='Step6-title-pre' mod='profileadv'}</span>
                                    <span class="fw-700">{l s='Step6-title-post' mod='profileadv'}</span>
                                </h1>
                            </div>
                            <div class="text-center">
                                <div>
                                    <div class="enable-option-button">
                                        <p class="input-title">{l s='¿' mod='profileadv'}
                                            <span class="pet-name-span"></span>{l s='Has Patologies?' mod='profileadv'}
                                        </p>
                                        <button data-value='1' type="button">{l s='Yes' mod='profileadv'}</button>
                                        <button data-value='0' class="active-button"
                                            type="button">{l s='No' mod='profileadv'}</button>
                                    </div>

                                    <div class="options-list" style="display: none;">
                                        <ul class="form-check form-check-inline text-left">
                                            {foreach from=$profileadvpathologieslist item=item key=key}
                                                <li>
                                                    <input type="checkbox" class="form-check-input" id="inputPathology_{$key}"
                                                        name="pet-pathology[]" value="{$key}" {if $key == "1"} checked="true" {/if}>
                                                    <label class="form-check-label" for="inputPathology_{$key}"
                                                        style="text-align: left;">{$item}</label>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                                <div>
                                    <div class="enable-option-button mt-5">
                                        <p class="input-title">
                                            {l s='Has Allergies?' mod='profileadv'}</p>
                                    </div>

                                    <div class="options-list">
                                        <ul class="form-check form-check-inline text-left">
                                            {foreach from=$profileadvallergieslist item=item key=key}
                                                <li>
                                                    <input type="checkbox" class="form-check-input" id="inputAllergies_{$key}"
                                                        name="pet-allergies[]" value="{$key}" {if $key == "1"} checked="true" {/if}>
                                                    <label class="form-check-label" for="inputAllergies_{$key}"
                                                        style="text-align: left;">{$item}</label>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous"
                                    data-step='5'>{l s='Previous' mod='profileadv'}</button>
                                <button type="button" class="btn next" data-step="7">{l s='Next' mod='profileadv'}</button>
                            </div>
                        </section>

                        <!-- section -->

                        <section data-step='7' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>{l s='Step7-title' mod='profileadv'}</h1>
                                <p>{l s='Step7-subtitle' mod='profileadv'}</p>
                            </div>
                            <div class="text-center">
                                <div class="row">
                                    <div class="row enable-option-button">
                                        <p class="input-title">
                                            {l s='Foto?' mod='profileadv'}<span class="pet-name-span"></span>
                                        </p>
                                    </div>

                                    <div class="options-list">
                                        <div class="b-info-block">
                                            <div class="b-body">
                                                <dl class="b-photo-ed">
                                                    <dt></dt>
                                                    <dd class="text-center">
                                                        <div class="b-avatar">
                                                            <a href="javascript:void(0)" onclick="profileadv_change(1)"
                                                                title="{l s='Edit/Change Photo' mod='profileadv'}">
                                                            </a>
                                                        </div>
                                                        <div class="file-select">
                                                            <a href="javascript:void(0)" onclick="profileadv_change(1)"
                                                                id="profileadv_edit_button"
                                                                title="{l s='Edit/Change Photo' mod='profileadv'}">
                                                                {l s='Añadir imagen' mod='profileadv'}</a> <i
                                                                class="fa fa-pencil-square-o" aria-hidden="true"></i>

                                                            <input type="file" name="profileadvimg" id="profileadvimg" />
                                                        </div>
                                                        <div class="alert alert-info">
                                                            {l s='Formatos permitidos' mod='profileadv'} *.png; *.jpeg;
                                                            <i class="fa fa-check" aria-hidden="true"></i>
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous" data-step='6'>{l s='Previous' mod='profileadv'}</button>
                                {if $logged}
                                    <button type="submit" class="btn btn-submit" id="submit-button" onClick="submitClicked()">{l s='Finalizar' mod='profileadv'}</button>
                                {else}
                                    <button type="button" class="btn next" data-step='8'>{l s='Next' mod='profileadv'}</button>
                                {/if}
                            </div>
                        </section>

                        {if !$logged}
                        <!-- Email step for guests -->
                        <section data-step='8' class="data-step" style="display: none;">
                            <div class="profileadv-add-header">
                                <h1>
                                    {l s='¡El menú ideal de tu peludo' mod='profileadv'}
                                    <span class="pet-name-span"></span>
                                    {l s='ya está casi listo!' mod='profileadv'}
                                </h1>
                                <p>{l s='En el siguiente paso verás el menú, la ración y el precio ideal para tu peque. Te enviaremos por correo un informe más completo con todos los detalles sobre su alimentación.' mod='profileadv'}</p>
                                <p>
                                    {l s='El correo electrónico del humano de ' mod='profileadv'}<span class="pet-name-span"></span>{l s=' es...' mod='profileadv'}
                                </p>
                            </div>
                            <div class="text-center" style="padding: 0% 20%;">
                                <input type="email" class="form-control" id="inputEmail" name="customer-email" />
                                <p class="mt-2">
                                    {l s='Al seguir, aceptarás los ' mod='profileadv'}
                                    <a href="/terminos">{l s=' términos ' mod='profileadv'}</a>
                                    {l s='y' mod='profileadv'}
                                    <a href="/condiciones">{l s=' condiciones ' mod='profileadv'}</a>
                                    {l s='para recibir los mejores consejos sobre la alimentación de tu peque.' mod='profileadv'}
                                </p>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="btn previous" data-step='7'>{l s='Previous' mod='profileadv'}</button>
                                <button type="submit" class="btn btn-submit" id="submit-button" onClick="submitClicked()">{l s='Finalizar' mod='profileadv'}</button>
                            </div>
                        </section>
                        {/if}
                    </form>
                </div>
            </div>
        </div>
    {/if}
{/block}