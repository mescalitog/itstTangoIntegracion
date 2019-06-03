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
require_once dirname(__FILE__) . '/../../services/pricesServices.php';
require_once dirname(__FILE__) . '/../ventas/listas.php';
require_once dirname(__FILE__) . '/../general/general.php';
require_once dirname(__FILE__) . '/../helpers.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Ventas as Ventas;
use ItSt\PrestaShop\Tango\General as General;
use ItSt\PrestaShop\Tango\Prices as Prices;
use ItSt\PrestaShop\Tango\Helpers as Helpers;

use Configuration;
use Tools;
use HelperList;
use Db;
use Context;

class ItstConfigFormsPrices extends ItstConfigForms
{
    const SETTINGS_SUBMIT = 'itst_config_lists_submit';
    const ADD_TO_LIST = 'itst_config_lists_add';
    const SUBMIT_ADD_TO_LIST = 'itst_config_lists_add_submit';
    const UPDATE_LIST = 'updateItstTangoIntegracion';
    const SUBMIT_UPDATE_LIST = 'itst_config_lists_update_submit';
    const SUBMIT_DELETE_LIST = 'deleteItstTangoIntegracion';
    const MIN_API_VERSION = '1.0';

    const ISO_WARNING =
    '<div class="alert alert-info">'
        . 'The Currency Code in Tango must be set as ISO 4217 code to update exchange rates.'
        . '</div>';

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
        $submit_ok = null;
        $api_version = Configuration::get(Consts\ITST_TANGO_VERSION, null);
        $context = Context::getContext();

