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
 * Se llama "Orders" en el contexto de Orders de tango, no de Prestashop.
 * En realidad tiene datos del cart
 */

namespace ItSt\PrestaShop\Tango;

require_once dirname(__FILE__) . '/../includes/constants.php';
require_once dirname(__FILE__) . '/helpers.php';
use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Constantes as Consts;
use DB;
use ObjectModel;

class OrdersExtended extends ObjectModel
{
    /** @var int $id_cart - the ID of the order */
    public $id_cart;

    /** @var String $NRO_O_COMP - Numero de OC */
    public $NRO_O_COMP;

    /** @var String $NRO_OC_COMP - Numero de OC */
    public $NRO_OC_COMP;

    /** @var Date $FECHA_ENTR - Fecha de Entrega */
    public $FECHA_ENTR;

    public static $definition = array(
        'table' => 'itst_orders_extended',
        'primary' => 'id_cart',
        'multishop' => true,
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'NRO_O_COMP' => array('type' => self::TYPE_STRING),
            'NRO_OC_COMP' => array('type' => self::TYPE_STRING),
            'FECHA_ENTR' => array('type' => self::TYPE_DATE),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }
}
