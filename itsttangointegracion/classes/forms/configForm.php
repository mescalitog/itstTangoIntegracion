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

require_once dirname(__FILE__) . '/../../includes/constants.php';

use ItSt\PrestaShop\Transporte\Constantes as Consts;
use Configuration;
use HelperForm;
use Tools;
use AdminController;
use Context;

abstract class ItstConfigForms
{
    private static $module = null;
    public static $context = false;
    public static $selectedTab = null;
    public static function init($module, $context = null)
    {
        self::$context = $context ? $context : Context::getContext();

        if (self::$module == null) {
            self::$module = $module;
        }
        return self::$module;
    }

    /**
     * Renders a form
     */
    public static function renderForm($form, $form_values, $action, $cancel = false, $back_url = false)
    {
        $context = Context::getContext();
        // Get default language
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = self::$module;
        $helper->name_controller = self::$module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        // $helper->currentIndex = AdminController::$currentIndex . '&configure=' . self::$module->name;
        $helper->submit_action = $action;
        $helper->identifier = self::$module->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = self::$module->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.

        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => self::$module->l('Save'),
                'href' => AdminController::$currentIndex . '&configure='
                    . self::$module->name . '&save'
                    . self::$module->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => self::$module->l('Back to list')
            )
        );

        // $helper->table = self::$module->table;

        $helper->currentIndex = $context->link->getAdminLink('AdminModules', false)
            . '&configure=' . self::$module->name
            . '&tab_module=' . self::$selectedTab
            . '&module_name=' . self::$module->name;

 
        $helper->tpl_vars = array(
            'fields_value' => $form_values,
            'languages' => $context->controller->getLanguages(),
            'id_language' => $context->language->id,
            'back_url' => $back_url,
            'show_cancel_button' => $cancel,
        );

        return $helper->generateForm($form);
    }

    public static function setSelectedTab($selectedTab)
    {
        self::$selectedTab = $selectedTab;
    }
}
