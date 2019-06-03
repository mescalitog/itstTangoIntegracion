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

namespace ItSt\PrestaShop\Tango\Helpers;

require_once dirname(__FILE__) . '/../../includes/constants.php';

use ItSt\PrestaShop\Tango\Constantes as Consts;
use Configuration;
use PrestaShopLogger;

class ItStValidaCuit
{
    //FUNCION VALIDA CUIT
    public static function validarCUIT($inputCuit)
    {
        $cuit = str_replace("-", "", $inputCuit);
        if (\Tools::strlen($cuit) != 11) {
            return false;
        }
        $cadena = str_split($cuit);

        $result = $cadena[0] * 5;
        $result += $cadena[1] * 4;
        $result += $cadena[2] * 3;
        $result += $cadena[3] * 2;
        $result += $cadena[4] * 7;
        $result += $cadena[5] * 6;
        $result += $cadena[6] * 5;
        $result += $cadena[7] * 4;
        $result += $cadena[8] * 3;
        $result += $cadena[9] * 2;

        $div = (int)($result / 11);
        $resto = $result - ($div * 11);

        if ($resto == 0) {
            if ($resto == $cadena[10]) {
                return true;
            } else {
                return false;
            }
        } elseif ($resto == 1) {
            if ($cadena[10] == 9 and $cadena[0] == 2 and $cadena[1] == 3) {
                return true;
            } elseif ($cadena[10] == 4 and $cadena[0] == 2 and $cadena[1] == 3) {
                return true;
            }
        } elseif ($cadena[10] == (11 - $resto)) {
            return true;
        } else {
            return false;
        }
    }
}
