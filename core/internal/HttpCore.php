<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

use Centreon\Internal\Di;

/**
 * Description of HttpCore
 *
 * @author lionel
 */
class HttpCore
{
    /**
     *
     * @var type 
     */
    protected $db;
    
    /**
     *
     * @var type 
     */
    protected $request;
    
    /**
     *
     * @var \Centreon\Internal\Router 
     */
    protected $router;
    
    /**
     *
     * @var string 
     */
    public static $moduleName = 'Core';
    
    /**
     *
     * @var type 
     */
    protected static $httpCoreInstance;

    /**
     * 
     * @param type $request
     */
    protected function __construct($request)
    {
        $this->db = Di::getDefault()->get('db_centreon');
        $this->router = Di::getDefault()->get('router');
        $this->request = $request;
    }
    
    /**
     * 
     * @param type $request
     * @return type
     */
    final public static function getHttpCoreInstance($request)
    {
        if (is_null(self::$httpCoreInstance)) {
            $className = get_called_class();
            self::$httpCoreInstance = new $className($request);
        }
        
        return self::$httpCoreInstance;
    }
    
    /**
     * 
     * @return string
     */
    protected function getUri()
    {
        return $this->request->uri();
    }

    /**
     * Get params
     *
     * @param string $type
     * @return array
     */
    protected function getParams($type = "")
    {
        switch(strtolower($type)) {
            case 'get':
                $collection = $this->request->paramsGet();
                break;
            case 'post':
                $collection = $this->request->paramsPost();
                break;
            case 'named':
                $collection = $this->request->paramsNamed();
                break;
            default:
                $collection = $this->request->params();
                break;
        }
        return $collection;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public static function getRoutes()
    {
        $tempo = array();
        $className = get_called_class();
        $ref = new \ReflectionClass($className);
        foreach ($ref->getMethods() as $method) {
            $methodName = $method->getName();
            if (substr($methodName, -6) == 'Action') {
                foreach (explode("\n", $method->getDocComment()) as $line) {
                    $str = trim(str_replace("* ", '', $line));
                    if (substr($str, 0, 6) == '@route') {
                        $route = substr($str, 6);
                        if (isset($className::$objectName)) {
                            $route = str_replace("{object}", $className::$objectName, $route);
                        }
                        $tempo[$methodName]['route'] = trim($route);
                    } elseif (substr($str, 0, 7) == '@method') {
                        $method_type = strtoupper(substr($str, 7));
                        $tempo[$methodName]['method_type'] = trim($method_type);
                    } elseif (substr($str, 0, 4) == '@acl') {
                        $aclFlags = explode(",", trim(substr($str, 4)));
                        $tempo[$methodName]['acl'] = Acl::convertAclFlags($aclFlags);
                    }
                }
            }
        }
        return $tempo;
    }
    
    /**
     * 
     * @param string $route
     * @param integer $returnCode
     */
    protected function redirect($route, $returnCode = 200)
    {
        $redirectUrl = $this->router->getPathFor($route);
        $this->router->response()->redirect($redirectUrl, $returnCode);
    }
    
    /**
     * 
     * @param array $response
     */
    protected function sendResponse(array $response = array())
    {
        if (isset($response['json']) && is_array($response['json'])) {
            $this->router->response()->json($response['json']);
        }
    }
}
