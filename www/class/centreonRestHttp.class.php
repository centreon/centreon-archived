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

require_once _CENTREON_PATH_ . '/www/api/exceptions.php';
require_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";

/**
 * Utils class for call HTTP JSON REST
 *
 * @author Centreon
 * @version 1.0.0
 * @package centreon-license-manager
 */
class CentreonRestHttp
{
    /**
     * @var The content type : default application/json
     */
    private $contentType = 'application/json';

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
        /* Create stream context */
        $httpOpts = array(
            'http' => array(
                'ignore_errors' => true,
                'protocol_version' => '1.1',
                'method' => $method,
                'header' => join("\r\n", $headers)
            )
        );
        /* Add body json data */
        if (false === is_null($data)) {
            $httpOpts['http']['content'] = json_encode($data);
        }
        /* Create context */
        $httpContext = stream_context_create($httpOpts);

        /* Get contents */
        $content = @file_get_contents($url, false, $httpContext);

        if (!$content) {
            $headers = array(
                'code' => 404
            );
        } else {
            $decodedContent = json_decode($content, true);
            /* Get headers */
            $headers = $this->parseHttpMeta($http_response_header);
        }
        
        /* Manage HTTP status code */
        $exceptionClass = null;
        $logMessage = 'Unknown HTTP error';
        switch ($headers['code']) {
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
            case 200:
            case 201:
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
     * Parse stream meta to convert to http headers
     *
     * @param array $metas The stream metas
     * @return array The http headers
     */
    private function parseHttpMeta($metas)
    {
        $headers = array(
            'code' => 404
        );

        foreach ($metas as $meta) {
            /* Parse HTTP Code */
            if (preg_match('!^HTTP/1.1 (\d+) (.+)!', $meta, $matches)) {
                $headers['code'] = $matches[1];
                $headers['status'] = $matches[2];
            /* Parse content type return */
            } elseif (preg_match('/Content-Type: (.*)/', $meta, $matches)) {
                $infos = explode(';', $matches[1]);
                $headers['content-type'] = $infos[0];
                /* Get extra information of content-type */
                if (count($infos) > 0) {
                    foreach ($infos as $info) {
                        $line = explode('=', trim($info));
                        if ($line[0] == 'charset') {
                            $headers['charset'] = $line[1];
                        }
                    }
                }
            }
        }

        return $headers;
    }
}
