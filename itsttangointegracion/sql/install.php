<?php
/**
 * 2007-2019  PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author    itstuff <info@itstuff.com.ar>
 *  @copyright Copyright 2019 (c) ItStuff [https://itstuff.com.ar]
 *  @license commercial license contact itstuff for details
 *
 */

$sql = array();

$sql[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'itst_tango_carriers` (
    `id_carrier_transporte` INT NOT NULL AUTO_INCREMENT,
    `COD_TRANSP` VARCHAR(10) NULL,
    `id_carrier` INT(10) UNSIGNED NULL,
    `id_shop` int(11) NULL,
    `id_shop_group` int(11) NULL,    
    PRIMARY KEY (`id_carrier_transporte`),
    INDEX `fk_itstTangoCarriers_carrier_idx` (`id_carrier` ASC),
    CONSTRAINT `fk_itstTangoCarriers_carrier`
      FOREIGN KEY (`id_carrier`)
      REFERENCES `' . _DB_PREFIX_ . 'carrier` (`id_carrier`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION);
  ';

$sql[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'itst_prices_list` (
    `id_prices_list` INT NOT NULL AUTO_INCREMENT,
    `order` INT NULL,
    `NRO_DE_LIS` INT NULL,
    `ID_MONEDA` INT NULL,
    `id_shop` int(11) NULL,
    `id_shop_group` int(11) NULL,
    PRIMARY KEY (`id_prices_list`),
    INDEX `itst_prices_list_order` (`order` ASC));
  ';

$sql[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'itst_sync_order` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_order` INT(11) NULL,
    `id_order_state` INT NULL,
    `ID_GVA21` INT NULL,
    `NRO_PEDIDO` VARCHAR(14) NULL,
    `TALON_PED` INT NULL,
    `error_code` INT NULL,
    `error_message` TEXT NULL,
    `retries` INT NULL DEFAULT 0,
    `valid_until` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `itst_sync_order_order` (`id_order` ASC));';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
