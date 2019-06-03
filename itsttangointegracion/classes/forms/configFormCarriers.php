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
require_once dirname(__FILE__) . '/../../services/carriersServices.php';
require_once dirname(__FILE__) . '/../ventas/transportes.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Ventas as Ventas;
use ItSt\PrestaShop\Tango\Carriers as Carriers;
use TangoApi;

use Configuration;
use Tools;
use HelperList;
use Db;
use Context;
use Carrier;

class ItstConfigFormsCarriers extends ItstConfigForms
{
    const SETTINGS_CARRIERS_SUBMIT = 'itst_config_carriers_submit';
    const ADD_CARRIER_RULE = 'itst_config_carriers_add';
    const UPDATE_CARRIER_RULE = 'updateItstTangoIntegracion';
    const SUBMIT_ADD_CARRIER_RULE = 'itst_config_carriers_add_submit';
    const SUBMIT_UPDATE_CARRIER_RULE = 'itst_config_carriers_update_submit';
    const SUBMIT_DELETE_CARRIER_RULE = 'deleteItstTangoIntegracion';
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
        $submit_ok = null;
        $context = Context::getContext();
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
        if (Tools::isSubmit(self::SETTINGS_CARRIERS_SUBMIT)) {
            parent::setSelectedTab("carriers-settings");
            self::postProcessConfigGeneral();
        } elseif (Tools::isSubmit(self::SUBMIT_ADD_CARRIER_RULE)) {
            parent::setSelectedTab("carriers-settings");
            $submit_ok = self::postProcessNewRule();
        } elseif (Tools::isSubmit(self::SUBMIT_UPDATE_CARRIER_RULE)) {
            parent::setSelectedTab("carriers-settings");
            $submit_ok = self::postProcessUpdateRule();
        } elseif (Tools::isSubmit(self::SUBMIT_DELETE_CARRIER_RULE)) {
            parent::setSelectedTab("carriers-settings");
            $submit_ok = self::postProcessDeleteRule();
        }

        // Estoy Mostrando el form
        if ((Tools::isSubmit(self::ADD_CARRIER_RULE)
                || Tools::isSubmit(self::SUBMIT_ADD_CARRIER_RULE)
                || Tools::isSubmit(self::UPDATE_CARRIER_RULE))
            && (!isset($submit_ok) || !$submit_ok)
        ) {
            parent::setSelectedTab("carriers-settings");
            $back_url = $context->link->getAdminLink('AdminModules', false)
                . '&configure=' . self::$module->name
                . '&tab_module=' . self::$module->tab
                . '&module_name=' . self::$module->name
                . '&selected_tab=' . 'carriers-settings'
                . '&token=' . Tools::getAdminTokenLite('AdminModules');
        }

        if ((Tools::isSubmit(self::SUBMIT_ADD_CARRIER_RULE) && isset($submit_ok) && (!$submit_ok))
            || Tools::isSubmit(self::ADD_CARRIER_RULE)
        ) {
            $output = $output
                . parent::renderForm(
                    self::getConfigCarriersRulesForm(),
                    self::getConfigCarriersRulesNewValues(),
                    self::SUBMIT_ADD_CARRIER_RULE,
                    true,
                    $back_url
                );
        } elseif (Tools::isSubmit(self::UPDATE_CARRIER_RULE) && Tools::isSubmit('id_carrier_transporte')) {
            $output = $output
                . parent::renderForm(
                    self::getConfigCarriersRulesForm(),
                    self::getConfigCarriersRulesUpdateValues(),
                    self::SUBMIT_UPDATE_CARRIER_RULE,
                    true,
                    $back_url
                );
        } else {
            // Muestra el form con la configuracion del producto transporte
            $output .= parent::renderForm(
                self::getFormFields(),
                self::getFormValues(),
                self::SETTINGS_CARRIERS_SUBMIT
            );
        }

