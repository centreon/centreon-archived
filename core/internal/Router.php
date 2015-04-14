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
 *
 */

namespace Centreon\Internal;

use Centreon\Internal\Module\Informations;
use CentreonSecurity\Controllers\LoginController;
use Klein\Klein;

class Router extends Klein
{
    /**
     * The regular expression used to compile and match URL's
     *
     * @const string
     */
    const ROUTE_COMPILE_REGEX = '`(\\\?(?:/|\.|))(\[([^:\]]*+)(?::([^:\]]*+))?\])(\?|)`';
    
    /**
     * Temporary list of route with method for create API route version
     * @var array
     */
    protected $tmpRoute = array();

    /**
     * Route info
     * 
     * @var array
     */
    protected $routesData = array();

    /**
     * Temporary store the 404 route
     *
     * @var array
     */
    protected $notFoundRoute = array();

    /**
     * Count the deep of scan for loadin route
     *
     * @var int
     */
    protected $countDeep = 0;
    
    /**
     *
     * @var type 
     */
    protected $mainRouteModules = array();
    
    /**
     * The list of version for api route
     * @var array
     */
    protected static $routeApiVersion = array();

    /**
     * 
     */
    public function parseRoutes()
    {
        $this->mainRouteModules = array(
            'centreon-main',
            'centreon-security'
        );
        $cacheHandler = Di::getDefault()->get('cache');
        $routesFullList = $cacheHandler->get('routes');
        
        if (is_null($routesFullList) || !$routesFullList) {
            $routesFullList = $this->getRoutesList();
            $cacheHandler->set('routes', $routesFullList);
        }
        
        foreach ($routesFullList as $routeType => $routesList) {
            switch ($routeType) {
                default:
                case 'front':
                    $prefix = '';
                    break;
                case 'api':
                    $prefix = 'api';
                    break;
            }
            foreach ($routesList as $moduleName => $routesAndControllers) {
                foreach ($routesAndControllers as $controllerName => $routes) {
                    $this->parseRouteData($moduleName, $controllerName, $routes, $prefix);
                }
            }
            if ($routeType == 'api') {
                $this->computeApiVersion($routesList);
            }
        }
        unset($this->tmpRoute);
        
        $this->respond(
            '404',
            function ($request, $response) {
                $tmpl = Di::getDefault()->get('template');
                $response->code(404);
                $response->body($tmpl->fetch('404.tpl'));
            }
        );

        // FIXME Deadcode, should be removed or we should have a ticket to do something about it
        /*$this->onHttpError(function ($code, $router, $matched, $methods_matched, $http_exception) {
            switch ($code) {
                case 404:
                    $tmpl = Di::getDefault()->get('template');
                    $router->response()->body($tmpl->fetch('404.tpl'));
                    break;
                case 405:
                    $router->response()->body(
                        'You can\'t do that!'
                    );
                    break;
                default:
                    $router->response()->body(
                        'Oh no, a bad error happened that caused a '. $code
                    );
            }
        });*/
    }
    
    /**
     * 
     * @return array
     */
    private function getRoutesList()
    {
        // getting controllers list using current activate module list
        $modulesList = Informations::getModuleList(true);
        $modules = array();
        foreach ($modulesList as $currentModule) {
            $modules[$currentModule]['path'] = Informations::getModulePath($currentModule);
            $modules[$currentModule]['commonName'] = Informations::getModuleCommonName($currentModule);
        }
        $controllersFullList = $this->getControllersList($modules);
        
        // getting front route
        $routesFullList = array('front' => array(), 'api' => array());
        foreach ($controllersFullList as $type => $controllersList) {
            foreach($controllersList as $moduleName => $controllers) {
                $this->getRoutesFromController($moduleName, $controllers, $routesFullList[$type]);
            }
        }
        
        return $routesFullList;
    }
    
    /**
     * 
     * @param type $moduleName
     * @param type $controllers
     * @param type $routesFullList
     */
    private function getRoutesFromController($moduleName, $controllers, &$routesFullList)
    {
        foreach($controllers as $controllerName) {
            $routes = $controllerName::getRoutes();
            if (count($routes) > 0) {
                $routesFullList[$moduleName][$controllerName] = $routes;
            }
        }
    }
    
    /**
     * 
     * @param type $modules
     * @return string
     */
    private function getControllersList($modules)
    {
        $controllersList = array();
        
        // Now lets see the modules
        foreach ($modules as $moduleName => $module) {
            $myModuleControllersFiles = glob("$module[path]/controllers/*Controller.php");
            $myModuleApiFiles = glob("$module[path]/api/rest/*Api.php");
            foreach ($myModuleControllersFiles as $moduleController) {
                $controllersList['front'][$moduleName][] = '\\'.$module['commonName'].'\\Controllers\\'.basename($moduleController, '.php');
            }
            foreach ($myModuleApiFiles as $moduleApi) {
                $controllersList['api'][$moduleName][] = '\\'.$module['commonName'].'\\Api\\Rest\\'.basename($moduleApi, '.php');
            }
        }
        
        return $controllersList;
    }
    
