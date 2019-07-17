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

<div class="col">

    <div class="card">
        <h3 class="card-header">
            <i class="material-icons">person</i>
                {l s='The customer [%06d] is a contact of the following Tango customer' sprintf=[{$customerExtended->id_customer}] mod='itsttangointegracion'}            
                <a href="{$url|escape:'html':'UTF-8'}&amp;back={$smarty.server.REQUEST_URI|urlencode}"  
                    class="tooltip-link float-right" data-toggle="pstooltip" title="" data-placement="top" data-original-title="{l s='Sync' mod='itsttangointegracion'}"
                >
                    <i class="material-icons"> sync </i>
                </a>

        </h3>

        <div class="card-body">
            <div class="row">
                {if isset($customerExtended->COD_CLIENT) && $customerExtended->COD_CLIENT != ""}
                    <div class="col-lg-6">
                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Customer Code' mod='itsttangointegracion'} </div>
                            <div class="col-8"> {$customerExtended->COD_CLIENT} </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Vat Number' mod='itsttangointegracion'} </div>
                            <div class="col-8"> {$customerExtended->COD_CLIENT} </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Business Name' mod='itsttangointegracion'} </div>
                            <div class="col-8"> {$customerExtended->CUIT} </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Created at' mod='itsttangointegracion'} </div>
                            <div class="col-8"> {$customerExtended->date_add} </div>
                        </div>         
                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Updated at' mod='itsttangointegracion'} </div>
                            <div class="col-8">{$customerExtended->date_upd} </div>
                        </div>                                           
                    </div>   
                    <div class="col-lg-6">
                        <div class="row mb-1">
                            <div class="col-4 text-right"> {l s='Price List' mod='itsttangointegracion'} </div>
                            <div class="col-8"> {$customerExtended->NRO_LISTA} </div>
                        </div>
                    </div>   
                {else}
                    <div class="col-lg-12">
                        <div class="text-center text-warning">
                            <div class="m-b-none">
                            <i class="icon-warning"></i>
                            <h5 class="font-bold no-margins">
                                {l s='could not synchronize this customer' mod='itsttangointegracion'}                            
                            </h5>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>

    </div>
</div>    
{/block}