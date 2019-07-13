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
function upgrade_module_1_4_0($module, $install = false)
{
    $sql = array();
    if ($module->active && !$install) {
        // 1.3.8 estados
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_tango_ps_status` (
        `id_tango_ps_status` INT NOT NULL AUTO_INCREMENT,
        `id_tango_status` INT(10) UNSIGNED NULL,
        `id_order_state` INT(10) UNSIGNED NULL,
        PRIMARY KEY (`id_tango_ps_status`),
        INDEX `idx_tango_status` (`id_tango_status` ASC),
        CONSTRAINT `fk_itstTango_order_state`
            FOREIGN KEY (`id_order_state`)
            REFERENCES `' . _DB_PREFIX_ . 'order_state` (`id_order_state`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION);
        ';
        // 1.3.8 clientes
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_customer_extended` (
            `id_customer` INT(10) UNSIGNED NOT NULL,
            `COD_CLIENT` VARCHAR(6) NULL,
            `COD_VENDED` VARCHAR(10) NULL,
            `COND_VTA` INT(10) UNSIGNED NULL,
            `CUIT` VARCHAR(20) NULL,
            `CUPO_CREDI` DECIMAL(22,7) NULL,
            `NOM_COM` VARCHAR(20) NULL,
            `RAZON_SOCI` VARCHAR(60) NULL,
            `NRO_LISTA` INT(10) UNSIGNED NULL,
            `id_shop_group` int(11) unsigned NOT NULL DEFAULT 1,
            `id_shop` int(11) unsigned NOT NULL DEFAULT 1,
            `secure_key` varchar(32) NOT NULL DEFAULT -1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
        
          PRIMARY KEY (`id_customer`),
          INDEX `idx_itst_customer_extended_COD_CLIENT` (`COD_CLIENT` ASC),
          INDEX `idx_itst_customer_extended_CUIT` (`CUIT` ASC),
          CONSTRAINT `fk_itst_customer_extended_customer`
            FOREIGN KEY (`id_customer`)
            REFERENCES `' . _DB_PREFIX_ . 'customer` (`id_customer`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION);
        ';
        

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
    }

    return (
        $module->registerHook('actionCustomerAccountAdd') && 
        $module->registerHook('displayAdminCustomers')
    );
}