        return $output . self::renderRulesList();
    }

    // Post Procesa configuracion General
    protected static function postProcessConfigGeneral()
    {
        $form_values = self::getFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        $cod_articu = Tools::getValue(Consts\ITST_TANGO_SHIPPING_PRODUCT);
        if (isset($cod_articu)) {
            $product = TangoApi::instance()->getProduct($cod_articu);
            if (isset($product) && isset($product['COD_ARTICU'])) {
                return self::$module->setConfirmationMessage(
                    sprintf(
                        self::$module->l(
                            'The product %1$s - %2$s will be used to synchronize shipping costs',
                            'itsttangointegracion'
                        ),
                        $product['COD_ARTICU'],
                        $product['DESCRIPCIO']
                    ),
                    self::$module->name
                );
            } else {
                Configuration::updateValue(Consts\ITST_TANGO_SHIPPING_SYNC, false);
                Configuration::updateValue(Consts\ITST_TANGO_SHIPPING_PRODUCT, null);
                return self::$module->setErrorMessage(
                    sprintf(
                        self::$module->l(
                            '%1$s is not a valid Tango product or the API could not be reached',
                            'itsttangointegracion'
                        ),
                        $cod_articu
                    ),
                    self::$module->name
                );
            }
        }
        return self::$module->setConfirmationMessage(
            self::$module->l('Settings updated.', self::$module->name)
        );
    }

    /**
     * Formulario de Configuracion General
     */
    public static function getFormFields()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Carriers Settings', 'itsttangointegracion'),
                    'icon' => 'icon-cogs',
                ),
                'description' => self::$module->l(
                    'The shipping cost will be synchronized with tango using the product configured below',
                    'itsttangointegracion'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Synchronize Shipping Costs', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_SHIPPING_SYNC,
                        'is_bool' => true,
                        'desc' => self::$module->l('Should this module synchronize shipping costs?'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'name' => Consts\ITST_TANGO_SHIPPING_PRODUCT,
                        'label' => self::$module->l('Shipping cost product'),
                        'desc' => self::$module->l(
                            'Enter a valid Tango product. This product will be used to synchronze shipping costs',
                            'itsttangointegracion'
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

    /**
     * Valores del Formulario de Configuracion General
     */
    public static function getFormValues()
    {
        return array(
            Consts\ITST_TANGO_SHIPPING_SYNC => Configuration::get(Consts\ITST_TANGO_SHIPPING_SYNC, false),
            Consts\ITST_TANGO_SHIPPING_PRODUCT => Configuration::get(Consts\ITST_TANGO_SHIPPING_PRODUCT, false),
        );
    }
    // Post Procesa agregar una regla
    protected static function postProcessNewRule()
    {
        $id_shop = (int)Context::getContext()->shop->id;
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $id_carrier = (int)Tools::getValue('id_carrier');
        $cod_transp = Tools::getValue('COD_TRANSP');
        $result = Db::getInstance()->getRow(
            'SELECT id_carrier_transporte FROM ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers')
                . ' WHERE `id_carrier` = ' . $id_carrier
                . ' AND `id_shop` = ' . $id_shop
                . ' AND `id_shop_group` = ' . $id_shop_group
        );
        if (!$result) {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers') . '
                    (`id_carrier`, `COD_TRANSP`, `id_shop`, `id_shop_group`)
                    VALUES (' . $id_carrier . ', \'' . $cod_transp . '\',' . $id_shop . ', ' . $id_shop_group . ')';

            if (($result = Db::getInstance()->execute($query)) != false) {
                self::$module->logger->addLog(
                    self::$module->l('A new carrier rule has been added.'),
                    Consts\SEVERITY_INFO,
                    null,
                    self::$module->name
                );
                return self::$module->setConfirmationMessage(self::$module->l('The rule has been added.'));
            }
            return self::$module->setErrorMessage(
                self::$module->l('An error happened: the rule could not be added.')
            );
        }
        return self::$module->setErrorMessage(
            self::$module->l('The rule could not be added: Another rule for the same carrier already exists.')
        );
    }
    // Post Procesa actuaizar una regla
    protected static function postProcessUpdateRule()
    {
        $id_carrier_transporte = (int)Tools::getValue('id_carrier_transporte');
        if (!$id_carrier_transporte) {
            return false;
        }
        $id_shop = (int)Context::getContext()->shop->id;
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $id_carrier = (int)Tools::getValue('id_carrier');
        $cod_transp = Tools::getValue('COD_TRANSP');

        $result = Db::getInstance()->getRow(
            'SELECT id_carrier_transporte FROM ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers')
                . ' WHERE `id_carrier` = ' . $id_carrier
                . ' AND `id_shop` = ' . $id_shop
                . ' AND `id_shop_group` = ' . $id_shop_group
                . ' AND `id_carrier_transporte` <> ' . $id_carrier_transporte
        );

        if ($result) {
            return self::$module->setErrorMessage(
                self::$module->l('The rule could not be updated: Another rule for the same carrier already exists.')
            );
        }

        // $carrier_rule = Carriers\ItstTangoCarriers::getCarrierRule($id_carrier_transporte);

        $query = 'UPDATE ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers')
            . ' SET `id_carrier` = ' . $id_carrier
            . ', `COD_TRANSP` = ' . $cod_transp
            . ' WHERE `id_carrier_transporte` = ' . (int)$id_carrier_transporte;

        if ((Db::getInstance()->execute($query)) != false) {
            self::$module->logger->addLog(
                self::$module->l('A Carrier rule has been updated.'),
                Consts\SEVERITY_INFO,
                null,
                self::$module->name
            );
            return self::$module->setConfirmationMessage(self::$module->l('The rule has been updated.'));
        }
        return self::$module->setErrorMessage(self::$module->l('The rule has not been updated'));
    }
    // Post Procesa eliminar
    protected static function postProcessDeleteRule()
    {
        $id_carrier_transporte = (int)Tools::getValue('id_carrier_transporte');
        if (!$id_carrier_transporte) {
            return self::$module->setErrorMessage(implode(self::$module->l('Empty rules can not been deleted')));
        }

        $query = 'DELETE FROM ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers')
            . ' WHERE `id_carrier_transporte` = ' . (int)$id_carrier_transporte;
        if (!Db::getInstance()->execute($query)) {
            return self::$module->setErrorMessage(implode(self::$module->l('The rule has not been deleted')));
        }
        self::$module->logger->addLog(
            self::$module->l('A Carrier rule has been deleted.'),
            Consts\SEVERITY_INFO,
            null,
            self::$module->name
        );
        return self::$module->setConfirmationMessage(self::$module->l('The rule has been deleted.'));
        /*
        return Tools::redirectAdmin($context->link->getAdminLink('AdminModules', false)
            . '&configure=' . self::$module->name
            . '&tab_module=' . self::$module->tab
            . '&module_name=' . self::$module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules'))
            . '&selected_tab=' . 'carriers-settings';
            */
    }

    /**
     * Muestar el form para editar o actualizar una regla
     */
    public static function getConfigCarriersRulesForm()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Carrier Rule', array(), 'itsttangointegracion'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_carrier_transporte',
                    ),
                    array(
                        'type' => 'select',
                        'label' => self::$module->l('Carrier', array(), 'itsttangointegracion'),
                        'name' => 'id_carrier',
                        'options' => array(
                            'query' => self::getListaCarriers(),
                            'id' => 'id_carrier',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => self::$module->l('Tango Carrier', array(), 'itsttangointegracion'),
                        'name' => 'COD_TRANSP',
                        'desc' => self::$module->l('Select related Tango carrier', 'itsttangointegracion'),
                        'options' => array(
                            'query' => self::getTransportesOptions(),
                            'id' => 'COD_TRANSP',
                            'name' => 'NOMBRE_TRA'
                        )
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

    /**
     * Devuelve los datos para una nueva regla
     */
    public static function getConfigCarriersRulesNewValues()
    {
        return array(
            'id_carrier_transporte' => null,
            'id_carrier' => 0,
            'COD_TRANSP' => null
        );
    }

    /**
     * Devuelve los datos para actualizar una regla
     */
    public static function getConfigCarriersRulesUpdateValues()
    {
        $id_carrier_transporte = Tools::getValue('id_carrier_transporte');
        if (isset($id_carrier_transporte)) {
            $carrier_rule = Carriers\ItstTangoCarriers::getCarrierRule($id_carrier_transporte);
            return array(
                'id_carrier_transporte' => $carrier_rule['id_carrier_transporte'],
                'id_carrier' => $carrier_rule['id_carrier'],
                'COD_TRANSP' => $carrier_rule['COD_TRANSP']
            );
        }
        return array(
            'id_carrier_transporte' => null,
            'id_carrier' => 0,
            'COD_TRANSP' => null
        );
    }

    protected static function renderRulesList()
    {
        $helper = new HelperList();
        $context = Context::getContext();
        $helper->title = self::$module->l('Carriers Rules');
        $helper->table = self::$module->name;
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_carrier_transporte';
        $helper->actions = array('edit', 'delete');

        $values = self::getCarriersRulesListValues();
        $helper->listTotal = count($values);
        $helper->tpl_vars = array('show_filters' => false);

        $helper->toolbar_btn['new'] = array(
            'href' => $context->link->getAdminLink('AdminModules', false)
                . '&configure=' . self::$module->name
                . '&tab_module=' . self::$module->tab
                . '&module_name=' . self::$module->name
                . '&' . self::ADD_CARRIER_RULE . '=1&token=' . Tools::getAdminTokenLite('AdminModules')
                . '&selected_tab=' . 'carriers-settings',
            'desc' => self::$module->l('Add new rule')
        );

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->currentIndex = $context->link->getAdminLink('AdminModules', false)
            . '&configure=' . self::$module->name
            . '&tab_module=' . self::$module->tab
            . '&module_name=' . self::$module->name
            . '&selected_tab=' . 'carriers-settings';

        return $helper->generateList($values, self::getCarriersRulesList());
    }

    /**
     * Devuelve la configuracion de la lista de reglas de transportes
     */
    protected static function getCarriersRulesList()
    {
        return array(
            'name' => array(
                'title' => self::$module->l('Carrier\'s name', 'itsttangointegracion'),
                'type' => 'text',
                'orderby' => false
            ),
            'COD_TRANSP' => array(
                'title' => self::$module->l('Tango Carrier Code', 'itsttangointegracion'),
                'type' => 'text',
                'orderby' => false
            ),
            'NOMBRE_TRA' => array(
                'title' => self::$module->l('Tango Carrier', 'itsttangointegracion'),
                'type' => 'text',
                'orderby' => false
            ),
        );
    }

    protected static function getCarriersRulesListValues()
    {
        $context = Context::getContext();
        $id_shop = (int)$context->shop->id;
        $id_shop_group = (int)$context->shop->id_shop_group;

        $list = Carriers\ItstTangoCarriers::getCarriersRules($id_shop, $id_shop_group);

        foreach ($list as $key => $val) {
            if (!$val['name']) {
                $list[$key]['name'] = Configuration::get('PS_SHOP_NAME');
            }
            if ($val['COD_TRANSP']) {
                $transporte = Ventas\Transportes::getTransporte($val['COD_TRANSP']);
                $list[$key]['NOMBRE_TRA'] = (isset($transporte))
                    ? (isset($transporte['NOMBRE_TRA']) ? $transporte['NOMBRE_TRA'] : 'SIN TRANSPORTE')
                    : 'SIN TRANSPORTE';
            }
        };
        return $list;
    }

    /**
     * Obtiene la lista de moneas
     */
    protected static function getTransportesOptions()
    {
        $data = Ventas\Transportes::getTransportes();
        return $data;
    }

    /**
     * Devuelve la lista de transportes
     */
    protected static function getListaCarriers()
    {
        $carriers = Carrier::getCarriers(
            (int)Context::getContext()->language->id,
            true,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        foreach ($carriers as $key => $val) {
            $carriers[$key]['name'] = (!$val['name'] ? Configuration::get('PS_SHOP_NAME') : $val['name']);
        }
        return $carriers;
    }
}
