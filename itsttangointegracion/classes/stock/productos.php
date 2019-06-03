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

namespace ItSt\PrestaShop\Tango\Stock;

require_once dirname(__FILE__) . '/stock.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use TangoApi;

class Productos
{
    protected static $instance = null;
    private $logger = null;
    // Singleton
    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Productos();
        }
        return static::$instance;
    }

    public function __construct()
    {
        //Keep the empty line
    }

    /**
     * Devuelve la categoria por default para los productos nuevos.
     * Productos que no estan en prestashiop pero si en tango.
     */
    private function getNewProductDefaultCategoryId()
    {
        $logger = Helpers\ItStLogger::instance();
        $defaultLang = (int)\Configuration::get('PS_LANG_DEFAULT');
        $categoryId = null;
        $category = \Category::searchByName($defaultLang, Consts\ITST_TANGO_SYNC_CATEGORY, true);
        $logger->addLog(
            'getNewProductDefaultCategoryId;started;'
                . 'ITST_TANGO_SYNC_CATEGORY=' . Consts\ITST_TANGO_SYNC_CATEGORY
                . ', $category=' . \Tools::jsonEncode($category),
            Consts\SEVERITY_DEBUG,
            0,
            'itsttangointegracion::stock::productos'
        );
        if (empty($category)) {
            $cat = new \Category();
            $cat->description = array($defaultLang => 'Productos Importados desde Tango');
            $cat->name = array($defaultLang => Consts\ITST_TANGO_SYNC_CATEGORY);
            $cat->id_parent = 2; // Inicio
            $cat->is_root_category = false;
            $cat->link_rewrite = array(
                $defaultLang => str_replace(\Tools::strtoupper(Consts\ITST_TANGO_SYNC_CATEGORY), ' ', '-')
            );
            $cat->active = 0;
            if ($cat->add()) {
                $categoryId = (int)$cat->id;
                $logger->addLog(
                    'getNewProductDefaultCategoryId;'
                        . ',Se agrego la categoria=' . $cat->description[$defaultLang],
                    Consts\SEVERITY_INFO,
                    0,
                    'itsttangointegracion::stock::productos'
                );
            } else {
                $logger->addLog(
                    'getNewProductDefaultCategoryId;'
                        . 'Ocurrió un error creando la categoria ' . Consts\ITST_TANGO_SYNC_CATEGORY,
                    Consts\SEVERITY_ERROR,
                    0,
                    'itsttangointegracion::stock::productos'
                );
            }
        } else {
            $categoryId = (int)$category['id_category'];
        }
        return $categoryId;
    }

    /**
     * Sincroniza productos
     *
     * @param int $id_shop Shop identifier
     *
     * @return bool
     */
    public function syncProducts($id_shop = 0)
    {
        $logger = Helpers\ItStLogger::instance();
        $logger->addLog(
            'syncProducts;started;id_shop=' . $id_shop,
            Consts\SEVERITY_DEBUG,
            0,
            'itsttangointegracion::stock::productos'
        );
        $top = 250;
        $page = 0;
        $errorCount = 0;
        $maxErrors = \Configuration::get(Consts\ITST_TANGO_MAX_ERRORS, 5);
        $productCategoryId = $this->getNewProductDefaultCategoryId();
        $logger->addLog(
            'syncProducts;started;$productCategoryId=' . $productCategoryId,
            Consts\SEVERITY_DEBUG,
            0,
            'itsttangointegracion::stock::productos'
        );
        do {
            $page++;
            $productsPage = TangoApi::instance()->getProducts($top, $page);
            $count = $productsPage['count'];
            // $logger->logDebug('productos :: '.Tools::jsonEncode($productsPage));
            $logger->addLog(
                'syncProducts;count = ' . $count . ', page='
                    . $page . ', row count=' . count($productsPage['rows']),
                Consts\SEVERITY_DEBUG,
                0,
                'itsttangointegracion::stock::productos'
            );

            // TODO: a productImporter
            $products = $productsPage['rows'];

            for ($i = 0; ($i < count($products) and ($errorCount < $maxErrors)); $i++) {
                $articulo = $products[$i];
                $referece = $articulo['COD_ARTICU'];
                $idProductByRef = (int)\Db::getInstance()->getValue('
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    ' . \Shop::addSqlAssociation('product', 'p') . '
                    WHERE p.`reference` = "' . pSQL($referece) . '"
                    ', false);
                $product = new \Product($idProductByRef);
                // Si no tiene categoria lo agrego a la default
                if (empty($idProductByRef)) {
                    $product->id_category_default = (int)$productCategoryId;
                }
                //FIXME: precios y cantidad
                $product->quantity = 0;
                $product->price = 0;
                // $product->name = array((int)$defaultLang =>  $articulo['DESCRIPCIO']);
                $product->name = $articulo['DESCRIPCIO'];
                $product->ean13 = $articulo['COD_BARRA'];
                $product->description = $articulo['DESCRIPCIO'] . ' ' . $articulo['DESC_ADIC'];
                $product->description_short = $articulo['DESCRIPCIO'];
                $product->reference = $articulo['COD_ARTICU'];
                $logger->addLog(
                    'syncProducts;'
                        . 'COD_ARTICU=' . $articulo['COD_ARTICU']
                        . ', idProductByRef=' . $idProductByRef
                        . ', , productId (prestaShop)=' . (isset($product->id) ? $product->id : 'nuevo')
                        . ', errorCount=' . $errorCount
                        . ', ITST_TANGO_MAX_ERRORS=' . $maxErrors,
                    Consts\SEVERITY_DEBUG,
                    0,
                    'itsttangointegracion::stock::productos'
                );
                //save the changes
                if (!$product->save()) {
                    $errorCount++;
                    $logger->addLog(
                        'syncProducts;Ocurrió un error al grabar el producto COD_ARTICU=' . $articulo['COD_ARTICU'],
                        Consts\SEVERITY_ERROR,
                        0,
                        'itsttangointegracion::stock::productos'
                    );
                }
            }
        } while ((($page * $top) < $count) and ($errorCount <= $maxErrors));
        if ($errorCount >= $maxErrors) {
            $logger->addLog(
                'syncProducts;'
                    . 'was aborted due to many errors (' . $errorCount . ')',
                Consts\SEVERITY_FATAL_ERROR,
                0,
                'itsttangointegracion::ventas'
            );
        }
    }

    public function __destruct()
    {
        //Keep the empty line
    }
}
