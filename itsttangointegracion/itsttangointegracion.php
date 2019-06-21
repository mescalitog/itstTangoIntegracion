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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/includes/constants.php';
require_once dirname(__FILE__) . '/classes/tangoApi.php';
require_once dirname(__FILE__) . '/classes/forms/configFormTabs.php';
require_once dirname(__FILE__) . '/classes/helpers.php';
require_once dirname(__FILE__) . '/classes/ventas/pedidos.php';
require_once dirname(__FILE__) . '/classes/ventas/talonarios.php';
require_once dirname(__FILE__) . '/classes/general/general.php';
require_once dirname(__FILE__) . '/classes/orderExtended.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Ventas as Ventas;
use ItSt\PrestaShop\Tango\General as General;
use ItSt\PrestaShop\Tango\Forms as Forms;

class ItstTangoIntegracion extends Module
{
    protected $config_form = false;
    public $logger = null;
    public $api_version = null;
    public $selectedTab = false;

    // Manejo de Errores
    protected $errors = array();
    protected $successes = array();
    protected $warnings = array();

    public function setConfirmationMessage($message)
    {
        $this->successes[] = $message;
        return true;
    }

    public function setErrorMessage($message)
    {
        $this->errors[] = $message;
        return false;
    }

    public function setWarningMessage($message)
    {
        $this->_warnings[] = $message;
        return false;
    }

    public function __construct()
    {
        $this->name = 'itsttangointegracion';
        // $this->className = 'ItstTangoIntegracion';
        $this->tab = 'administration';
        $this->version = '1.3.4';
        $this->author = 'itstuff.com.ar';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();


        $this->displayName = $this->l('ItStuff Integración con Axoft Tango');
        $this->description = $this->l('Modulo de integración para Axoft Tango');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        // ItStuff Begins
        $this->logger = Helpers\ItStLogger::instance();

        $this->display = 'view';

        // Chequeo la configuracion
        $this->api_version = Configuration::get(Consts\ITST_TANGO_VERSION, null);
        if (!isset($this->api_version) || ($this->api_version == null)) {
            $this->warning = $this->l(
                'This Module needs to be configured before you can use it.',
                $this->name
            );
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue(Consts\ITST_TANGO_LIVE_MODE, false);

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            // Agrego js y css para el front
            $this->registerHook('header') &&
            // Agrego js y css para el back
            $this->registerHook('backOfficeHeader') &&
            // Para sincronizar con tango
            $this->registerHook('actionOrderStatusPostUpdate') &&
            // Para mantener la lista de transportes actualizada
            $this->registerHook('actionCarrierUpdate') &&
            // Para ingresar el numero de OC (solo queda una)
            $this->registerHook('displayCheckoutSummaryTop') &&
            // Elegir fecha de entrega
            $this->registerHook('displayBeforeCarrier');
        // Registracion y Clientes
        /*
            $this->registerHook('additionalCustomerFormFields') &&
            $this->registerHook('validateCustomerFormFields') &&
            $this->registerHook('actionAuthentication') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('actionCustomerAccountUpdate');
            */
    }

    public function uninstall()
    {
        Configuration::deleteByName(Consts\ITST_TANGO_LIVE_MODE);
        Configuration::deleteByName(Consts\ITST_TANGO_LOG_SEVERITY);
        Configuration::deleteByName(Consts\ITST_TANGO_LOG_TOFILE);
        Configuration::deleteByName(Consts\ITST_TANGO_API_URL);
        Configuration::deleteByName(Consts\ITST_TANGO_API_KEY);
        Configuration::deleteByName(Consts\ITST_TANGO_NAME);
        Configuration::deleteByName(Consts\ITST_TANGO_VERSION);
        Configuration::deleteByName(Consts\ITST_TANGO_ENVIRONMENT);
        // Pedidos
        Configuration::deleteByName(Consts\ITST_TANGO_ORDERS_SYNC);
        Configuration::deleteByName(Consts\ITST_TANGO_ORDERS_COMP_STK);
        Configuration::deleteByName(Consts\ITST_TANGO_ORDERS_TALONARIO);
        Configuration::deleteByName(Consts\ITST_TANGO_ORDERS_RETRIES);
        Configuration::deleteByName(Consts\ITST_TANGO_ORDERS_VALID_DAYS);

        Configuration::deleteByName(Consts\ITST_TANGO_PRODUCT_SYNC);
        // Determina si se va a chequear que la referencia exista en Tango
        Configuration::deleteByName(Consts\ITST_TANGO_VALIDATE_PRODUCT_REFERENCE);
        // Determina si se va a sincronizar el stock
        Configuration::deleteByName(Consts\ITST_TANGO_STOCK_SYNC);
        Configuration::deleteByName(Consts\ITST_TANGO_STOCK_SYNC_DEPOSIT);

        //Precios
        Configuration::deleteByName(Consts\ITST_TANGO_PRICES_SYNC);
        Configuration::deleteByName(Consts\ITST_TANGO_PRICES_SYNC_PRODUCTS);
        Configuration::deleteByName(Consts\ITST_TANGO_PRICES_SYNC_COMBINATIONS);
        Configuration::deleteByName(Consts\ITST_TANGO_MAX_ERRORS);

        Configuration::deleteByName(Consts\ITST_TANGO_INVENTORY_SYNC);

        // Shipping Costs
        Configuration::deleteByName(Consts\ITST_TANGO_SHIPPING_SYNC);
        Configuration::deleteByName(Consts\ITST_TANGO_SHIPPING_PRODUCT);

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() &&
            // Desinstalo los hooks
            $this->unregisterHook('header') &&
            $this->unregisterHook('backOfficeHeader') &&
            $this->unregisterHook('actionOrderStatusPostUpdate') &&
            $this->unregisterHook('actionCarrierUpdate') &&
            $this->unregisterHook('displayCheckoutSummaryTop') &&
            $this->unregisterHook('displayBeforeCarrier');


        // Registracion y Clientes
        /*
            $this->unregisterHook('additionalCustomerFormFields') &&
            $this->unregisterHook('validateCustomerFormFields') &&
            $this->unregisterHook('actionAuthentication') &&
            $this->unregisterHook('actionCustomerAccountAdd') &&
            $this->unregisterHook('actionCustomerAccountUpdate');
            */
    }

    public function getContent()
    {
        $output = null;
        Forms\ItstConfigFormsTabs::init($this);

        $store_url = $this->context->link->getBaseLink();
        // variables para el template
        $tplVars = array(
            'module_dir' => $this->_path,
            'module_local_dir' => $this->local_path,
            'ITST_TANGO_form' => './index.php?tab=AdminModules&configure=' . $this->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules')
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name,
            'products_cron' => $store_url
                . $this->_path . '/products-cron.php?token=' . Helpers\ItStTools::getSecureKey()
                . '&id_shop=' . $this->context->shop->id,
            'prices_cron' => $store_url . $this->_path . '/prices-cron.php?token=' . Helpers\ItStTools::getSecureKey()
                . '&id_shop=' . $this->context->shop->id,

            "tabs" => Forms\ItstConfigFormsTabs::getConfigTabs(),
            "selectedTab" => $this->getSelectedTab()
        );

        if (count($this->successes)) {
            foreach ($this->successes as $confirmation) {
                $output .= $this->displayConfirmation($confirmation);
            }
        }
        // Proceso Errores
        if (count($this->errors)) {
            foreach ($this->errors as $err) {
                $output .= $this->displayError($err);
            }
        }

        // Asigno variables para el template
        $this->context->smarty->assign($tplVars);
        $output = $output . $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/configure-tabs.tpl'
        );

        return $output;
    }

