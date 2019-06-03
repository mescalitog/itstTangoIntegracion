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

/**
 * Logger
 */
class ItStLogger
{
    private static $instance = null;
    private $logger = null;
    // Singleton
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ItStLogger();
        }
        return self::$instance;
    }

    public function __construct($severity = 0)
    {
        $cacheDir = dirname(__FILE__) . '/../../logs/';
        $file = $cacheDir . ('_PS_MODE_DEV_' ? 'dev' : 'prod') . '_' . @date('Ymd') . '_tango.log';
        $this->logger  = new \FileLogger($severity);  //0 == nivel de debug. Sin esto logDebug() no funciona.
        $this->logger->setFilename($file);
        $logToFile = Configuration::get(Consts\ITST_TANGO_LOG_TOFILE);
        if ($logToFile == 1) {
            $this->logger->logInfo('logger started;errorCode;objectType;objectId;message');
        }
    }

    /**
     * Loguea en el sistema de archivos si esta habilitado el logueo en la configuracion.
     * @param string message mensaje a loguear
     * @param string severity
     * @param string errorCode
     * @param string objectType
     * @param number objectId
     */
    public function addLog(
        $message,
        $severity = Consts\SEVERITY_INFO,
        $errorCode = null,
        $objectType = null,
        $objectId = null,
        $logToPrestashop = 1
    ) {
        $logToFile = Configuration::get(Consts\ITST_TANGO_LOG_TOFILE);
        if ($logToFile == 1) {
            switch ($severity) {
                case Consts\SEVERITY_INFO:
                    $this->logger->logInfo($errorCode . ';' . $objectType . ';' . $objectId . ';' . $message);
                    break;
                case Consts\SEVERITY_WARNING:
                    $this->logger->logWarning($errorCode . ';' . $objectType . ';' . $objectId . ';' . $message);
                    break;
                case Consts\SEVERITY_ERROR:
                case Consts\SEVERITY_FATAL_ERROR:
                    $this->logger->logError($errorCode . ';' . $objectType . ';' . $objectId . ';' . $message);
                    break;
                default:
                    $this->logger->logDebug($errorCode . ';' . $objectType . ';' . $objectId . ';' . $message);
            }
        }
        if ($logToPrestashop) {
            self::addPrestaShopLog($message, $severity, $errorCode, $objectType, $objectId);
        }
    }

    public function addPrestaShopLog(
        $message,
        $severity = Consts\SEVERITY_INFO,
        $errorCode = null,
        $objectType = null,
        $objectId = null
    ) {
        // public static function addLog(
        // $message,
        // $severity = 1,
        // $errorCode = null,
        // $objectType = null,
        // $objectId = null,
        // $allowDuplicate = false,
        // $idEmployee = null)
        $severityToLog = Configuration::get(Consts\ITST_TANGO_LOG_SEVERITY, Consts\SEVERITY_FATAL_ERROR);
        if ($severity >= $severityToLog) {
            PrestaShopLogger::addLog($message, $severity, $errorCode, $objectType, $objectId);
        }
    }
    public function __destruct()
    {
        $logToFile = Configuration::get(Consts\ITST_TANGO_LOG_TOFILE);
        if (($logToFile == 1) && isset($this->logger)) {
            $this->logger->logInfo('logger stop;ITST_TANGO_LOG_SEVERITY=' . $logToFile);
        }
    }
}
