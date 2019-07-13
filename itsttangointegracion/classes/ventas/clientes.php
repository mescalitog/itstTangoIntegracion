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

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;

use TangoApi;

class Clientes
{
    protected static $instance = null;
    // Singleton
    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Clientes();
        }
        return static::$instance;
    }

    public function __construct()
    {
    }

    public static function getCliente($COD_CLIENT)
    {
        $cliente = \TangoApi::instance()->ventasGetCliente($COD_CLIENT);
        return $cliente;
    }

    public static function getContactos($options)
    {
        $contactos = \TangoApi::instance()->ventasGetClienteContactos($options);
        return $contactos;
    }

    public static function getDireccionesEntrega($COD_CLIENT) {
        $direcciones = \TangoApi::instance()->ventasGetClienteDirecciones($COD_CLIENT);
        return $direcciones;
    }

    public function __destruct()
    {
    }
}
