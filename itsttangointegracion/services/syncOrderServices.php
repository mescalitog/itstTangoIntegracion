<?php
/**
 * 2007-2019  PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    itstuff <info@itstuff.com.ar>
 * @copyright Copyright 2019 (c) ItStuff [https://itstuff.com.ar]
 * @license   https://itstuff.com.ar/licenses/commercial-1.0.html Commercial License
 */

namespace ItSt\PrestaShop\Tango\Orders;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DB;
use Context;
use Prices;
use Tools;
use Configuration;
use FileLogger;

class ItstTangoSyncOrders
{
    /**
     * Devuelve una regla
     */
    public static function getByOrderId($id_order)
    {
        if (!(int)$id_order) {
            return false;
        }
        $sql = 'SELECT *
		  FROM `' . _DB_PREFIX_ . 'itst_sync_order`
		  WHERE `id_order` = ' . (int)$id_order;
        return Db::getInstance()->getRow($sql);
    }

    public static function setOrderSyncResult($order, $response)
    {
        $id_gva21 = isset($response["ID_GVA21"]) ? $response["ID_GVA21"] : 'null';
        $nro_pedido = isset($response["NRO_PEDIDO"]) ? $response["NRO_PEDIDO"] : null;
        $talon_ped = isset($response["TALON_PED"]) ? $response["TALON_PED"] : 'null';
        $error_code = isset($response["code"]) ? $response["code"] : 'null';
        $error_message = isset($response["message"]) ? $response["message"] : null;
        $id_order_state = isset($order->id_order_state) ? $order->id_order_state : 'null';

        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'itst_sync_order` '
            . ' ( `id_order`, `id_order_state`, `ID_GVA21`, `NRO_PEDIDO`, `TALON_PED`, `error_code`, '
            . '`error_message`, `created_at`, `updated_at`)
    VALUES
    (' . $order->id . ', ' . (int)$id_order_state . ',' . $id_gva21 . ','
            . (isset($nro_pedido) ? '\'' . $nro_pedido . '\'' : 'null') .
            ', ' . $talon_ped . ', '
            . $error_code . ', '
            . (isset($error_message) ? '\'' . $error_message . '\'' : 'null') .
            ', now(), now());';

        return Db::getInstance()->execute($query);
    }
}