    /**
     * 
     * @param type $moduleName
     * @param type $controllerName
     * @param type $routesData
     * @param type $routePrefix
     */
    private function parseRouteData($moduleName, $controllerName, $routesData, $routePrefix = '')
    {
        $baseUrl = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/');
        if (!empty($routePrefix)) {
            $baseUrl .= '/' . $routePrefix;
        }
        foreach ($routesData as $action => $data) {
            if (!isset($data['acl'])) {
                $data['acl'] = "";
            }
            
            if (isset($data['route'])) {
            
                if (!in_array($moduleName, $this->mainRouteModules)) {
                    $data['route'] = '/' . $moduleName . $data['route'];
                }

                $this->routesData[] = $data;
                if (substr($data['route'], 0, 1) === '@' || $data['route'] === '405') {
                    $routeName = $data['route'];
                } elseif ($data['route'] === '404') {
                    $this->notFoundRoute = array(
                        'controllerName' => $controllerName,
                        'action' => $action,
                        'method' => $data['method_type']
                    );
                } else {
                    $routeName = $baseUrl . $data['route'];
                }
                if (isset($_SESSION['acl']) &&
                    false === $_SESSION['acl']->routeAllowed($data['route'])) {
                    $this->respond(
                        $routeName,
                        function ($request, $response) {
                            $response->code(403);
                        }
                    );
                } else {
                    $this->tmpRoute[$routeName][$data['method_type']] = $controllerName . '::' . $action;
                    $this->respond(
                        $data['method_type'],
                        $routeName,
                        function ($request, $response) use ($controllerName, $action, $routeName) {
                            if (!isset($_SESSION['user']) && !strstr($routeName, ".css") &&
                                !strstr($controllerName, "LoginController") && !strstr($routeName, "/api/")) {
                                $obj = LoginController::getHttpCoreInstance($request);
                                $obj->loginAction();
                            } else {
                                $obj = $controllerName::getHttpCoreInstance($request);
                                $obj->$action();
                            }
                        }
                    );
                }
            }
        }
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routesData;
    }

    /**
     * Get the path for a given route
     *
     * This looks up the route by its passed name and returns
     * the path/url for that route, with its URL params as
     * placeholders unless you pass a valid key-value pair array
     * of the placeholder params and their values
     *
     * If a pathname is a complex/custom regular expression, this
     * method will simply return the regular expression used to
     * match the request pathname, unless an optional boolean is
     * passed "flatten_regex" which will flatten the regular
     * expression into a simple path string
     *
     * This method, and its style of reverse-compilation, was originally
     * inspired by a similar effort by Gilles Bouthenot (@gbouthenot)
     *
     * @link https://github.com/gbouthenot
     * @param string $route_name        The name of the route
     * @param array $params             The array of placeholder fillers
     * @param boolean $flatten_regex    Optionally flatten custom regular expressions to "/"
     * @throws OutOfBoundsException     If the route requested doesn't exist
     * @access public
     * @return string
     */
    public function getPathFor($route_name, array $params = null, $flatten_regex = true)
    {
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/').$route_name;
        if (preg_match_all(static::ROUTE_COMPILE_REGEX, $path, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                list($block, $pre, $inner_block, $type, $param, $optional) = $match;
                if (isset($params[$param])) {
                    $path = str_replace($block, $pre. $params[$param], $path);
                } elseif ($optional) {
                    $path = str_replace($block, '', $path);
                }
            }

        } elseif ($flatten_regex && strpos($path, '@') === 0) {
            $path = '/';
        }

        return $path;
    }

    /**
     * Return current URI without the base URL
     *
     * @return string
     */
    public function getCurrentUri()
    {
        $baseUrl = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/');
        return preg_replace('/^'.preg_quote($baseUrl, '/').'/', '', $this->request()->uri());
    }
    
    /**
     * 
     * @param type $methodName
     * @return type
     */
    public static function getApiVersion($methodName)
    {
        if (isset(static::$routeApiVersion[$methodName])) {
            return static::$routeApiVersion[$methodName];
        }
        return array();
    }
    
    /**
     * 
     * @param type $routesList
     */
    protected function computeApiVersion($routesList)
    {
        foreach ($routesList as $moduleName => $routesAndControllers) {
            foreach ($routesAndControllers as $controllerName => $routes) {
                foreach ($routes as $action => $info) {
                    if (isset($info['api_route']) && isset($info['api_version']) && isset($info['method_type'])) {
                        $route = $this->fullPath($moduleName, $info['api_route'], 'api');
                        if (isset($this->tmpRoute[$route][$info['method_type']])) {
                            static::$routeApiVersion[$this->tmpRoute[$route][$info['method_type']]][$info['api_version']] = $action;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 
     * @param string $moduleName
     * @param string $route
     * @param string $routePrefix
     * @return string
     */
    protected function fullPath($moduleName, $route, $routePrefix='')
    {
        $baseUrl = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/');
        if (!empty($routePrefix)) {
            $baseUrl .= '/' . $routePrefix;
        }
        
        if (!in_array($moduleName, $this->mainRouteModules)) {
            return $baseUrl . '/' . $moduleName . $route;
        }
        return $baseUrl . '/' . $route;
    }
}
