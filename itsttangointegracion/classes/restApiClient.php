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

require_once dirname(__FILE__) . '/../includes/constants.php';
require_once dirname(__FILE__) . '/helpers.php';

use ItSt\PrestaShop\Tango\Helpers as Helpers;
use ItSt\PrestaShop\Tango\Constantes as Consts;

class RestApiClient
{
    private static function getConnect($uri, $method, $content_type, $uri_base)
    {
        $apiKey = Configuration::get(Consts\ITST_TANGO_API_KEY);
        $connect = curl_init($uri_base . $uri);
        curl_setopt($connect, CURLOPT_USERAGENT, 'ItSt Prestashop');
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt(
            $connect,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: application/json',
                'Content-Type: ' . $content_type,
                'authorization: Api-Key ' . $apiKey
            )
        );
        return $connect;
    }

    private static function setData($connect, $data, $content_type)
    {
        if ($content_type == 'application/json') {
            if (gettype($data) == 'string') {
                Tools::jsonDecode($data, true);
            } else {
                $data = Tools::jsonEncode($data);
            }

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception('JSON Error [{$json_error}] - Data: {$data}');
                }
            }
        }
        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    private static function callAPI($method, $uri, $data, $content_type, $uri_base)
    {
        Helpers\ItStLogger::instance()->addLog(
            'callAPI;'
                . ' method = ' . $method
                . ', uri=' . $uri
                . ', data=' . Tools::jsonEncode($data)
                . ', uri_base=' . $uri_base,
            Consts\SEVERITY_DEBUG,
            null,
            'restApiClient'
        );
        $connect = self::getConnect($uri, $method, $content_type, $uri_base);

        if ($data) {
            self::setData($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);
        $response = array(
            'status' => $api_http_code,
            'response' => Tools::jsonDecode($api_result, true),
        );

        Helpers\ItStLogger::instance()->addLog(
            'callAPI '
                . 'response = ' . $api_result,
            Consts\SEVERITY_DEBUG,
            null,
            'restApiClient'
        );

        if ($response['status'] == 0) {
            Helpers\ItStLogger::instance()->addLog(
                'callAPI = Can not call the API, status code 0.',
                Consts\SEVERITY_ERROR,
                0,
                'restApiClient'
            );
        } else {
            if ($response['status'] > 205) {
                Helpers\ItStLogger::instance()->addLog(
                    'callAPI response message= ' . $response['response']['message'],
                    Consts\SEVERITY_ERROR,
                    $response['status'],
                    'restApiClient',
                    null,
                    0
                );
            }
        }
        curl_close($connect);
        return $response['response'];
    }

    public static function post($uri, $data, $content_type = 'application/json')
    {
        $apiUrl = Configuration::get(Consts\ITST_TANGO_API_URL);
        return self::callAPI('POST', $uri, $data, $content_type, $apiUrl);
    }

    public static function get($uri, $content_type = 'application/json')
    {
        $apiUrl = Configuration::get(Consts\ITST_TANGO_API_URL);
        Helpers\ItStLogger::instance()->addLog(
            'get uri=' . $uri . ' apiUrl=' . $apiUrl,
            Consts\SEVERITY_DEBUG,
            0,
            'restApiClient'
        );
        return self::callAPI('GET', $uri, null, $content_type, $apiUrl);
    }
}
