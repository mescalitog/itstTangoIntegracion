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
 * @author    itstuff.com.ar
 * @copyright Copyright (c) ItStuff [https://itstuff.com.ar]
 * @license   https://itstuff.com.ar/licenses/commercial-1.0.html Commercial License
 */

namespace ItSt\PrestaShop\Tango\Ventas;

require_once dirname(__FILE__) . '/ventas.php';
require_once dirname(__FILE__) . '/clientes.php';
require_once dirname(__FILE__) . '/../orderExtended.php';
require_once dirname(__FILE__) . '/../../services/carriersServices.php';
require_once dirname(__FILE__) . '/../../services/syncOrderServices.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Carriers as Carriers;
use ItSt\PrestaShop\Tango\Orders as Orders;
use DateTime;
use DateInterval;

use TangoApi;

class Pedidos
{
    protected static $instance = null;
    // Singleton
    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Pedidos();
        }
        return static::$instance;
    }

    public function __construct()
    {
        //Keep blank line
    }

    public function createPedidoOrderStatusPostUpdate($params)
    {
        if (!isset($this->context)) {
            $this->context = \Context::getContext();
        }

        $order_taxes = \Configuration::get(Consts\ITST_TANGO_ORDERS_TAXES, false);
        $talon_ped = \Configuration::get(Consts\ITST_TANGO_ORDERS_TALONARIO, false);
        //Shipping Costs
        $shiping_sync = \Configuration::get(Consts\ITST_TANGO_SHIPPING_SYNC, null);
        $shipping_cod_articu = \Configuration::get(Consts\ITST_TANGO_SHIPPING_PRODUCT, null);
        $comp_stk = \Configuration::get(Consts\ITST_TANGO_ORDERS_COMP_STK, 0);

        $logger = Helpers\ItStLogger::instance();

        // Rendondeo los precios?
        $roundPrices = true;

        $newOrderStatus = $params['newOrderStatus'];
        $id_order = $params['id_order'];
        $order = new \Order($id_order);
        $pedido = Orders\ItstTangoSyncOrders::getByOrderId($id_order);
        if ($pedido) {
            $logger->addLog(
                'createPedidoOrderStatusPostUpdate;La orden ' . $order->reference
                    . ' esta sincronizada con el pedido ' . $pedido['NRO_PEDIDO'],
                Consts\SEVERITY_INFO,
                null,
                'itsttangointegracion'
            );
            return;
        }

        $cart = $params['cart'];
        // Extended order data
        $orderExtended = new \ItSt\PrestaShop\Tango\OrdersExtended($cart->id);
        $customer = new \Customer($cart->id_customer);
        $contactos = Clientes::getContactos(array('email' => $customer->email));
        $contacto = (empty($contactos['rows'])) ? null : $contactos['rows'][0];
        $condVenta = 1;
        $condVended = null;
        $nroLista = null;
        //Cuando no encuentro el cliente lo mando como ocacional, definido en las constantes.
        $codCliente = Consts\ITST_TANGO_ORDERS_CLIENTE_OCACIONAL;
        if (isset($contacto)) {
            $cliente = Clientes::getCliente($contacto['COD_CLIENT']);
            $codCliente = (isset($cliente) && isset($cliente['COD_CLIENT'])
                ? $cliente['COD_CLIENT']
                : Consts\ITST_TANGO_ORDERS_CLIENTE_OCACIONAL);
            $condVenta = (isset($cliente) && isset($cliente['COND_VTA'])) ? $cliente['COND_VTA'] : 1;
            $condVended = (isset($cliente) && isset($cliente['COD_VENDED'])) ? $cliente['COD_VENDED'] : null;
            $nroLista = (!isset($cliente) && isset($cliente['NRO_LISTA'])) ? $cliente['NRO_LISTA'] : null;
        };

        //Transporte
        $transporte = Carriers\ItstTangoCarriers::getCarrierRuleByCarrier(
            $cart->id_carrier,
            $cart->id_shop,
            $cart->id_shop_group
        );
        // Productos
        $products = $cart->getProducts();
        $logger->addLog(
            'createPedidoOrderStatusPostUpdate;newOrderStatus:' . \Tools::jsonEncode($newOrderStatus),
            Consts\SEVERITY_DEBUG,
            null,
            'itsttangointegracion::ventas::pedidos'
        );
        $logger->addLog(
            'createPedidoOrderStatusPostUpdate;order:' . \Tools::jsonEncode($order),
            Consts\SEVERITY_DEBUG,
            null,
            'itsttangointegracion::ventas::pedidos'
        );
        $renglones = array();

        foreach ($products as $product) {
            $precio = ($order_taxes) ? $product['price_wt'] : $product['price'];
            $renglon = array(
                'COD_ARTICU' => $product['reference'],
                'Descripcion' => $product['name'],
                'CANT_PEDID' => $product['quantity'],
                'CANT_A_FAC' => $product['quantity'],
                'DESCUENTO' => 0,
                'PRECIO' => $roundPrices ? round($precio) : $precio,
                'COD_CLASIF' => ''
            );
            $renglones[] = $renglon;
        }

        $shipping_precio = ($order_taxes) ? $order->total_shipping_tax_incl : $order->total_shipping_tax_excl;
        if (isset($shiping_sync) && isset($shipping_cod_articu) && ($shipping_cod_articu)) {
            $renglones[] = array(
                'COD_ARTICU' => $shipping_cod_articu,
                'Descripcion' => 'Costo de Envio',
                'CANT_PEDID' => 1,
                'CANT_A_FAC' => 1,
                'CANT_A_DES' => 0,
                'DESCUENTO' => 0,
                'PRECIO' => $roundPrices ? round($shipping_precio) : $shipping_precio,
                'COD_CLASIF' => ''
            );
        }
        // FIXME: CUIT y COD_CLIENT reemplazando SIRET y APE
        // http://www.doblelink.com/blog/cambiar-los-campos-siret-y-ape-en-prestashop/
        // FIXME: es pedido web, tienda, web_order_id
        $fecha_entr = new DateTime();
        $fecha_entr->add(new DateInterval('P7D'));
        // $porc_desc = ($order->total_discounts * 100 /  $order->total_products_wt);
        $total_order_wt = $order->total_products_wt + $order->total_shipping_tax_incl;
        $porc_desc = ($order->total_discounts_tax_incl * 100 /  $total_order_wt);
        $pedido = array(
            'ID_EXTERNO' => $order->reference,
            'NRO_OC_COMP' => $orderExtended->NRO_O_COMP,
            'FECHA_O_COMP' => (new DateTime($order->date_add))->format('c'),
            'FECHA_ENTR' => (isset($orderExtended->FECHA_ENTR) && ($orderExtended->FECHA_ENTR <> '0000-00-00')) ? $orderExtended->FECHA_ENTR : $fecha_entr->format('c'),
            'COND_VTA' => $condVenta,
            'COD_VENDED' => $condVended,
            'COMP_STK' => $comp_stk,
            'COD_SUCURS' => '',
            'COTIZ' => '',
            'FECHA_PEDI' => (new DateTime($order->date_add))->format('c'),
            'N_LISTA' => $nroLista,
            'COD_CLIENT' => $codCliente,
            'LEYENDA_1' => $newOrderStatus->name,
            'LEYENDA_2' => (isset($orderExtended->NRO_O_COMP) && !empty($orderExtended->NRO_O_COMP))
                ? 'El cliente ingreso OC:' . $orderExtended->NRO_O_COMP
                : 'El cliente no ingreso OC',
            'renglones' => $renglones,
            // 'TIENDA' => 'E-COMMERCE',
            'ES_PEDIDO_WEB' => 0,
            'ESTADO' => 1,
            'TOTAL_PEDI' => ($order_taxes) ? ($order->total_paid_tax_incl ) : ($order->total_paid_tax_excl),
            'PORC_DESC' => $porc_desc,
            'COD_TRANSP' => (isset($transporte['COD_TRANSP'])) ? $transporte['COD_TRANSP'] : null
        );

        $logger->addLog(
            'createPedidoOrderStatusPostUpdate;pedido:' . \Tools::jsonEncode($pedido),
            Consts\SEVERITY_DEBUG,
            null,
            'itsttangointegracion'
        );

        //FIXME: al la clase de pedidos
        $result = TangoApi::instance()->createPedido($talon_ped, $pedido);
        Orders\ItstTangoSyncOrders::setOrderSyncResult($order, $result);
        $logger->addLog(
            'createPedidoOrderStatusPostUpdate;result:' . \Tools::jsonEncode($result),
            Consts\SEVERITY_DEBUG,
            null,
            'itsttangointegracion'
        );
        if (!isset($result['NRO_PEDIDO'])) {
            $logger->addLog(
                'Ocurrio un error al crear el pedido ' . $order->reference,
                Consts\SEVERITY_FATAL_ERROR,
                null,
                'itsttangointegracion'
            );
        } else {
            $logger->addLog(
                'Order ' . $order->reference
                    . ' was created in tango as ' . $result['NRO_PEDIDO'],
                Consts\SEVERITY_INFO,
                null,
                'itsttangointegracion'
            );
        }
    }

    public function __destruct()
    {
        //Keep blank line
    }
}