    /**
     * Devuelve el tag seleccionado
     */
    protected function getSelectedTab()
    {
        $this->selectedTab = Forms\ItstConfigForms::$selectedTab;
        if ($this->selectedTab) {
            return $this->selectedTab;
        }
        if (Tools::getValue("selected_tab")) {
            return Tools::getValue("selected_tab");
        }
        return "general-settings";
    }
    /**** HOOKS START  */
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->registerStylesheet(
            'module-front-css',
            $this->_path . '/views/css/front.css',
            [ 'media' => 'all', 'priority' => 200, ]
        );
        $this->context->controller->registerJavascript(
            'module-front-js', // Unique ID
            $this->_path . '/views/js/front.js', // JS path
            [
                'priority' => 200,
                'attribute' => 'sync',
            ]
        );

        // Font Awesome
        $this->context->controller->registerJavascript(
            'font-awesome-js', // Unique ID
            'https://use.fontawesome.com/2b8d3dc4a1.js', // JS path
            ['server' => 'remote', 'position' => 'bottom', 'priority' => 150] // Arguments
        );
        // Date Picker
        $this->context->controller->registerJavascript(
            'module-moment-js', // Unique ID
            $this->_path . '/views/js/moment.min.js', // JS path
            ['priority' => 100, 'attribute' => 'sync',]
        );
        $this->context->controller->registerJavascript(
            'module-moment-locale-es-js', // Unique ID
            $this->_path . '/views/js/es.js', // JS path
            ['priority' => 150, 'attribute' => 'sync',]
        );

