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

$GLOBALS['LIB_LOCATION'] = dirname(__FILE__);

include_once 'restApiClient.php';
require_once dirname(__FILE__) . '/../includes/constants.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;

class TangoApi
{
    private $apiUrl;
    private $apiKey;
    private $generateLog;

    // Singleton
    private static $instance = null;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new TangoApi(
                Configuration::get(Consts\ITST_TANGO_API_URL),
                Configuration::get(Consts\ITST_TANGO_API_KEY),
                Configuration::get(Consts\ITST_TANGO_LOG_SEVERITY)
            );
        }
        return self::$instance;
    }

    public function __construct($apiUrl, $apiKey, $generateLog)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->generateLog = $generateLog;
    }

    public function status()
    {
        $status = RestApiClient::get("/general/status", null, "application/json");
        Configuration::updateValue(Consts\ITST_TANGO_VERSION, $status['version']);
        Configuration::updateValue(Consts\ITST_TANGO_ENVIRONMENT, $status['environment']);
        return $status;
    }

    public function createPedido($talon_ped, $pedido)
    {
        $result = RestApiClient::post("/ventas/pedidos/" . $talon_ped, $pedido, "application/json");
        return $result;
    }

    public function getProducts($top, $page)
    {
        $result = RestApiClient::get("/stock/productos?top=" . $top . "&page=" . $page . "&perfil=V,A");
        return $result;
    }

    public function getProduct($cod_articu)
    {
        $result = RestApiClient::get("/stock/producto/".$cod_articu);
        return $result;
    }

    public function ventasGetPrecioArticulo($nroDeLis, $CodArticu)
    {
        $result = RestApiClient::get('/ventas/precios/' . $nroDeLis . '/' . $CodArticu);
        return $result;
    }
    public function ventasGetListas()
    {
        $result = RestApiClient::get('/ventas/listas');
        return $result;
    }
    public function ventasGetLista($nroList)
    {
        $result = RestApiClient::get('/ventas/listas/' . $nroList);
        return $result;
    }
    public function ventasGetCliente($COD_CLIENT)
    {
        $result = RestApiClient::get('/ventas/clientes/' . $COD_CLIENT);
        return $result;
    }
    public function ventasGetClienteContactos($options)
    {
        $query = "";
        foreach (array_keys((array)$options) as $key) {
            $query  = $query . $key . '=' . $options[$key] . "&";
        }
        $result = RestApiClient::get('/ventas/clientes/contactos?' . $query);
        return $result;
    }

    public function ventasGetTalonarios($options)
    {
        $query = "";
        // Typecast en options es para que no de error si $options es null sin hacer un if
        foreach (array_keys((array)$options) as $key) {
            $query  = $query . $key . '=' . $options[$key] . "&";
        }
        $result = RestApiClient::get('/ventas/talonarios?' . $query);
        return $result;
    }

    public function ventasGetTransportes()
    {
        $result = RestApiClient::get('/ventas/transportes/')['rows'];
        return $result;
    }
    public function ventasGetTransporte($cod_transp)
    {
        $result = RestApiClient::get('/ventas/transportes/' . $cod_transp);
        return $result;
    }

    public function generalGetMonedas()
    {
        $result = RestApiClient::get('/general/monedas')['rows'];
        return $result;
    }

    public function generalGetMoneda($ID_MONEDA)
    {
        $result = RestApiClient::get('/general/monedas/' . $ID_MONEDA);
        return $result;
    }

    public function generalGetCotizacion($ID_MONEDA)
    {
        $result = RestApiClient::get('/general/monedas/' . $ID_MONEDA . '/cotizacion');
        return $result;
    }
}
