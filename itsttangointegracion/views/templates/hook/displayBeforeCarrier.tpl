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
<!--
<div class="block-purchase-order">
    <p>
        <a class="collapse-button purchase-order-button" data-toggle="collapse" href="#purchase-order" aria-expanded="false" aria-controls="purchase-order">
        {l s='Want to include a purchase order number?', d='itsttangointegracion'}
        </a>
    </p>

    <div class="collapse" id="purchase-order">
        {block name='purchase_order_form'}
        <form action="{$urls.pages.cart}" data-link-action="add-purchase-order" method="post">
            <input type="hidden" name="token" value="{$static_token}">
            <input class="purchase-order-input" type="text" name="purchase_order" placeholder="{l s='Purchase Order Number', d='itsttangointegracion'}">
            <button type="submit" class="btn btn-primary"><span>{l s='Add', d='itsttangointegracion'}</span></button>
        </form>
        {/block}
    </div>
</div>
-->
<!--
<pre>
{$params}
</pre>
-->
