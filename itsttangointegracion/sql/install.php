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

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_tango_carriers` (
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

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_prices_list` (
    `id_prices_list` INT NOT NULL AUTO_INCREMENT,
    `order` INT NULL,
    `NRO_DE_LIS` INT NULL,
    `ID_MONEDA` INT NULL,
    `id_shop` int(11) NULL,
    `id_shop_group` int(11) NULL,
    PRIMARY KEY (`id_prices_list`),
    INDEX `itst_prices_list_order` (`order` ASC));
  ';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_sync_order` (
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

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itst_orders_extended` (
    `id_cart` INT(10) unsigned NOT NULL,
    `NRO_O_COMP` VARCHAR(14) NULL,
    `NRO_OC_COMP` VARCHAR(20) NULL,
    `FECHA_ENTR` DATE NULL,
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
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
