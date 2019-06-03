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

namespace ItSt\PrestaShop\Tango\Helpers;

require_once dirname(__FILE__) . '/../../includes/constants.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use Configuration;
use PrestaShopLogger;

/**
 * Tools
 */
class ItStTools
{
    public static function getSecureKey()
    {
        $secureKey = md5(_COOKIE_KEY_ . Configuration::get('PS_SHOP_NAME'));
        return $secureKey;
    }
    public static function validateSecureKey($key)
    {
        $secureKey = md5(_COOKIE_KEY_ . Configuration::get('PS_SHOP_NAME'));
        return (!empty($key) and $secureKey == $key);
    }
}
