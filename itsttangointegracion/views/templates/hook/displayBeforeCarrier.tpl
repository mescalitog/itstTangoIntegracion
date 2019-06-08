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
{block name='select_fec_entr'}
    <p>
      <a class="btn btn-link" data-toggle="collapse" href="#collapseFecEntr" aria-expanded="false" aria-controls="collapseFecEntr">
        {l s='Want to establish a delivery date?' mod='itsttangointegracion'}
      </a>
    </p>
    <div class="collapse" id="collapseFecEntr">
        <form class="clearfix" id="update_fec_entr" action="{$url}" data-link-action="update-extended" method="post">
            <div class="form-group col-5">
                <div class="input-group date" id="fec_entr" data-target-input="nearest">
                    <input type="text" class="form-control datetimepicker-input" data-target="#fec_entr"/>
                    <div class="input-group-append" data-target="#fec_entr" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr class="separator">
{/block}

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        $(function () {
            {if isset($fecha_entr)} 
                $('#fec_entr').setDate = '{$fecha_entr}';
            {/if}
            $('#fec_entr').datetimepicker({ format: 'L',  {if isset($fecha_entr)}useCurrent: true{else}useCurrent: false{/if}, locale: 'es',  minDate: moment().add(5, 'days'), maxDate: moment().add(60, 'days'), daysOfWeekDisabled: [0,6] });
            $('#fec_entr').on('change.datetimepicker', function(e) {
                if (e.oldDate !== e.date){
                    try
                    {
                        var fecha_entr = moment($('#fec_entr').viewDate).format("YYYY-MM-DD");
                        resAjax = $.ajax({
                            type: "POST",
                            url: "{$url}",
                            async: true,
                            data: {
                                ajax : "1",
                                module : "{$module}",
                                fc : "{$fc}",
                                controller : "{$controller}",
                                action : "update-extended",
                                id_cart: "{$id_cart}",
                                token: "{$token}",
                                fecha_entr: fecha_entr
                            },
                        });
                    }
                    catch(e) {
                        console.error(e);
                     }
                }
            });
        });
    });    
</script>
