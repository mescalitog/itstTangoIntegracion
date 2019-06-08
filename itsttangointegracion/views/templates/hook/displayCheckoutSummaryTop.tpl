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
{block name='purchase_order_form'}
    <p>{l s='Do you have a purchase order number?' mod='itsttangointegracion'}</p>
    <div class="purchase-order">
        <form class="clearfix" id="update_oc" action="{$url}" data-link-action="update-extended" method="post">
            <div class="input-group mb-3">
                <input class="purchase-order-input form-control" type="text" name="nro_o_comp" value="{$nro_o_comp}" placeholder="{l s='Purchase Order Number' mod='itsttangointegracion'}" aria-label="{l s='Purchase Order Number' mod='itsttangointegracion'}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary" name="update_oc" type="button"><span>{l s='Add' mod='itsttangointegracion'}</span></button>
                </div>
            </div>
        </form>
    </div>
    <hr class="separator">
{/block}
