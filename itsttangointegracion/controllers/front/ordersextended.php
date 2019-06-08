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

/**
 * <ModuleName> => itsttangointegracion
 * <FileName> => ordersextended.php
 * Format expected: <ModuleName><FileName>ModuleFrontController
 */

require_once dirname(__FILE__) . './../../includes/constants.php';
require_once dirname(__FILE__) . './../../classes/helpers.php';
require_once dirname(__FILE__) . './../../classes/orderExtended.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;

class ItsttangointegracionOrdersextendedModuleFrontController extends ModuleFrontController
{
    private $variables = [];

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $this->variables['nro_o_comp'] = \Tools::getValue('nro_o_comp', null);
        $this->variables['fecha_entr'] = \Tools::getValue('fecha_entr', null);
        $this->variables['id_cart'] = \Tools::getValue('id_cart', 0);
        $token = \Tools::getValue('token');
        $action = \Tools::getValue('action', '');
        $ajax = \Tools::getValue('ajax', false);
        $logger = Helpers\ItStLogger::instance();
        if ($action == 'update-extended') {
            if (\Tools::getToken(false) === $token) {
                $orderExtended = new ItSt\PrestaShop\Tango\OrdersExtended($this->variables['id_cart']);
                if (isset($this->variables['nro_o_comp'])) {
                    $orderExtended->NRO_O_COMP = $this->variables['nro_o_comp'];
                }
                if (isset($this->variables['fecha_entr'])) {
                    $orderExtended->FECHA_ENTR = $this->variables['fecha_entr'];
                }
                $orderExtended->save();
            } else {
                $logger->addLog(
                    'prostProcess;token and secureKey don\'t match',
                    Consts\SEVERITY_ERROR,
                    409,
                    'itsttangointegracion'
                );
            }
        }
        if (!$ajax) {
            \Tools::redirect('index.php?controller=order');
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
    }
}
