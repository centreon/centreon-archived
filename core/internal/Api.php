<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

use Centreon\Internal\Router;
use Centreon\Internal\Exception\HttpException;
use Centreon\Internal\Exception\Http\BadRequestException;
use Centreon\Internal\Exception\Http\UnauthorizedException;
use CentreonConfiguration\Repository\UserRepository;

/**
 * Description of Api
 *
 * @author lionel
 */
class Api extends HttpCore
{
    /**
     * If a api route need the auth token
     * @var array
     */
    protected static $routeAuth = array();
    
    /**
     * 
     * @param type $request
     */
    protected function __construct($request)
    {
        parent::__construct($request);
    }
    
    /**
     * 
     * @param type $object
     * @param type $objectData
     * @param type $links
     */
    protected function sendJsonApiResponse($object, $objectData, $links = array())
    {
        $finalResponse = array(strtolower($object) => $objectData);
        
        if (count($finalResponse) > 0) {
            $finalResponse['links'] = $links;
        }
        
        $this->router->response()->header('Content-Type', 'application/json');
        $this->router->response()->json($finalResponse);
    }
    
    /**
     * Get routes
     *
     * @return array
     */
    public static function getRoutes()
    {
        $tempo = array();
        $obj = get_called_class();
        $ref = new \ReflectionClass(get_called_class());
        foreach ($ref->getMethods() as $method) {
            $methodName = $method->getName();
            if (substr($methodName, -6) == 'Action') {
                foreach (explode("\n", $method->getDocComment()) as $line) {
                    $str = trim(str_replace("* ", '', $line));
                    if (substr($str, 0, 6) == '@route') {
                        $route = substr($str, 6);
                        $objExp = explode('\\', $obj);
                        $nbOcc = count($objExp) -1;
                        $finalName = substr($objExp[$nbOcc], 0, strlen($objExp[$nbOcc])-3);
                        $route = str_replace('{object}', strtolower($finalName), $route);
                        
                        $tempo[$methodName]['route'] = trim($route);
                    } elseif (substr($str, 0, 7) == '@method') {
                        $method_type = strtoupper(substr($str, 7));
                        $tempo[$methodName]['method_type'] = trim($method_type);
                    } elseif (substr($str, 0, 4) == '@acl') {
                        $aclFlags = explode(",", trim(substr($str, 4)));
                        $tempo[$methodName]['acl'] = Acl::convertAclFlags($aclFlags);
                    } elseif (substr($str, 0, 5) == '@auth') {
                        $tempo[$methodName]['auth'] = true;
                    } elseif (substr($str, 0, 4) == '@api') {
                        $route = substr($str, 4);
                        
                        /* @todo better */
                        $objExp = explode('\\', $obj);
                        $nbOcc = count($objExp) -1;
                        $finalName = substr($objExp[$nbOcc], 0, strlen($objExp[$nbOcc])-3);
                        $route = str_replace('{object}', strtolower($finalName), $route);
                        
                        $tempo[$methodName]['api_route'] = trim($route);
                    } elseif (substr($str, 0, 6) == '@since') {
                        $version = substr($str, 6);
                        $tempo[$methodName]['api_version'] = trim($version);
                    }
                }
                if (isset($tempo[$methodName]['auth']) && $tempo[$methodName]['auth']) {
                    if (isset($tempo[$methodName]['route'])) {
                        static::$routeAuth[] = '\\' . $obj . '::' . $methodName;
                    }
                }
            }
        }
        return $tempo;
    }
    
    /**
     * 
     * @param type $requestMethod
     * @param type $requestVersion
     */
    public function executeRoute($requestMethod, $requestVersion = null)
    {
        try {
            $routeVersion = Router::getApiVersion($requestMethod);
            if (in_array($requestMethod, static::$routeAuth)) {
                $headers = $this->request->headers();
                if (!isset($headers['centreon-x-token'])) {
                    throw new BadRequestException('Missing Token', 'The Token for the request is not present');
                }
                
                $token = $headers['centreon-x-token'];
                if (!\CentreonConfiguration\Repository\UserRepository::checkApiToken($token)) { /* method auth */
                    throw new UnauthorizedException('Invalid Token', 'The Token is not valid');
                }
            }

            $methodName = null;
            $currentVersion = null;

            if (isset($routeVersion[$requestVersion])) {
                $methodName = $routeVersion[$requestVersion];
            } elseif (isset($routeVersion)) {
                foreach ($routeVersion as $version => $method) {
                    if (is_null($requestVersion)) {
                        if (is_null($currentVersion)) {
                            $currentVersion = $version;
                            $methodName = $method;
                        } else {
                            if (version_compare($currentVersion, $version, '>')) {
                                $currentVersion = $version;
                                $methodName = $method;
                            }
                        }
                    } else {
                        if (version_compare($version, $requestVersion, '<')) {
                            if (is_null($currentVersion)) {
                                $currentVersion = $version;
                                $methodName = $method;
                            } else {
                                if (version_compare($currentVersion, $version, '>')) {
                                    $currentVersion = $version;
                                    $methodName = $method;
                                }
                            }
                        }
                    }
                }
            }
            
            if (is_null($methodName)) {
                throw new Exception\Http\NotFoundException('Action does not exist', 'The requested action does not exist');
            }
            
            // Exexcute Api Method
            $calledMethod = function($className, $methodName, $request) {
                $classToCall = $className::getHttpCoreInstance($request);
                $classToCall->$methodName();
            };
            $className = get_called_class();
            $calledMethod($className, $methodName, $this->request);
            
        } catch (HttpException $ex) {
            $errorObject = array(
                'id' => '',
                'href' => '',
                'status' => $ex->getCode(),
                'code' => $ex->getInternalCode(),
                'title' => $ex->getTitle(),
                'detail' => $ex->getMessage(),
                'links' => '',
                'path' => ''
            );
            $this->router->response()->code($ex->getCode())->json($errorObject);
        } catch (Exception $ex) {
            $this->router->response()->code(500);
        }
    }
    
    /**
     * 
     * @param type $code
     * @param type $exceptionParams
     */
    public static function raiseHttpException($code, $exceptionParams)
    {
        
    }
}
