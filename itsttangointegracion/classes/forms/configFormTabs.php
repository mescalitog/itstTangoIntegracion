<?php
/*
* 2007-2015 PrestaShop
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
* @author    itstuff.com.ar
* @copyright Copyright (c) ItStuff [https://itstuff.com.ar]
* @license   https://itstuff.com.ar/licenses/commercial-1.0.html Commercial License
*/

namespace ItSt\PrestaShop\Tango\Forms;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/configFormGeneral.php';
require_once dirname(__FILE__) . '/configFormCarriers.php';
require_once dirname(__FILE__) . '/configFormPrices.php';
require_once dirname(__FILE__) . '/configFormOrders.php';
require_once dirname(__FILE__) . '/configFormInventory.php';
require_once dirname(__FILE__) . '/configFormProducts.php';
require_once dirname(__FILE__) . '/configFormCustomers.php';
require_once dirname(__FILE__) . '/../../includes/constants.php';

use Configuration;
use HelperForm;
use Tools;
use AdminController;
use ItSt\PrestaShop\Tango\Constantes as Consts;

class ConfigFormTabs
{
    protected static $module = null;

    public static function init($module)
    {
        self::$module = $module;
        ConfigFormGeneral::init($module);
        ConfigFormCarriers::init($module);
        ConfigFormOrders::init($module);
        ConfigFormPrices::init($module);
        ConfigFormInventory::init($module);
        ConfigFormProducts::init($module);
        ConfigFormCustomers::init($module);

        return self::$module;
    }

    /**
     * Devuelve la lista de tabs en la configuracion
     * Los tabs agrupan la configuracion segun temas como general, transporte, etc.
     */

    public static function getConfigTabs()
    {
        // Translations Test
        $version = Configuration::get(Consts\ITST_TANGO_VERSION, null);
        $tabs = array();
        $tabs[] = array(
            "id" => "general-settings",
            'title' => self::$module->l('General Settings', 'ConfigFormTabs'),
            "content" => ConfigFormGeneral::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "carriers-settings",
            'title' => self::$module->l('Carriers Settings', 'ConfigFormTabs'),
            "content" => ConfigFormCarriers::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "prices-settings",
            'title' => self::$module->l('Prices Settings', 'ConfigFormTabs'),
            "content" => ConfigFormPrices::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "products-settings",
            'title' => self::$module->l('Products Settings', 'ConfigFormTabs'),
            "content" => ConfigFormProducts::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "inventory-settings",
            'title' => self::$module->l('Inventory Settings', 'ConfigFormTabs'),
            "content" => ConfigFormInventory::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "orders-settings",
            'title' => self::$module->l('Orders Settings', 'ConfigFormTabs'),
            "content" => ConfigFormOrders::getContent(),
            "version" => $version
        );

        $tabs[] = array(
            "id" => "customers-settings",
            'title' => self::$module->l('Customers Settings', 'ConfigFormTabs'),
            "content" => ConfigFormCustomers::getContent(),
            "version" => $version
        );

        return $tabs;
    }
}