        $this->context->controller->registerJavascript(
            'tempusdominus-js', // Unique ID
            'https://cdnjs.cloudflare.com/ajax/libs/'
                . 'tempusdominus-bootstrap-4/5.0.0-alpha14/js/tempusdominus-bootstrap-4.min.js', // JS path
            ['server' => 'remote', 'position' => 'bottom', 'priority' => 150] // Arguments
        );
        $this->context->controller->registerStylesheet(
            'tempusdominus-css',
            'https://cdnjs.cloudflare.com/ajax/libs/'
                . 'tempusdominus-bootstrap-4/5.0.0-alpha14/css/tempusdominus-bootstrap-4.min.css',
            ['server' => 'remote', 'media' => 'all', 'priority' => 200,]
        );
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        // Solo agrega esto en la configuracion
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/itst-backoffice.js');
            $this->context->controller->addCSS($this->_path . 'views/css/itst-backoffice.css');
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $syncOrders = Configuration::get(Consts\ITST_TANGO_ORDERS_SYNC, false);
        $newOrderStatus = $params['newOrderStatus'];
        // El estado se considera v'alidado
        $logable = $newOrderStatus->logable;
        $this->logger->addLog(
            'hookActionOrderStatusPostUpdate;started;'
                . 'syncOrders:' . $syncOrders
                . ', logable:' . $logable
                . ', params:' . Tools::jsonEncode($params),
            Consts\SEVERITY_DEBUG,
            null,
            $this->name
        );
        if (!$syncOrders) {
            return $this->logger->addLog(
                $this->l('Module is not configured to synchronize orders.'),
                Consts\SEVERITY_WARNING,
                null,
                $this->name
            );
        }
        // Si el estado considera la orden validada
        if ($logable) {
            return Ventas\Pedidos::instance()->createPedidoOrderStatusPostUpdate($params);
        }
    }

    /**
     * Lo para mantener actulizada la lista de carriers
     */
    public function hookActionCarrierUpdate($params)
    {
        $new_carrier = $params['carrier'];

        $query = 'UPDATE ' . _DB_PREFIX_ . bqSQL('itst_tango_carriers')
            . ' SET `id_carrier` = ' . (int)$new_carrier->id
            . ' WHERE `id_carrier` = ' . (int)$params['id_carrier'];
        $this->logger->addLog(
            'A carrier has been updated',
            Consts\SEVERITY_INFO,
            null,
            $this->name
        );
        return Db::getInstance()->execute($query);
    }

    public function hookDisplayCheckoutSummaryTop($params)
    {
        // FIXME: corregir para produccion
        $syncOrders = Configuration::get(Consts\ITST_TANGO_ORDERS_SYNC, false);
        $syncOrders = 1;
        $cart_id = $params['cart']->id;
        $orderExtended = new ItSt\PrestaShop\Tango\OrdersExtended($cart_id);
        $orderExtended->id_cart = $cart_id;
        $orderExtended->save();
        if ($syncOrders) {
            $this->context->smarty->assign(array(
                'ps_version' => (float)_PS_VERSION_,
                'params' => Tools::jsonEncode($params),
                'nro_o_comp' => $orderExtended->NRO_O_COMP,
                'url' => Context::getContext()->link->getModuleLink(
                    'itsttangointegracion',
                    'ordersextended',
                    array(
                        'token' => Tools::getToken(false),
                        'id_cart' => $cart_id,
                        'action' => 'update-extended'
                    )
                )
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayCheckoutSummaryTop.tpl');
        }
    }

    public function hookDisplayBeforeCarrier($params)
    {
        // FIXME: corregir para produccion
        $syncOrders = Configuration::get(Consts\ITST_TANGO_ORDERS_SYNC, false);
        $syncOrders = 1;
        $cart_id = $params['cart']->id;
        $orderExtended = new ItSt\PrestaShop\Tango\OrdersExtended($cart_id);
        $orderExtended->id_cart = $cart_id;
        $orderExtended->save();
        if ($syncOrders) {
            $this->context->smarty->assign(array(
                'ps_version' => (float)_PS_VERSION_,
                'params' => Tools::jsonEncode($params),
                'fecha_entr' => $orderExtended->FECHA_ENTR,
                'module'     => 'itsttangointegracion',
                'fc'         => 'module',
                'controller' => 'ordersextended',
                'url'        => $this->context->link->getBaseLink(),
                'id_cart'    => $cart_id,
                'token'      => Tools::getToken(false),                
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayBeforeCarrier.tpl');
        }
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        // FIXME: Agregar campos adicionales
    }

    public function hookValidateCustomerFormFields($params, $moduleId)
    {
        // FIXME: Agregar validacion
    }

    public function hookActionAuthentication($params)
    {
        // FIXME: El usuario se autentico
    }
    public function hookActionCustomerAccountAdd($params)
    {
        // FIXME: Se agregó una cuenta
    }
    public function hookActionCustomerAccountUpdate($params)
    {
        // FIXME: Se actualizó una cuenta
    }
}
