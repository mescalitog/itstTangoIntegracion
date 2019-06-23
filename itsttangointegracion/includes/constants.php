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

namespace ItSt\PrestaShop\Tango\Constantes {
    
    const ITST_TANGO_LIVE_MODE = 'ITST_TANGO_LIVE_MODE';
    const ITST_TANGO_LOG_SEVERITY = 'ITST_TANGO_LOG_SEVERITY';
    const ITST_TANGO_LOG_TOFILE = 'ITST_TANGO_LOG_TOFILE';
    const ITST_TANGO_API_URL = 'ITST_TANGO_API_URL';
    const ITST_TANGO_API_KEY = 'ITST_TANGO_API_KEY';
    const ITST_TANGO_NAME = 'ITST_TANGO_NAME';
    const ITST_TANGO_VERSION = 'ITST_TANGO_VERSION';
    const ITST_TANGO_ENVIRONMENT = 'ITST_TANGO_ENVIRONMENT';

    // Determina si se va a chequear que la referencia exista en Tango
    const ITST_TANGO_VALIDATE_PRODUCT_REFERENCE = 'ITST_TANGO_VALIDATE_PRODUCT_REFERENCE';
    // Determina si se va a sincronizar el stock
    const ITST_TANGO_STOCK_SYNC = 'ITST_TANGO_STOCK_SYNC';
    const ITST_TANGO_STOCK_SYNC_DEPOSIT = 'ITST_TANGO_STOCK_SYNC_DEPOSIT';
    // Configuracion del modulo de precios
    const ITST_TANGO_PRICES_SYNC = 'ITST_TANGO_PRICES_SYNC';
    const ITST_TANGO_PRICES_SYNC_PRODUCTS = 'ITST_TANGO_PRICES_SYNC_PRODUCTS';
    const ITST_TANGO_PRICES_SYNC_COMBINATIONS = 'ITST_TANGO_PRICES_SYNC_COMBINATIONS';
    const ITST_TANGO_MAX_ERRORS = 'ITST_TANGO_MAX_ERRORS';


    const ITST_TANGO_INVENTORY_SYNC = 'ITST_TANGO_INVENRORY_SYNC';
    const ITST_TANGO_ORDERS_COMP_STK = 'ITST_TANGO_ORDERS_COMP_STK';
    const ITST_TANGO_PRODUCT_SYNC = 'ITST_TANGO_PRODUCT_SYNC';
    // Configuracion del modulo de pedidos
    const ITST_TANGO_ORDERS_SYNC = 'ITST_TANGO_ORDERS_SYNC';
    const ITST_TANGO_ORDERS_TALONARIO = 'ITST_TANGO_ORDERS_TALONARIO';
    const ITST_TANGO_ORDERS_CLIENTE_OCACIONAL = '000000';
    const ITST_TANGO_ORDERS_RETRIES = 'ITST_TANGO_ORDERS_RETRIES';
    const ITST_TANGO_ORDERS_VALID_DAYS = 'ITST_TANGO_ORDERS_VALID_DAYS';
    const ITST_TANGO_ORDERS_TAXES = 'ITST_TANGO_ORDERS_TAXES';

    //Transportes
    // Que producto usamos en tango para cargar el valor del transporte
    const ITST_TANGO_SHIPPING_SYNC = 'ITST_TANGO_SHIPPING_SYNC';
    const ITST_TANGO_SHIPPING_PRODUCT = 'ITST_TANGO_CARRIER_PRODUCT';

    // Log Severity Levels
    const SEVERITY_DEBUG = 0;
    const SEVERITY_INFO = 1;
    const SEVERITY_WARNING = 2;
    const SEVERITY_ERROR = 3;
    const SEVERITY_FATAL_ERROR = 4;

    // Parametros
    // Cantidad Maxima de Errores antes de abortar
    const ITST_TANGO_SYNC_CATEGORY = 'NUEVOS IMPORTADOS TANGO';

    //Orden de Compra
    const ITST_OC_SUBMIT = 'ITST_OC_SUBMIT';
}
