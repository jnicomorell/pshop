<div class="col-12" style="flex-basis: auto;">
  <div id="mipets">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            {l s="Listado de mascotas" mod='profileadv'} <span
              class="badge badge-primary rounded">{count($petList)}</span>
            <a class="btn btn-primary" style="float:right" target="_blank"
              href="{Context::getContext()->link->getAdminLink('AdminProfileAdvAdd', true)}&customer={$id_customer}">
              <i class="icon-plus-sign"></i>
              {l s="Add" mod='profileadv'}
            </a>
          </div>
          <div class="card-body clearfix">
            <table class="table text-center" style="cursor: pointer">
              <thead class="thead-dark">
                <tr>
                  <th class="text-center"></th>
                  <th class="text-center">#</th>
                  <th class="text-center">{l s="Nombre" mod='profileadv'}</th>
                  <th class="text-center">{l s="Género" mod='profileadv'}</th>
                  <th class="text-center">{l s="F. nacimiento" mod='profileadv'}</th>
                  <th class="text-center">{l s="Peso" mod='profileadv'}</th>
                  <th class="text-center">{l s="Observaciones" mod='profileadv'}</th>
                  <th class="text-center">{l s="Gramos/día" mod='profileadv'}</th>
                  <th class="text-center"></th>
                </tr>
              </thead>
              <tbody>
                {foreach from=$petList item=curr}
                  <tr>
                    <td>
                      {if isset($curr['message']) > 0 && !empty($curr['message'])}
                        <button onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')" type="button"
                          class="btn btn-info" title="Comentario para {$curr['name']}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path
                              d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                          </svg>
                        </button>
                      {/if}
                    </td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      <img src="/img/pets/{$curr['img']}"
                        style="width: 50px;height: 50px;border-radius: 50%;padding: 1%; object-fit: cover" />
                    </td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {$curr['name']}</td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {if $curr['genre'] == 1}
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px"
                          height="20px" viewBox="0 0 20 20" version="1.1">
                          <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="Dribbble-Light-Preview" transform="translate(-60.000000, -2079.000000)" fill="#000000">
                              <g id="icons" transform="translate(56.000000, 160.000000)">
                                <path
                                  d="M11,1937.005 C8.243,1937.005 6,1934.762 6,1932.005 C6,1929.248 8.243,1927.005 11,1927.005 C13.757,1927.005 16,1929.248 16,1932.005 C16,1934.762 13.757,1937.005 11,1937.005 L11,1937.005 Z M16,1919 L16,1921 L20.586,1921 L15.186,1926.402 C14.018,1925.527 12.572,1925.004 11,1925.004 C7.134,1925.004 4,1928.138 4,1932.004 C4,1935.87 7.134,1939.005 11,1939.005 C14.866,1939.005 18,1935.871 18,1932.005 C18,1930.433 17.475,1928.987 16.601,1927.818 L22,1922.419 L22,1927 L24,1927 L24,1919 L16,1919 Z"
                                  id="male-[#1364]">
                                </path>
                              </g>
                            </g>
                          </g>
                        </svg>
                      {else}
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px"
                          height="20px" viewBox="-3 0 20 20" version="1.1">
                          <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="Dribbble-Light-Preview" transform="translate(-103.000000, -2079.000000)" fill="#000000">
                              <g id="icons" transform="translate(56.000000, 160.000000)">
                                <path
                                  d="M54.010058,1930.97067 C52.6753909,1930.97067 51.421643,1930.45194 50.4775859,1929.51025 C47.3327267,1926.36895 49.5904718,1920.99511 54.010058,1920.99511 C58.4266471,1920.99511 60.6903863,1926.36595 57.5425301,1929.51025 C56.5984729,1930.45194 55.344725,1930.97067 54.010058,1930.97067 M58.9411333,1930.92079 C63.3617184,1926.50661 60.1768991,1919 54.007061,1919 C47.8512088,1919 44.6294265,1926.50661 49.0510106,1930.92079 C50.1609021,1932.02908 51.9840813,1932.67949 52.9830836,1932.88598 L52.9830836,1935.00978 L49.9860767,1935.00978 L49.9860767,1937.00489 L52.9830836,1937.00489 L52.9830836,1939 L54.9810882,1939 L54.9810882,1937.00489 L57.9780951,1937.00489 L57.9780951,1935.00978 L54.9810882,1935.00978 L54.9810882,1932.88598 C56.9790928,1932.67949 57.8302427,1932.02908 58.9411333,1930.92079"
                                  id="female-[#1363]">
                                </path>
                              </g>
                            </g>
                          </g>
                        </svg>
                      {/if}
                    </td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {$curr['birth']|date_format:"%d/%m/%y"} ( {if $curr['ageyears'] > 0}{$curr['ageyears']}
                        {l s="años" mod="profileadv"}
                      {else}{$curr['agemonths']}
                        {l s="meses" mod="profileadv"}
                      {/if})
                    </td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {$curr['weight']}Kg</td>
                    <td onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {if count($curr['pathology']) > 0}
                        <p class="text-info"><i class="icon-medkit"></i>
                          <span>{l s="Mascota con patologías" mod="profileadv"}</span>
                        </p>
                      {/if}
                      {if count($curr['allergies']) > 0}
                        <p class="text-info"><i class="icon-stethoscope"></i>
                          <span>{l s="Mascota con alergias" mod="profileadv"}</span>
                        </p>
                      {/if}
                    </td>
                    <td style="{if $curr['amount'] > 0}color: #72c279;{else}color: #e08f95;{/if} font-weight: bold; "
                      onclick="window.open('{$curr['edit_url']}&customer={$id_customer}', '_blank')">
                      {$curr['amount']}
                    </td>
                    <td>
                      <a class="btn btn-default delete-pet" onclick="deletePet('{$curr['reference']}',{$id_customer})"
                        data-pet-ref="{$curr['reference']}" data-id-customer="{$id_customer}">
                        <i class="icon-check-minus"></i>
                        {l s="Delete" mod="profileadv"}
                      </a>
                    </td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>