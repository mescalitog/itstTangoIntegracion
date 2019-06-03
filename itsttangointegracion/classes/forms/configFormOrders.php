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
require_once dirname(__FILE__) . '/../ventas/listas.php';
require_once dirname(__FILE__) . '/../../includes/constants.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Ventas as Ventas;
use Configuration;
use Tools;
use Context;
use Module;

class ItstConfigFormsOrders extends ItstConfigForms
{
    const CONFIG_ORDERS_ENABLE_SUBMIT = 'submitTangoIntegracionConfigOrders';
    const MIN_API_VERSION = '1.3';

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

        if ((bool)Tools::isSubmit(self::CONFIG_ORDERS_ENABLE_SUBMIT)) {
            self::postProcess();
            parent::setSelectedTab('orders-settings');
        }

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

        $output = $output
            . parent::renderForm(
                self::getFormFields(),
                self::getFormValues(),
                self::CONFIG_ORDERS_ENABLE_SUBMIT
            );
        return $output;
    }
    /**
     * Formulario de Configuracion General
     */
    public static function getFormFields()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Orders Settings', 'itsttangointegracion'),
                    'icon' => 'icon-cogs',
                ),
                'description' => self::$module->l(
                    'The orders will be synchronized with Tango for all status with'
                        . ' "Consider the associated order as validated." enabled',
                    'itsttangointegracion'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Orders', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_ORDERS_SYNC,
                        'is_bool' => true,
                        'desc' => self::$module->l('Should this module create orders?'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Stock Compromise', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_ORDERS_COMP_STK,
                        'is_bool' => true,
                        'desc' => self::$module->l('Should the orders compromise inventory stock?'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'name' => Consts\ITST_TANGO_ORDERS_TALONARIO,
                        'label' => self::$module->l('Orders Talonario'),
                        'desc' => self::$module->l(
                            'Select the talonario this module will use to create orders',
                            'itsttangointegracion'
                        ),
                        'options' => array(
                            'query' => self::getTalonariosOptions(),
                            'id' => 'TALONARIO', 'name' => 'DESCRIP'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'name' => Consts\ITST_TANGO_ORDERS_RETRIES,
                        'class' => 'input fixed-width-md',
                        'suffix' => 'retries',
                        'label' => self::$module->l('Maximun number of retries if errors'),
                        'desc' => self::$module->l(
                            'The maximun number of retries if there are errors in orders synchronization',
                            'itsttangointegracion'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'name' => Consts\ITST_TANGO_ORDERS_VALID_DAYS,
                        'class' => 'input fixed-width-md',
                        'suffix' => 'days',
                        'label' => self::$module->l('Number of days an errored order will be valid'),
                        'desc' => self::$module->l(
                            'Enter the number of days an errorer order will be valid for retries',
                            'itsttangointegracion'
                        )
                    ),
                ),
                'submit' => array(
                    'title' =>
                    self::$module->l('Save', 'itsttangointegracion'),
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right'
                )
            ),
        );
        return array($form);
    }

    /**
     * Valores del Formulario de Configuracion General
     */
    public static function getFormValues()
    {
        return array(
            Consts\ITST_TANGO_ORDERS_SYNC => Configuration::get(Consts\ITST_TANGO_ORDERS_SYNC, false),
            Consts\ITST_TANGO_ORDERS_COMP_STK => Configuration::get(Consts\ITST_TANGO_ORDERS_COMP_STK, false),
            Consts\ITST_TANGO_ORDERS_TALONARIO => Configuration::get(Consts\ITST_TANGO_ORDERS_TALONARIO, false),
            Consts\ITST_TANGO_ORDERS_RETRIES => Configuration::get(Consts\ITST_TANGO_ORDERS_RETRIES, false),
            Consts\ITST_TANGO_ORDERS_VALID_DAYS => Configuration::get(Consts\ITST_TANGO_ORDERS_VALID_DAYS, false),
        );
    }

    protected static function postProcess()
    {
        $form_values = self::getFormValues();
        self::$module->logger->addLog(
            self::$module->l('Configuration Updated.'),
            Consts\SEVERITY_INFO,
            null,
            self::$module->name
        );
        foreach (array_keys((array)$form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        self::$module->_postSuccesses[] =
            self::$module->l('Settings updated.', self::$module->name);
        return true;
    }

    /**
     * Obtiene las listas de precios disponibles
     */
    protected static function getTalonariosOptions()
    {
        $data = Ventas\Talonarios::getTalonarios(array('COMPROB' => 'PED'));
        return $data;
    }
}
