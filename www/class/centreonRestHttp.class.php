<?php
/**
 * Copyright 2016 Centreon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . '/www/api/exceptions.php';
require_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";

/**
 * Utils class for call HTTP JSON REST
 *
 * @author Centreon
 * @version 1.0.0
 * @package centreon
 */
class CentreonRestHttp
{
    /**
     * @var The content type : default application/json
     */
    private $contentType = 'application/json';

    /**
     * @var using a proxy
     */
    private $proxy = null;

    /**
     * @var proxy authentication information
     */
    private $proxyAuthentication = null;

    /**
     * @var logFileThe The log file for call errors
     */
    private $logObj = null;

    /**
     * Constructor
     *
     * @param string $contentType The content type
     */
    public function __construct($contentType = 'application/json', $logFile = null)
    {
        $this->getProxy();
        $this->contentType = $contentType;
        if (!is_null($logFile)) {
            $this->logObj = new CentreonLog(array(4 => $logFile));
        }
    }

    private function insertLog($output, $url, $type = 'RestInternalServerErrorException')
    {
        if (is_null($this->logObj)) {
            return;
        }

        $logOutput = '[' . $type . '] ' . $url . ' : ' . $output;

        $this->logObj->insertLog(4, $logOutput);
    }

    /**
     * Call the http rest endpoint
     *
     * @param string $url The endpoint url
     * @param string $method The HTTP method
     * @param array|null $data The data to send on the request
     * @param array $headers The extra headers without Content-Type
     * @return array The result content
     */
    public function call($url, $method = 'GET', $data = null, $headers = array())
    {
        /* Add content type to headers */
        $headers[] = 'Content-type: ' . $this->contentType;
        $headers[] = 'Connection: close';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!is_null($this->proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            if (!is_null($this->proxyAuthentication)) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyAuthentication);
            }
        }

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!$http_code) {
            $http_code = 404;
        }

        curl_close($ch);

        $decodedContent = '';
        if ($result) {
            $decodedContent = json_decode($result, true);
        }

        /* Manage HTTP status code */
        $exceptionClass = null;
        $logMessage = 'Unknown HTTP error';
        switch ($http_code) {
            case 200:
            case 201:
                break;
            case 400:
                $exceptionClass = 'RestBadRequestException';
                break;
            case 401:
                $exceptionClass = 'RestUnauthorizedException';
                break;
            case 403:
                $exceptionClass = 'RestForbiddenException';
                break;
            case 404:
                $exceptionClass = 'RestNotFoundException';
                $logMessage = 'Page not found';
                break;
            case 405:
                $exceptionClass = 'RestMethodNotAllowedException';
                break;
            case 409:
                $exceptionClass = 'RestConflictException';
                break;
            case 500:
            default:
                $exceptionClass = 'RestInternalServerErrorException';
                break;
        }

        if (!is_null($exceptionClass)) {
            $message = isset($decodedContent['message']) ? $decodedContent['message'] : $logMessage;
            $this->insertLog($message, $url, $exceptionClass);
            throw new $exceptionClass($message);
        }

        /* Return the content */
        return $decodedContent;
    }

    /**
     * get proxy data
     *
     */
    private function getProxy()
    {
        $db = new CentreonDB();
        $query = 'SELECT `key`, `value` '
            . 'FROM `options` '
            . 'WHERE `key` IN ( '
            . '"proxy_url", "proxy_port", "proxy_user", "proxy_password" '
            . ') ';
        $res = $db->query($query);
        while ($row = $res->fetchRow()) {
            $dataProxy[$row['key']] = $row['value'];
        }

        if (isset($dataProxy['proxy_url']) && !empty($dataProxy['proxy_url'])) {
            $this->proxy = 'tcp://' . $dataProxy['proxy_url'];

            if ($dataProxy['proxy_port']) {
                $this->proxy .= ':' . $dataProxy['proxy_port'];
            }

            /* Proxy basic authentication */
            if (isset($dataProxy['proxy_user']) && !empty($dataProxy['proxy_user']) &&
                isset($dataProxy['proxy_password']) && !empty($dataProxy['proxy_password'])) {
                $this->proxyAuthentication = $dataProxy['proxy_user'] . ':' . $dataProxy['proxy_password'];
            }
        }
    }
}
