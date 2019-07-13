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


require_once dirname(__FILE__) . './../../includes/constants.php';
require_once dirname(__FILE__) . './../../classes/helpers.php';
require_once dirname(__FILE__) . './../../classes/customerExtended.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use Context;

class AdminCustomerExtendedController extends ModuleAdminController
{
    private $variables = [];

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->variables['id_customer'] = \Tools::getValue('id_customer', 0);
        $action = \Tools::getValue('action', '');
        $ajax = \Tools::getValue('ajax', false);
        $logger = Helpers\ItStLogger::instance();
        if ($action == 'sync-customer') {

            $customerExtended = new ItSt\PrestaShop\Tango\CustomerExtended($this->variables['id_customer']);

            $customerExtended->syncTangoByContact();
            $customerExtended->syncTangAddresses();

            $result = $customerExtended->save();

            if (!$result) {
                $this->errors[] = $this->trans(
                    'Ocurrió un error actualizando los datos.',
                    array(),
                    'itsttangointegracion'
                );
                $logger->addLog(
                    sprintf(
                        $this->l(
                            'An error ocurred trying to manually synchronize customer %d in Tango'
                        ),
                        $this->variables['id_customer']
                    ),
                    Consts\SEVERITY_ERROR,
                    null,
                    'itsttangointegracion'
                );
            } else {
                $this->success[] = $this->trans(
                    'El cliente fue actualizado.',
                    array(),
                    'itsttangointegracion'
                );
                $logger->addLog(
                    sprintf(
                        $this->l(
                            'Customer %d was synchronize with user %s in Tango'
                        ),
                        $this->variables['id_customer'],
                        $customerExtended->COD_CLIENT
                    ),
                    Consts\SEVERITY_INFO,
                    null,
                    'itsttangointegracion'
                );
            }
        } else {
            $this->errors[] = $this->trans(
                'The action is not supported.',
                array(),
                'itsttangointegracion'
            );
        }

        if ($ajax && !$this->errors) {
            $this->ajaxRender(Tools::jsonEncode([
                'success' => $this->success
            ]));
            return;
        }

        if ($ajax && $this->errors) {
            $this->ajaxRender(Tools::jsonEncode([
                'hasError' => true,
                'errors' => $this->errors,
                'success' => $this->success
            ]));
            return;
        }

        // Not ajax
        if (!count($this->errors)) {
            return Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers') . '&viewcustomer&id_customer=' . $this->variables['id_customer'] . '&conf=4');
        } else {
            $this->context->controller->errors = array_merge($this->context->controller->errors, $this->errors);
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