        // Chequeo el acceso a la API
        if (!isset($api_version) || ($api_version == null)) {
            return $output .= '<div class="alert alert-warning">'
                . '<p>'
                . self::$module->l('There is no API status information', 'itsttangointegracion') . ' '
                . '<br />'
                . self::$module->l(
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
            parent::setSelectedTab("prices-settings");
            self::postProcessConfigGeneral();
        } elseif (Tools::isSubmit(self::SUBMIT_ADD_TO_LIST)) {
            parent::setSelectedTab("prices-settings");
            $submit_ok = self::postProcessNewRule();
        } elseif (Tools::isSubmit(self::SUBMIT_UPDATE_LIST)) {
            parent::setSelectedTab("prices-settings");
            $submit_ok = self::postProcessUpdateRule();
        } elseif (Tools::isSubmit(self::SUBMIT_DELETE_LIST)) {
            parent::setSelectedTab("prices-settings");
            $submit_ok = self::postProcessDeleteRule();
        }

        // Estoy Mostrando el form
        if ((Tools::isSubmit(self::ADD_TO_LIST)
                || Tools::isSubmit(self::SUBMIT_ADD_TO_LIST)
                || Tools::isSubmit(self::UPDATE_LIST))
            && (!isset($submit_ok) || !$submit_ok)
        ) {
            parent::setSelectedTab("prices-settings");
            $back_url = $context->link->getAdminLink('AdminModules', false)
                . '&configure=' . self::$module->name
                . '&tab_module=' . self::$module->tab
                . '&module_name=' . self::$module->name
                . '&selected_tab=' . 'prices-settings'
                . '&token=' . Tools::getAdminTokenLite('AdminModules');
        }

        if (((bool)Tools::isSubmit(self::SUBMIT_ADD_TO_LIST) && isset($submit_ok) && (!$submit_ok))
            || Tools::isSubmit(self::ADD_TO_LIST)
        ) {
            $output = $output
                . parent::renderForm(
                    self::getListItemForm(),
                    self::getListNewValues(),
                    self::SUBMIT_ADD_TO_LIST,
                    true,
                    $back_url
                );
        } elseif (Tools::isSubmit(self::UPDATE_LIST) && Tools::isSubmit('id_prices_list')) {
            $output = $output
                . parent::renderForm(
                    self::getListItemForm(),
                    self::getListUpdateValues(),
                    self::SUBMIT_UPDATE_LIST,
                    true,
                    $back_url
                );
        } else {
            $output .= parent::renderForm(self::getFormFields(), self::getFormValues(), self::SETTINGS_SUBMIT);
        }

        return $output . self::renderList();
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
    // Post Procesa agregar una regla
    protected static function postProcessNewRule()
    {
        $id_shop = (int)Context::getContext()->shop->id;
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $order = (int)Tools::getValue('order');
        $nro_de_list = (int)Tools::getValue('NRO_DE_LIS');
        $id_moneda = (int)Tools::getValue('ID_MONEDA');

        $query = 'INSERT INTO ' . _DB_PREFIX_ . bqSQL('itst_prices_list')
            . ' (`order`, `NRO_DE_LIS`, `ID_MONEDA`, `id_shop`, `id_shop_group`) '
            . ' VALUES (' . $order . ', ' . $nro_de_list . ', ' . $id_moneda . ', '
            . $id_shop . ', ' . $id_shop_group . ')';
        $result = Db::getInstance()->execute($query);
        if ($result != false) {
            self::$module->logger->addLog(
                self::$module->l('A new price list has been added.'),
                Consts\SEVERITY_INFO,
                null,
                self::$module->name
            );
            return self::$module->setConfirmationMessage(self::$module->l('The list has been added.'));
        }
        return self::$module->setErrorMessage(self::$module->l('An error happened: the list could not be added.'));
    }
    // Post Procesa actuaizar una regla
    protected static function postProcessUpdateRule()
    {
        $id_prices_list = (int)Tools::getValue('id_prices_list');
        if (!$id_prices_list) {
            return false;
        }
        $order = (int)Tools::getValue('order');
        $nro_de_list = (int)Tools::getValue('NRO_DE_LIS');
        $id_moneda = (int)Tools::getValue('ID_MONEDA');

        $query = 'UPDATE ' . _DB_PREFIX_ . bqSQL('itst_prices_list')
            . ' SET `order` = ' . $order
            . ', `NRO_DE_LIS` = ' . $nro_de_list
            . ', `ID_MONEDA` = ' . $id_moneda
            . ' WHERE `id_prices_list` = ' . (int)$id_prices_list;

        if ((Db::getInstance()->execute($query)) != false) {
            self::$module->logger->addLog(
                self::$module->l('A Price list has been updated.'),
                Consts\SEVERITY_INFO,
                null,
                self::$module->name
            );
            return self::$module->setConfirmationMessage(self::$module->l('The price list has been updated.'));
        }
        return self::$module->setErrorMessage(self::$module->l('The price list has not been updated'));
    }
    // Post Procesa eliminar
    protected static function postProcessDeleteRule()
    {
        $id_prices_list = (int)Tools::getValue('id_prices_list');
        if (!$id_prices_list) {
            return self::$module->setErrorMessage(implode(self::$module->l('Empty list can not been deleted')));
        }

        $query = 'DELETE FROM ' . _DB_PREFIX_ . bqSQL('itst_prices_list')
            . ' WHERE `id_prices_list` = ' . (int)$id_prices_list;
        if (!Db::getInstance()->execute($query)) {
            return self::$module->setErrorMessage(implode(self::$module->l('The list has not been deleted')));
        }
        self::$module->logger->addLog(
            self::$module->l('A Price List rule has been deleted.'),
            Consts\SEVERITY_INFO,
            null,
            self::$module->name
        );
        return self::$module->setConfirmationMessage(self::$module->l('The list has been deleted.'));
        /*
        return Tools::redirectAdmin(
            self::$module->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . self::$module->name
                . '&tab_module=' . self::$module->tab
                . '&module_name=' . self::$module->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules')
                . '&selected_tab=' . 'prices-settings'
        );
*/
    }

    protected static function getCronLink()
    {
        $context = Context::getContext();
        $store_url = $context->link->getBaseLink();
        $path = __PS_BASE_URI__ . 'modules/' . self::$module->name . '/';
        $prices_cron = $store_url . $path . 'prices-cron.php?token='
            . Helpers\ItStTools::getSecureKey() . '&id_shop=' . $context->shop->id;
        return $prices_cron;
    }

    protected static function getFormFields()
    {
        $enabled = Configuration::get(Consts\ITST_TANGO_PRICES_SYNC, false);
        $cronlink = Tools::htmlentitiesUTF8(self::getCronLink());
        $cron_info = '<h4>' . self::$module->l('How to synchronize prices') . '</h4>'
            . self::$module->l('Enable this feature to synchronize prices');

        $cron_info_link = '<h4>' . self::$module->l('How to synchronize prices') . '</h4>'
            . 'Use the following link to create a cron job for prices synchronization. <br>'
            . '<a href="' . $cronlink . '" target="_blank">' . $cronlink . '</a><br>'
            . self::$module->l(
                'Ask your hosting provider to setup a "Cron job" to load the above URL at the time you would like'
            );

        $description = ($enabled) ? $cron_info_link : $cron_info;

        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Prices Settings', 'itsttangointegracion'),
                    'icon' => 'icon-usd',
                ),
                'description' => $description,
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Prices', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_PRICES_SYNC,
                        'is_bool' => true,
                        'desc' => self::$module->l('Enable the cron job that synchronize prices'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Price for Products', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS,
                        'is_bool' => true,
                        'desc' => self::$module->l('When enable will synchronize prices for products'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Price for Combinations', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS,
                        'is_bool' => true,
                        'desc' => self::$module->l('When enable will synchronize prices for products combinations'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
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
            Consts\ITST_TANGO_PRICES_SYNC => Configuration::get(
                Consts\ITST_TANGO_PRICES_SYNC,
                false
            ),
            Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS => Configuration::get(
                Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS,
                false
            ),
            Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS => Configuration::get(
                Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS,
                false
            ),
        );
    }
    /**
     * Muestar el form para editar o actualizar una regla
     */
    public static function getListItemForm()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Price List', array(), 'itsttangointegracion'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_prices_list',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'order',
                        'required' => true,
                        'label' => self::$module->l('List evaluation order'),
                        'desc' => self::$module->l(
                            'Determine the order this list will be evaluated.',
                            'itsttangointegracion'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'NRO_DE_LIS',
                        'required' => true,
                        'label' => self::$module->l('Local money price list'),
                        'desc' => self::$module->l(
                            'Select the list this module will use to obtain local money prices',
                            'itsttangointegracion'
                        ),
                        'options' => array(
                            'query' => self::getListadePreciosOptions(),
                            'id' => 'NRO_DE_LIS', 'name' => 'NOMBRE_LIS'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'ID_MONEDA',
                        'desc' => self::$module->l('Currency', 'itsttangointegracion'),
                        'options' => array(
                            'query' => self::getMonedasOptions(),
                            'id' => 'ID_MONEDA', 'name' => 'COD_MONEDA'
                        )
                    ),
                    array('type' => 'free', 'name' => 'iso_warning', 'col' => 9, 'offset' => 0)
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

    /**
     * Devuelve los datos para una nueva regla
     */
    public static function getListNewValues()
    {
        return array(
            'id_prices_list' => null,
            'order' => 0,
            'NRO_DE_LIS' => null,
            'ID_MONEDA' => null,
            'iso_warning' => self::ISO_WARNING
        );
    }

    /**
     * Devuelve los datos para actualizar una regla
     */
    public static function getListUpdateValues()
    {
        $id_prices_list = Tools::getValue('id_prices_list');
        if (isset($id_prices_list)) {
            $priceList = Prices\ItstTangoPrices::getPricesList($id_prices_list);
            return array(
                'id_prices_list' => $priceList['id_prices_list'],
                'order' => $priceList['order'],
                'NRO_DE_LIS' => $priceList['NRO_DE_LIS'],
                'ID_MONEDA' => $priceList['ID_MONEDA'],
                'iso_warning' => self::ISO_WARNING
            );
        }
        return array(
            'id_prices_list' => null,
            'order' => 0,
            'NRO_DE_LIS' => null,
            'ID_MONEDA' => null,
            'iso_warning' => self::ISO_WARNING
        );
    }

    protected static function renderList()
    {
        $context = Context::getContext();
        $helper = new HelperList();

        $helper->title = self::$module->l('Currency and Price Lists');
        $helper->table = self::$module->name;
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_prices_list';
        $helper->actions = array('edit', 'delete');

        $values = self::getListValues();
        $helper->listTotal = count($values);
        $helper->tpl_vars = array('show_filters' => false);

        $helper->toolbar_btn['new'] = array(
            'href' => $context->link->getAdminLink('AdminModules', false)
                . '&configure=' . self::$module->name
                . '&tab_module=' . self::$module->tab
                . '&module_name=' . self::$module->name
                . '&' . self::ADD_TO_LIST . '=1&token=' . Tools::getAdminTokenLite('AdminModules')
                . '&selected_tab=' . 'prices-settings',
            'desc' => self::$module->l('Add new rule')
        );

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->currentIndex = $context->link->getAdminLink('AdminModules', false)
            . '&configure=' . self::$module->name
            . '&tab_module=' . self::$module->tab
            . '&module_name=' . self::$module->name
            . '&selected_tab=' . 'prices-settings';

        return $helper->generateList($values, self::getListColumns());
    }

    /**
     * Devuelve la configuracion de la lista de reglas de transportes
     */
    protected static function getListColumns()
    {
        return array(
            'order' => array(
                'title' => self::$module->l('List Order', 'itsttangointegracion'),
                'type' => 'number', 'orderby' => false
            ),
            'NRO_DE_LIS' => array(
                'title' => self::$module->l('List ID', 'itsttangointegracion'),
                'type' => 'number', 'orderby' => false
            ),
            'NOMBRE_LIS' => array(
                'title' => self::$module->l('List name', 'itsttangointegracion'),
                'type' => 'text', 'orderby' => false
            ),
            'ID_MONEDA' => array(
                'title' => self::$module->l('Currency ID', 'itsttangointegracion'),
                'type' => 'number', 'orderby' => false
            ),
            'COD_MONEDA' => array(
                'title' => self::$module->l('Currency Code', 'itsttangointegracion'),
                'type' => 'text', 'orderby' => false
            ),
        );
    }

    protected static function getListValues()
    {
        $context = Context::getContext();
        $id_shop = (int)$context->shop->id;
        $id_shop_group = (int)$context->shop->id_shop_group;

        $list = Prices\ItstTangoPrices::getPricesLists($id_shop, $id_shop_group);

        foreach ($list as $key => $val) {
            if ($val['NRO_DE_LIS']) {
                $priceList = Ventas\Listas::getLista($val['NRO_DE_LIS']);
                $list[$key]['NOMBRE_LIS'] = (isset($priceList))
                    ? (isset($priceList['NOMBRE_LIS']) ? $priceList['NOMBRE_LIS'] : 'SIN LISTA')
                    : 'SIN LISTA';
            }
            if ($val['ID_MONEDA']) {
                $moneda = General\Monedas::getMoneda($val['ID_MONEDA']);
                $list[$key]['COD_MONEDA'] = (isset($moneda))
                    ? (isset($moneda['COD_MONEDA']) ? $moneda['COD_MONEDA'] : '***') : '***';
            }
        };
        self::$module->logger->addLog(Tools::jsonEncode($list), Consts\SEVERITY_DEBUG, null, self::$module->name);
        return $list;
    }

    /**
     * Obtiene la lista de moneas
     */
    protected static function getMonedasOptions()
    {
        $data = General\Monedas::getMomedas();
        return $data;
    }
    /**
     * Obtiene las listas de precios disponibles
     */
    protected static function getListadePreciosOptions()
    {
        $data = Ventas\Listas::getListas();
        return $data;
    }
}
