<div class="container">
    <div class="row">
        <div class="col-12" style="display: flex;flex-wrap: wrap;">
            <div class="jumbotron text-center"
                style="background: #FFF;padding: 1%;margin:1%;max-width: 30%;flex-grow: 1;">
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="petName">{l s='Nombre de la mascota' mod='profileadv'}</label>
                        <input type="text" class="form-control" id="petName" name="petName" aria-describedby="emailHelp"
                            placeholder="{l s='Coco' mod='profileadv'}" autocomplete="off">
                    </div>
                    <button type="submit" id="searchPet" name="searchPet"
                        class="btn btn-primary">{l s='Buscar' mod='profileadv'}</button>
                </form>
            </div>
            <div class="jumbotron text-center"
                style="background: #FFF;padding: 1%;margin:1%;max-width: 30%;flex-grow: 1;">
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="customerEmail">{l s='Email del cliente' mod='profileadv'}</label>
                        <input type="text" class="form-control" id="customerEmail" name="customerEmail"
                            aria-describedby="emailHelp" placeholder="{l s='correo@correo.es' mod='profileadv'}"
                            autocomplete="off">
                    </div>
                    <button type="submit" id="searchEmail" name="searchEmail"
                        class="btn btn-primary">{l s='Buscar' mod='profileadv'}</button>
                </form>
            </div>
            {if $petsWAmount > 0}
                <div class="jumbotron jumbotron-fluid alert alert-danger text-center"
                    style="padding: 1%;margin:1%;max-width: 30%;flex-grow: 1;">
                    <form action="#" method="POST">
                        <div class="form-group">
                            <h2 style="font-size: 35px">{$petsWAmount}</h2>
                            <label for="noAmount">{l s='Mascotas sin cantidad asignada' mod='profileadv'}</label>
                            <input type="text" class="form-control" id="noAmount" name="noAmount"
                                aria-describedby="emailHelp" value="0" autocomplete="off" style="display: none;">
                        </div>
                        <button type="submit" id="noAmountData" name="noAmountData"
                            class="btn btn-primary">{l s='Filtrar' mod='profileadv'}</button>
                    </form>
                </div>
            {/if}
            {if $petsWCustomer > 0}
                <div class="jumbotron jumbotron-fluid alert alert-danger text-center"
                    style="padding: 1%;margin:1%;max-width: 30%;flex-grow: 1;">
                    <form action="#" method="POST">
                        <div class="form-group">
                            <h2 style="font-size: 35px">{$petsWCustomer}</h2>
                            <label for="noCustomer">{l s='Mascotas sin cliente asignado' mod='profileadv'}</label>
                            <input type="text" class="form-control" id="noCustomer" name="noCustomer"
                                aria-describedby="emailHelp" value="0" autocomplete="off" style="display: none;">
                        </div>
                        <button type="submit" id="noCustomerData" name="noCustomerData"
                            class="btn btn-primary">{l s='Filtrar' mod='profileadv'}</button>
                    </form>
                </div>
            {/if}
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="admin-pet-list" style="background: #FFF; padding: 1%">
            {if !$showPetsWCustomer}
                <table class="table order">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;"></th>
                            <th scope="col" style="text-align: center;">{l s='Name' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">
                                {l s='Genre' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">
                                {l s='Type' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">
                                {l s='Amount' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">
                                {l s='Client' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">
                                {l s='Message' mod='profileadv'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$pets item=item}
                            <tr>
                                <th scope="row" style="text-align: center; cursor:pointer"
                                    onclick="window.open('{$link->getAdminLink('Adminprofileadv', true)}&reference={$item['reference']}&customer={$item['id_customer']}', '_blank')">
                                    <img src="/img/pets/{$item['avatar_thumb']}"
                                        style="width: 50px;height: 50px;border-radius: 50%;object-fit: cover;">
                                </th>
                                <td style="text-align: center; cursor: pointer"><a
                                        href="{$link->getAdminLink('Adminprofileadv', true)}&reference={$item['reference']}&customer={$item['id_customer']}"
                                        target="_blank" style="text-transform: capitalize">{$item['name']} &#8599;</a></td>
                                <td style="text-align: center;">
                                    {if $item['genre'] == '1'}
                                        {l s='Macho' mod='profileadv'}
                                    {else}
                                        {l s='Hembra' mod='profileadv'}
                                    {/if}
                                </td>
                                <td style="text-align: center;">
                                    {if $item['type'] == '1'}
                                        {l s='Perro' mod='profileadv'}
                                    {else}
                                        {l s='Gato' mod='profileadv'}
                                    {/if}
                                </td>
                                <td {if $item['amount'] <= 0}class="alert alert-danger" {/if}
                                    style="text-align: center; color: {if $item['amount'] > 0}#53d572{else}red{/if};">
                                    {$item['amount']}</td>
                                <td style="text-align: center;">
                                    {if $item['customer'] > 0}
                                        <a target="_blank" style="text-transform: capitalize"
                                            href="{$item['customer_href']}">{$item['customer']}
                                            &#8599;</a>
                                    {/if}
                                </td>
                                <td style="text-align: center;">
                                    <p>{$item['message']}</p>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {else}
                <table class="table order">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;"></th>
                            <th scope="col" style="text-align: center;">{l s='Name' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Genre' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Type' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Amount' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Client' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Date Add' mod='profileadv'}</th>
                            <th scope="col" style="text-align: center;">

                                {l s='Sended Email' mod='profileadv'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$pets item=item}
                            <tr>
                                <th scope="row" style="text-align: center;">
                                    <img src="/img/pets/{$item['avatar_thumb']}"
                                        style="width: 50px;height: 50px;border-radius: 50%;object-fit: cover;">
                                </th>
                                <td style="text-align: center; cursor: pointer"><a
                                        href="{$link->getAdminLink('Adminprofileadv', true)}&reference={$item['reference']}&customer=1"
                                        target="_blank" style="text-transform: capitalize">{$item['name']} &#8599;</a></td>
                                <td style="text-align: center;">
                                    {if $item['genre'] == '1'}
                                        {l s='Macho' mod='profileadv'}
                                    {else}
                                        {l s='Hembra' mod='profileadv'}
                                    {/if}
                                </td>
                                <td style="text-align: center;">
                                    {if $item['type'] == '1'}
                                        {l s='Perro' mod='profileadv'}
                                    {else}
                                        {l s='Gato' mod='profileadv'}
                                    {/if}
                                </td>
                                <td {if $item['amount'] <= 0}class="alert alert-danger" {/if}
                                    style="text-align: center; color: {if $item['amount'] > 0}#53d572{else}red{/if};">
                                    {$item['amount']}</td>
                                <td style="text-align: center;">
                                    <p>{$item['message']}</p>
                                </td>
                                <td style="text-align: center;">
                                    <p>{$item['date_add']|date_format:'%d-%m-%y %H:%M:%S'}</p>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="updateSendedEmail('{$item['reference']}',1)"
                                            {if $item['sended_email'] >= 1}disabled{/if}>1</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="updateSendedEmail('{$item['reference']}',2)"
                                            {if $item['sended_email'] >= 2}disabled{/if}>2</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="updateSendedEmail('{$item['reference']}',3)"
                                            {if $item['sended_email'] >= 3}disabled{/if}>3</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="updateSendedEmail('{$item['reference']}',4)"
                                            {if $item['sended_email'] >= 4}disabled{/if}>4</button>
                                        <button type="button" class="btn btn-secondary"
                                            onclick="updateSendedEmail('{$item['reference']}',5)"
                                            {if $item['sended_email'] >= 5}disabled{/if}>5</button>
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {l s='Actions' mod='profileadv'}
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <a class="dropdown-item" href="{$item['pet_href']}"
                                                    target="_blank">{l s='Show url' mod='profileadv'}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
    </div>
    <div id="clipboard"></div>
</div>