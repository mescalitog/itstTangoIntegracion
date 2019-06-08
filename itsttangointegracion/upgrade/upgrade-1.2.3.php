<?php
/**
 * 2007-2019  PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    itstuff.com.ar
 * @copyright Copyright (c) ItStuff [https://itstuff.com.ar]
 * @license   https://itstuff.com.ar/licenses/commercial-1.0.html Commercial License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_2_3($object, $install = false)
{
    $sql = array();
    if ($object->active && !$install) {
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_orders_extended` (
            `id_cart` INT(10) unsigned NOT NULL,
            `NRO_O_COMP` VARCHAR(14) NULL,
            `NRO_OC_COMP` VARCHAR(20) NULL,
            `id_shop_group` int(11) unsigned NOT NULL DEFAULT 1,
            `id_shop` int(11) unsigned NOT NULL DEFAULT 1,
            `current_state` int(10) unsigned NOT NULL,
            `secure_key` varchar(32) NOT NULL DEFAULT -1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_cart`),
            KEY `id_shop_group` (`id_shop_group`),
            KEY `current_state` (`current_state`),
            KEY `id_shop` (`id_shop`),
            KEY `date_add` (`date_add`)
            );
          ';

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
        return true;
    }
    return true;
}
