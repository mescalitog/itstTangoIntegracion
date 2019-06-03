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
require_once dirname(__FILE__) . '/../general/general.php';
require_once dirname(__FILE__) . '/../../includes/constants.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\General as General;
use Configuration;
use Tools;

class ItstConfigFormsGeneral extends ItstConfigForms
{
    const CONFIG_GENERAL_SUBMIT = 'submitTangoIntegracionConfigGeneral';

    protected static $module = null;

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

        if ((bool)Tools::isSubmit(self::CONFIG_GENERAL_SUBMIT)) {
            parent::setSelectedTab("general-settings");
            self::postProcess();
        }
        $output = $output
            . parent::renderForm(
                self::getFormFields(),
                self::getFormValues(),
                self::CONFIG_GENERAL_SUBMIT
            );
        return $output;
    }

    protected static function postProcess()
    {
        $form_values = self::getFormValues();
        self::$module->logger->addLog(
            self::$module->l('General settings updated.'),
            Consts\SEVERITY_INFO,
            null,
            self::$module->name
        );
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        // Verifico si pude llamar a la api y su versión
        $status = General\General::status();
        if (isset($status['version']) && isset($status['environment'])) {
            Configuration::updateValue(Consts\ITST_TANGO_VERSION, $status['version']);
            Configuration::updateValue(Consts\ITST_TANGO_ENVIRONMENT, $status['environment']);
            return self::$module->setConfirmationMessage(
                self::$module->l(
                    'Successfully connected to the api. Current version is ',
                    self::$module->name
                )
                    . $status['version'] . ' running on ' . $status['environment'] . ' environment'
            );
        } else {
            Configuration::updateValue(Consts\ITST_TANGO_LIVE_MODE, false);
            self::$module->logger->addLog(
                'configuration; status check failed' . Tools::jsonEncode($status),
                Consts\SEVERITY_ERROR,
                null,
                self::$module->name
            );
            self::$module->setErrorMessage(
                self::$module->l(
                    'An error occurred trying to access the API. Please check configuration parameters.',
                    self::$module->name
                )
            );
        }
    }

    /**
     * Formulario de Configuracion General
     */
    public static function getFormFields()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => self::$module->l('Settings', 'itsttangointegracion'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Live mode'),
                        'name' => Consts\ITST_TANGO_LIVE_MODE,
                        'is_bool' => true,
                        'desc' => self::$module->l('Use this module in live mode'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        ),
                    ),
                    array('type' => 'free', 'name' => 'status_check', 'col' => 9, 'offset' => 0),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => Consts\ITST_TANGO_API_URL,
                        'label' => self::$module->l('WS Url', 'itsttangointegracion'),
                        'placeholder' => self::$module->l('Enter API url', 'itsttangointegracion'),
                        'required' => true,
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'name' => Consts\ITST_TANGO_API_KEY,
                        'label' => self::$module->l('API-KEY', 'itsttangointegracion'),
                        'placeholder' => self::$module->l(
                            'Enter API key provided by itstuff.com.ar',
                            'itsttangointegracion'
                        ),
                        'required' => true,
                    ),
                    'input' => array(
                        'type' => 'select',
                        'label' => self::$module->l('Stop after errors', 'itsttangointegracion'),
                        'name' => Consts\ITST_TANGO_MAX_ERRORS,
                        'desc' => self::$module->l(
                            'Automatic Process will stop after reach the configured amount of errors'
                        ),
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => 5, 'name' => '5'),
                                array('id' => 20, 'name' => '20'),
                                array('id' => 50, 'name' => '50'),
                                array('id' => 100, 'name' => '100'),
                                array('id' => 500, 'name' => '500'),
                            ),
                            'id' => 'id', 'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => self::$module->l('Log serverity'),
                        'name' => Consts\ITST_TANGO_LOG_SEVERITY,
                        'desc' => self::$module->l('Determine the severity of the generated logs'),
                        'options' => array(
                            'query' => array(
                                array('id' => Consts\SEVERITY_DEBUG, 'name' => 'DEBUG'),
                                array('id' => Consts\SEVERITY_INFO, 'name' => 'INFO'),
                                array('id' => Consts\SEVERITY_WARNING, 'name' => 'WARNING'),
                                array('id' => Consts\SEVERITY_ERROR, 'name' => 'ERROR'),
                            ),
                            'id' => 'id', 'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::$module->l('Logs to file'),
                        'name' => Consts\ITST_TANGO_LOG_TOFILE,
                        'is_bool' => true,
                        'desc' => self::$module->l('Should this module generate a log file?'),
                        'values' => array(
                            array('id' => 'active_on', 'value' => true, 'label' => self::$module->l('Enabled')),
                            array('id' => 'active_off', 'value' => false, 'label' => self::$module->l('Disabled'))
                        )
                    )
                ),
                'submit' => array(
                    'title' => self::$module->l('Save', 'itsttangointegracion'),
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right'
                )
            ),
        );

        if (Configuration::get(Consts\ITST_TANGO_LIVE_MODE)) {
            $form['form']['input'][] = array('type' => 'free', 'name' => 'check_api', 'col' => 9, 'offset' => 0);
        }

        return array($form);
    }

    /**
     * Valores del Formulario de Configuracion General
     */
    public static function getFormValues()
    {
        $version = Configuration::get(Consts\ITST_TANGO_VERSION, null);
        $envirnoment = Configuration::get(Consts\ITST_TANGO_ENVIRONMENT, null);
        $statusOk = '<div class="alert alert-success">'
            . '<p>'
            . self::$module->l(
                'Last status check returns version:' . $version
                    . ' environment:' . $envirnoment,
                'itsttangointegracion'
            ) . ' '
            . '</p>'
            . '</div>';
        $statusFail = '<div class="alert alert-warning">'
            . '<p>'
            . self::$module->l('There is no API status information', 'itsttangointegracion') . ' '
            . '<br />' . self::$module->l(
                'Submit this form to perform an API status check',
                'itsttangointegracion'
            ) . ' '
            . '</p>'
            . '</div>';
        $statusCheck = (($version == null) ? $statusFail : $statusOk);
        return array(
            Consts\ITST_TANGO_LIVE_MODE => Configuration::get(Consts\ITST_TANGO_LIVE_MODE, true),
            Consts\ITST_TANGO_LOG_SEVERITY => Configuration::get(Consts\ITST_TANGO_LOG_SEVERITY, false),
            Consts\ITST_TANGO_LOG_TOFILE => Configuration::get(Consts\ITST_TANGO_LOG_TOFILE, false),
            Consts\ITST_TANGO_API_URL => Configuration::get(Consts\ITST_TANGO_API_URL, null),
            Consts\ITST_TANGO_API_KEY => Configuration::get(Consts\ITST_TANGO_API_KEY, null),
            Consts\ITST_TANGO_NAME => Configuration::get(Consts\ITST_TANGO_NAME, null),
            Consts\ITST_TANGO_VALIDATE_PRODUCT_REFERENCE =>
            Configuration::get(Consts\ITST_TANGO_VALIDATE_PRODUCT_REFERENCE, null),
            Consts\ITST_TANGO_MAX_ERRORS => Configuration::get(Consts\ITST_TANGO_MAX_ERRORS, 5),
            'check_api' =>
            '<div class="alert alert-info">'
                . self::$module->l(
                    'Check the api calling status method and return the results.',
                    'itsttangointegracion'
                ) . ' '
                . self::$module->l(
                    'Version.',
                    'itsttangointegracion'
                ) . ' '
                . '</div>',
            'status_check' => $statusCheck,
        );
    }
}
