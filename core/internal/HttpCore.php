<?php

/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
