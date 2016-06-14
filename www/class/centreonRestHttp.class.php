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
     * Constructor
     *
     * @param string $contentType The content type
     */
    public function __contruct($contentType = 'application/json')
    {
        $this->contentType = $contentType;
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
                break;
            case 405:
                $exceptionClass = 'RestMethodNotAllowedException';
                break;
            case 409:
                $exceptionClass = 'RestConflictException';
                break;
            case 500:
                $exceptionClass = 'RestInternalServerErrorException';
                break;
        }
        if (!is_null($exceptionClass)) {
            if (isset($decodedContent['message'])) {
                throw new $exceptionClass($decodedContent['message']);
            }
            throw new $exceptionClass();
        }
        if ($headers['code'] != 200 && $headers['code'] != 201) {
            throw new RestInternalServerErrorException('Unknown HTTP error');
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
