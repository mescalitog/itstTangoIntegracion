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

namespace ItSt\PrestaShop\Tango\Forms;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/configForm.php';
require_once dirname(__FILE__) . '/../../includes/constants.php';
require_once dirname(__FILE__) . '/../general/general.php';
require_once dirname(__FILE__) . '/../helpers.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;

use Configuration;
use Tools;
use Db;
use Context;
use Module;
use Carrier;

class ItstConfigFormsInventory extends ItstConfigForms
{
    const SETTINGS_SUBMIT = 'itst_config_inventory_submit';
    const MIN_API_VERSION = '1.0';

    protected static $module = false;

    public static function init($module, $context = null)
    {
        if (self::$module == null) {
            self::$module = $module;
        }
        parent::init(self::$module, $context);
        return self::$module;
    }

    /**
     *
     */
    public static function getContent()
    {
        $output = null;
        $api_version = Configuration::get(Consts\ITST_TANGO_VERSION, null);

        // Chequeo el acceso a la API
        if (!isset($api_version) || ($api_version == null)) {
            return $output .= '<div class="alert alert-warning">'
                . '<p>'
                . self::$module->l('There is no API status information', 'itsttangointegracion') . ' '
                . '<br />' . self::$module->l(
                    'Configure the API in General Settings and get a valid response to enable this tab',
                    'itsttangointegracion'
                ) . ' '
                . '</p>'
                . '</div>';
        } elseif (version_compare($api_version, self::MIN_API_VERSION) < 0) {
            return $output .= '<div class="alert alert-warning">'
                . '<p>'
                . self::$module->l('Api version does not meet minimum requirements', 'itsttangointegracion') . ' '
                . '<br />' .
                sprintf(
                    self::$module->l(
                        'This module requires at least API version %1$s and current version is %2$s',
                        'itsttangointegracion'
                    ),
                    self::MIN_API_VERSION,
                    $api_version
                )
                . ' '
                . '</p>'
                . '</div>';
        }
        // Configuración General
        if (Tools::isSubmit(self::SETTINGS_SUBMIT)) {
            parent::setSelectedTab("inventory-settings");
            self::postProcessConfigGeneral();
        }
        $output .= parent::renderForm(self::getFormFields(), self::getFormValues(), self::SETTINGS_SUBMIT);
        return $output;
    }

    // Post Procesa configuracion General
    protected static function postProcessConfigGeneral()
    {
        $form_values = self::getFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        return self::$module->setConfirmationMessage(self::$module->l('Settings updated.', self::$module->name));
    }

    protected static function getCronLink()
    {
        $context = Context::getContext();
        $store_url = $context->link->getBaseLink();
        $path = __PS_BASE_URI__ . 'modules/' . self::$module->name . '/';
        $inventory_cron = $store_url . $path . 'inventory-cron.php?token='
            . Helpers\ItStTools::getSecureKey() . '&id_shop=' . $context->shop->id;
        return $inventory_cron;
    }

    protected static function getFormFields()
    {
        $enabled = Configuration::get(Consts\ITST_TANGO_INVENTORY_SYNC, false);
        $cronlink = Tools::htmlentitiesUTF8(self::getCronLink());
        $cron_info = '<h4>' . self::$module->l('How to synchronize inventory') . '</h4>'
            . self::$module->l('Enable this feature to synchronize inventory');

        $cron_info_link = '<h4>' . self::$module->l('How to synchronize inventory') . '</h4>'
            . 'Use the following link to create a cron job for inventory synchronization. <br>'
            . '<a href="' . $cronlink . '" target="_blank">' . $cronlink . '</a><br>'
            . self::$module->l(
                'Ask your hosting provider to setup a "Cron job"'
                    . ' to load the above URL at the time you would like'
            );

        $description = ($enabled) ? $cron_info_link : $cron_info;
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Inventory Settings', 'itsttangointegracion'),
                    'icon' => 'fa-product-hunt',
                ),
                'description' => $description,
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Inventory', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_INVENTORY_SYNC,
                        'is_bool' => true,
                        'desc' => self::$module->l('Enable the cron job that synchronize inventory'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    )
                ),
                'submit' => array(
                    'title' => self::$module->l('Save', 'itsttangointegracion'),
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right'
                )
            ),
        );
        return array($form);
    }

    protected static function getFormValues()
    {
        return array(
            Consts\ITST_TANGO_INVENTORY_SYNC => Configuration::get(Consts\ITST_TANGO_INVENTORY_SYNC, false)
        );
    }
}
