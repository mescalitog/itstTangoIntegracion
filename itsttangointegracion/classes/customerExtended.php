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

namespace ItSt\PrestaShop\Tango;

require_once dirname(__FILE__) . '/../includes/constants.php';
require_once dirname(__FILE__) . '/helpers.php';
require_once dirname(__FILE__) . '/ventas/clientes.php';

use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Ventas as Ventas;
use ItSt\PrestaShop\Tango\Constantes as Consts;
use DB;
use ObjectModel;
use Customer;
use Address;

class CustomerExtended extends ObjectModel
{
    /** @var int $id_customer - the ID of the customer */
    public $id_customer;

    /** @var String $COD_CLIENT - Codigo de cliente en tango */
    public $COD_CLIENT;

    /** @var String $COD_VENDED - Codigo de vendedor en tango */
    public $COD_VENDED;

    /** @var int $COND_VTA - Condicion de venta */
    public $COND_VTA;

    /** @var String $CUIT - Cuit del Cliente */
    public $CUIT;

    /** @var float $CUPO_CREDI - Cupo de Crédito */
    public $CUPO_CREDI;

    /** @var String $NOM_COM - Nombre Comercial */
    public $NOM_COM;

    /** @var String $RAZON_SOCI - Nombre Comercial */
    public $RAZON_SOCI;

    /** @var int $NRO_LISTA - Numero de Lista */
    public $NRO_LISTA;


    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;


    public static $definition = array(
        'table' => 'itst_customer_extended',
        'primary' => 'id_customer',
        'multishop' => true,
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'COD_CLIENT' => array('type' => self::TYPE_STRING),
            'COD_VENDED' => array('type' => self::TYPE_STRING),
            'COND_VTA' => array('type' => self::TYPE_INT),

            'CUIT' => array('type' => self::TYPE_STRING),
            'CUPO_CREDI' => array('type' => self::TYPE_FLOAT),
            'NOM_COM' => array('type' => self::TYPE_STRING),
            'RAZON_SOCI' => array('type' => self::TYPE_STRING),
            'NRO_LISTA' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        $this->id_customer = $id;
        parent::__construct($id, $id_lang);
    }

    public function syncTangoByContact()
    {
        $customer = new Customer($this->id_customer);

        if (isset($customer)) {
            $contactos = Ventas\Clientes::getContactos(array('email' => $customer->email));
            $contacto = (empty($contactos['rows'])) ? null : $contactos['rows'][0];
            if (isset($contacto)) {
                $cliente = Ventas\Clientes::getCliente($contacto['COD_CLIENT']);
                if (isset($cliente)) {
                    $this->COD_CLIENT = (isset($cliente) && isset($cliente['COD_CLIENT'])
                        ? $cliente['COD_CLIENT']
                        : Consts\ITST_TANGO_ORDERS_CLIENTE_OCACIONAL);
                    $this->COND_VTA = isset($cliente['COND_VTA']) ? $cliente['COND_VTA'] : 1;
                    $this->COD_VENDED = isset($cliente['COD_VENDED']) ? $cliente['COD_VENDED'] : null;
                    $this->NRO_LISTA = isset($cliente['NRO_LISTA']) ? $cliente['NRO_LISTA'] : null;
                    $this->CUIT = isset($cliente['CUIT']) ? $cliente['CUIT'] : null;
                    $this->RAZON_SOCI = isset($cliente['RAZON_SOCI']) ? $cliente['RAZON_SOCI'] : null;
                    $customer->siret = isset($cliente['CUIT']) ? $cliente['CUIT'] : $customer->siret;
                    $customer->ape = $this->COD_CLIENT;
                    $customer->company = isset($cliente['RAZON_SOCI']) ? $cliente['RAZON_SOCI'] : $customer->company;
                    return $customer->save();
                }
            }
        };
        return true;
    }

    public function syncTangAddresses()
    {

        $COD_CLIENT = $this->COD_CLIENT;

        if (isset($COD_CLIENT) && $COD_CLIENT != Consts\ITST_TANGO_ORDERS_CLIENTE_OCACIONAL && $COD_CLIENT != '') {
            $direcciones = Ventas\Clientes::getDireccionesEntrega($COD_CLIENT);
            $customer = new Customer($this->id_customer);
            foreach ($direcciones as $direccion) {

                // Busco la direccion por alias
                if (!Address::aliasExist($direccion['COD_DIRECCION_ENTREGA'], false, (int) $customer->id )) {
                    $address = new Address(
                        null
                    );

                    $address->address1 = $direccion['DIRECCION'];
                    $address->postcode = $direccion['CODIGO_POSTAL'];
                    $address->city = $direccion['LOCALIDAD'];
                    $address->other = $direccion['ID_DIRECCION_ENTREGA'];
                    $address->alias = $direccion['COD_DIRECCION_ENTREGA'];
                    $address->firstname = $customer->firstname;
                    $address->lastname = $customer->lastname;
                    $address->id_customer = (int) $customer->id;
                    $address->id_country = 44; //Argentina
                    $address->id_state = 0;
                    $address->save();
                }
            }
        };
        return true;
    }
}
