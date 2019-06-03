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

/*
 * Este modulo se puede llamar mediante un cron job para sincronizar listas de precios.
 */
require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../init.php';


require_once dirname(__FILE__) . '/includes/constants.php';
require_once dirname(__FILE__) . '/classes/helpers.php';
require_once dirname(__FILE__) . '/classes/ventas/precios.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Ventas as Ventas;

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}

$logger = Helpers\ItStLogger::instance();
$token = (null !== \Tools::getValue('token')) ? \Tools::getValue('token') : null;
$id_shop = (null !== \Tools::getValue('id_shop')) ?: 0;
$defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

if (!\Configuration::get(Consts\ITST_TANGO_PRICES_SYNC, null)) {
    $logger->addLog(
        'precios-cron;started;precios-cron is not enable in configuration',
        Consts\SEVERITY_DEBUG,
        0,
        'itsttangointegracion'
    );
} else {
    $logger->addLog(
        'precios-cron;started;token=' . $token . ', id_shop=' . $id_shop,
        Consts\SEVERITY_DEBUG,
        0,
        'itsttangointegracion'
    );

    if (Helpers\ItStTools::validateSecureKey($token)) {
        return Ventas\Precios::instance()->syncPrecios($id_shop);
    } else {
        $logger->addLog(
            'precios-cron;token and secureKey don\'t match',
            Consts\SEVERITY_ERROR,
            409,
            'itsttangointegracion'
        );
    }
}
