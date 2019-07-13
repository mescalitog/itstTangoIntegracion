{*
* 2007-2019  PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    itstuff <info@itstuff.com.ar>
*  @copyright 2007-2019  PrestaShop SA
*  @license commercial license contact itstuff for details
*  
*}

{block name="display_extended_customer"}

<div class="col-lg-12">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-user"></i>
            El cliente #{$customerExtended->id_customer} es contacto de este cliente en TANGO
            <div class="panel-heading-action">
                <a class="btn btn-default" href="{$url|escape:'html':'UTF-8'}&amp;back={$smarty.server.REQUEST_URI|urlencode}">
                    <i class="icon-refresh" aria-hidden="true"></i>
                    {l s='Sync'}
                </a>
            </div>
        </div>
        <div class="row">
            {if isset($customerExtended->COD_CLIENT) && $customerExtended->COD_CLIENT != ""}
                <div class="col-lg-6">
                    <div class="form-horizontal">
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Código de Cliente'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->COD_CLIENT}</p>
                            </div>
                        </div>
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='CUIT'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->CUIT}</p>
                            </div>
                        </div>
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Razón Social'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->RAZON_SOCI}</p>
                            </div>
                        </div>
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Creado'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->date_add}</p>
                            </div>
                        </div>
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Actualizado'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->date_upd}</p>
                            </div>
                        </div>                                                                                
                        
                    </div>
                </div>   
                <div class="col-lg-6">
                    <div class="form-horizontal">
                        <div class="row">
                            <label class="control-label col-lg-3">{l s='Lista de Precios'}</label>
                            <div class="col-lg-9">
                                <p class="form-control-static">{$customerExtended->NRO_LISTA}</p>
                            </div>
                        </div>                        
                    </div>                
                </div>
            {else}
                <div class="col-lg-12">
                    <div class="text-center text-warning">
                        <div class="m-b-none">
                        <i class="icon-warning"></i>
                        <h5 class="font-bold no-margins">
                            Este cliente no pudo ser sincronizado
                        </h5>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>    
{/block}