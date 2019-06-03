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
require_once dirname(__FILE__) . '/listas.php';
require_once dirname(__FILE__) . '/../general/general.php';
require_once dirname(__FILE__) . '/../../services/pricesServices.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\General as General;
use ItSt\PrestaShop\Tango\Prices as Prices;

use TangoApi;
use SpecificPrice;
use Context;

class Precios
{
    protected static $instance = null;
    private $logger = null;
    // Singleton
    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Precios();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->logger = Helpers\ItStLogger::instance();
    }

    private function getPriceLists()
    {
        $id_shop = (int)Context::getContext()->shop->id;
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;

        $tangoList = Prices\ItstTangoPrices::getPricesLists($id_shop, $id_shop_group);
        $list = array();
        foreach ($tangoList as $val) {
            $tango = Listas::getLista($val['NRO_DE_LIS']);
            if (isset($tango) && $tango != null && (!isset($tango['code']))) {
                $list[] = array(
                    'NRO_DE_LIS' => $tango['NRO_DE_LIS'],
                    'MON_CTE' => $tango['MON_CTE'],
                    'ID_MONEDA' => $val['ID_MONEDA'],
                    'COTIZACION' => (float)($tango['MON_CTE'])
                        ? 1
                        : General\Monedas::getCotizacion($val['ID_MONEDA'])['COTIZACION'],
                    'COD_MONEDA' => General\Monedas::getMoneda($val['ID_MONEDA'])['COD_MONEDA']
                );
            } elseif (isset($tango['code'])) {
                $this->logger->addLog(
                    'syncPrecios;Ocurrió un error procesando la lista;'
                        . 'error : ' . \Tools::jsonEncode($tango),
                    Consts\SEVERITY_FATAL_ERROR,
                    0,
                    'itsttangointegracion::ventas'
                );
            }
        };
        return $list;
    }
    /**
     * Devuelve el precio consultando las listas
     */
    private function getPrecioLista($priceLists, $CodArticu)
    {
        $precio = null;
        for ($i = 0; ($i < count($priceLists) and !isset($precio['PRECIO'])); $i++) {
            $nroLista = $priceLists[$i]['NRO_DE_LIS'];
            $precio = \TangoApi::instance()->ventasGetPrecioArticulo($nroLista, $CodArticu);
            $precio['LISTA'] = $priceLists[$i];
        }
        return $precio;
    }
    protected function syncProducts($productos, $priceLists)
    {
        $errorCount = 0;
        $maxErrors = \Configuration::get(Consts\ITST_TANGO_MAX_ERRORS, 5);
        if ($productos) {
            for ($i = 0; ($i < count($productos) and ($errorCount < $maxErrors)); $i++) {
                $producto = $productos[$i];
                $reference = $producto['reference'];
                if ($reference == null) {
                    $this->logger->addLog(
                        'syncPrecios;'
                            . 'the product id_product=' . $producto['id_product'] . ' has no reference.',
                        Consts\SEVERITY_WARNING,
                        0,
                        'itsttangointegracion::ventas'
                    );
                } else {
                    $precio = $this->getPrecioLista($priceLists, $reference);
                    $this->logger->addLog(
                        'syncPrecios;procesing;'
                            . 'id_product=' . $producto['id_product']
                            . ' reference=' . $producto['reference']
                            . ' precio=' . (isset($precio['PRECIO']) ? $precio['PRECIO'] : 'SIN PRECIO')
                            . ' lista=' . (isset($precio['LISTA']) ? $precio['LISTA']['NRO_DE_LIS'] : ''),
                        Consts\SEVERITY_DEBUG,
                        0,
                        'itsttangointegracion::ventas',
                        $producto['id_product']
                    );

                    if (isset($precio['PRECIO'])
                        && isset($precio['producto']['COD_ARTICU'])
                        && ($precio['producto']['COD_ARTICU']) == $reference
                    ) {
                        $product = new \Product($producto['id_product']);
                        $product->price = $precio['PRECIO'] * $precio['LISTA']['COTIZACION'];
                        $product->save();
                    } else {
                        $errorCount++;
                        $this->logger->addLog(
                            sprintf(
                                'There is no price for id_product:%1$s, reference:%2$s in any price list',
                                $producto['id_product'],
                                $producto['reference']
                            ),
                            Consts\SEVERITY_ERROR,
                            0,
                            'itsttangointegracion::ventas'
                        );
                    }
                }
            }
        }
        if ($errorCount >= $maxErrors) {
            $this->logger->addLog(
                'syncPrecios;'
                    . 'was aborted due to many errors (' . $errorCount . ')',
                Consts\SEVERITY_FATAL_ERROR,
                0,
                'itsttangointegracion::ventas'
            );
        } else {
            $this->logger->addLog(
                'syncPrecios;end;'
                    . 'products procesed =' . count($productos),
                Consts\SEVERITY_INFO,
                0,
                'itsttangointegracion::ventas'
            );
        };
        return ($errorCount < $maxErrors);
    }
    protected function syncCombinations($combinations, $priceLists)
    {
        $errorCount = 0;
        $maxErrors = \Configuration::get(Consts\ITST_TANGO_MAX_ERRORS, 5);
        if ($combinations) {
            for ($i = 0; ($i < count($combinations) and ($errorCount < $maxErrors)); $i++) {
                $combination = $combinations[$i];
                $reference = $combination['reference'];
                if ($reference == null) {
                    $this->logger->addLog(
                        'syncPrecios;'
                            . 'the product combination id_product_attribute='
                            . $combination['id_product_attribute']
                            . ' for product id_product=' . $combination['id_product'] . ' has no reference.',
                        Consts\SEVERITY_WARNING,
                        0,
                        'itsttangointegracion::ventas'
                    );
                } else {
                    $precio = $this->getPrecioLista($priceLists, $reference);
                    $this->logger->addLog(
                        'syncPrecios;procesing;combination:{'
                            . 'id_product_attribute:' . $combination['id_product_attribute']
                            . ',id_product:' . $combination['id_product']
                            . ',reference:' . $combination['reference']
                            . ',precio:' . (isset($precio['PRECIO']) ? $precio['PRECIO'] : 'SIN PRECIO')
                            . ',lista:' . (isset($precio['LISTA']) ? $precio['LISTA']['NRO_DE_LIS'] : '')
                            . '}',
                        Consts\SEVERITY_DEBUG,
                        0,
                        'itsttangointegracion::ventas',
                        $combination['id_product_attribute']
                    );
                    // El mensaje de error lo loguea la api, me fijo si tengo el precio
                    if (isset($precio['PRECIO'])
                        && isset($precio['producto']['COD_ARTICU'])
                        && ($precio['producto']['COD_ARTICU']) == $reference
                    ) {
                        //'SELECT p.`id_product`, pa.`id_product_attribute`, pa.`reference`
                        $product = new \Product($combination['id_product']);
                        $product->updateAttribute(
                            $combination['id_product_attribute'],
                            $combination['wholesale_price'],
                            $precio['PRECIO'] * $precio['LISTA']['COTIZACION'], //$price,
                            $combination['weight'], // $weight,
                            null, //$unit,
                            $combination['ecotax'], //ecotax
                            null, //id_images
                            $reference,
                            $combination['ean13'], //ean13
                            null //default
                        );
                    } else {
                        $errorCount++;
                        $this->logger->addLog(
                            sprintf(
                                'There is no price for comnination_id:%1$s, id_product:%2$s,'
                                    . ' reference:%3$s in any price list',
                                $combination['id_product_attribute'],
                                $combination['id_product'],
                                $combination['reference']
                            ),
                            Consts\SEVERITY_ERROR,
                            0,
                            'itsttangointegracion::ventas'
                        );
                    }
                }
            }
        }
        if ($errorCount >= $maxErrors) {
            $this->logger->addLog(
                'syncPrecios;'
                    . 'was aborted due to many errors (' . $errorCount . ')',
                Consts\SEVERITY_FATAL_ERROR,
                0,
                'itsttangointegracion::ventas'
            );
        } else {
            $this->logger->addLog(
                'syncPrecios;end;'
                    . 'products procesed =' . count($combinations),
                Consts\SEVERITY_INFO,
                0,
                'itsttangointegracion::ventas'
            );
        };
        return ($errorCount < $maxErrors);
    }

    public function syncPrecios($id_shop = 0)
    {
        $priceLists = $this->getPriceLists();
        $this->logger->addLog(
            'syncPrecios;started;{'
                . 'id_shop:' . $id_shop
                . 'ITST_TANGO_PRICES_SYNC_PRODUCTS:'
                . \Configuration::get(Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS, false)
                . 'ITST_TANGO_PRICES_SYNC_COMBINATIONS:'
                . \Configuration::get(Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS, false)
                . ',priceLists:' . \Tools::jsonEncode($priceLists) . '}',
            Consts\SEVERITY_INFO,
            0,
            'itsttangointegracion::ventas'
        );

        if (\Configuration::get(Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS, false)) {
            // FIXME: productos y combinaciones o eleccion y procesamiento por lotes?
            $productos = \Db::getInstance()->executeS(
                'SELECT p.`id_product`, p.`reference`
            FROM `' . _DB_PREFIX_ . 'product` p		
            WHERE p.`reference` is not null
            ORDER BY p.`id_product` DESC, p.`reference`'
                //		LIMIT ' . (int)$limit
            );
            $this->syncProducts($productos, $priceLists);
        }

        if (\Configuration::get(Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS, false)) {
            $combinations = \Db::getInstance()->executeS(
                'SELECT p.`id_product`, pa.`id_product_attribute`, pa.`reference`, pa.`wholesale_price`, pa.`weight`,
                    pa.`ecotax`, pa.`ean13`
            FROM `' . _DB_PREFIX_ . 'product` p 
            INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.`id_product`=p.`id_product`
            WHERE pa.`reference` is not null
            ORDER BY pa.`id_product` DESC, pa.`id_product_attribute`, pa.`reference`, pa.`wholesale_price`, pa.`weight`,
                    pa.`ecotax`, pa.`ean13`;'
            );
            $this->syncCombinations($combinations, $priceLists);
        }
    }

    public function __destruct()
    {
        //Keep the empty line
    }
}
