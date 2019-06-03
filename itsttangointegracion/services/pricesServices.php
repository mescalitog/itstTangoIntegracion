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

namespace ItSt\PrestaShop\Tango\Prices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DB;
use Context;
use Prices;
use Tools;
use Configuration;
use FileLogger;

class ItstTangoPrices
{
    /**
     * Devuelve una regla
     */
    public static function getPricesList($id_prices_list)
    {
        if (!(int)$id_prices_list) {
            return false;
        }
        return Db::getInstance()->getRow(
            '
		SELECT *
		FROM `' . _DB_PREFIX_ . 'itst_prices_list`
		WHERE `id_prices_list` = ' . (int)$id_prices_list
        );
    }

    public static function getPricesLists($id_shop = null, $id_shop_group = null)
    {
        $query = 'SELECT *
		FROM `' . _DB_PREFIX_ . bqSQL('itst_prices_list') . '`		
		WHERE 1';

        $query .= (($id_shop) ? (' AND `id_shop` = ' . $id_shop) : '1=1');
        $query .= (($id_shop_group) ? (' AND `id_shop_group` = ' . $id_shop_group) : '1=1');
        $query .= ' ORDER BY `order` ASC;';

        return Db::getInstance()->executeS($query);
    }
}
