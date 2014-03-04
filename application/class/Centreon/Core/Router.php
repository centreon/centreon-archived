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
 *
 */


namespace Centreon\Core;

class Router extends \Klein\Klein
{
    /**
     * The regular expression used to compile and match URL's
     *
     * @const string
     */
    const ROUTE_COMPILE_REGEX = '`(\\\?(?:/|\.|))(\[([^:\]]*+)(?::([^:\]]*+))?\])(\?|)`';

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
     * Parse routes
     *
     * @param string $pref | class prefix
     * @param string $dir | directory to parse
     */
    public function parseRoutes($pref, $dir)
    {
        $this->countDeep++;
        if ($handle = opendir($dir)) {
            $baseUrl = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/');
            while (false !== ($dirname = readdir($handle))) {
                if ($dirname != "." && $dirname != "..") {
                    if (preg_match('/(.+)Controller\.php$/', $dirname, $matches)) {
                        $controllerName = $pref.'\\'.$matches[1].'Controller';
                        $routesData = $controllerName::getRoutes();
                        foreach ($routesData as $action => $data) {
                            if (!isset($data['acl'])) {
                                $data['acl'] = "";
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
                                $routeName = $baseUrl.$data['route'];
                            }
                            if (isset($_SESSION['acl']) && 
                                false === $_SESSION['acl']->routeAllowed($data['route'])) {
                                $this->respond(
                                    $routeName, 
                                    function($request, $response) {
                                        $response->code(403);
                                    }
                                );
                            } else {
                                $this->respond(
                                    $data['method_type'],
                                    $routeName,
                                    function ($request, $response) use ($controllerName, $action, $routeName) {
                                        if (!isset($_SESSION['user']) && !strstr($routeName, ".css") && 
                                            !strstr($controllerName, "LoginController")) {
                                            $obj = new \Controllers\LoginController($request);
                                            $obj->loginAction();
                                        } else {
                                            $obj = new $controllerName($request);
                                            $obj->$action();
                                        }
                                    }
                                );
                            }
                        }
                    } elseif (is_dir($dir . '/' . $dirname)) {
                        $this->parseRoutes($pref . '\\' . ucfirst($dirname), $dir . '/' . $dirname);
                    }
                }
            }
            closedir($handle);
        }
        $this->countDeep--;
        if (0 === $this->countDeep) {
            $controllerName = $this->notFoundRoute['controllerName'];
            $action = $this->notFoundRoute['action'];
            $this->respond(
                $this->notFoundRoute['method'],
                '404',
                function ($request, $response) use ($controllerName, $action) {
                    $obj = new $controllerName($request);
                    $obj->$action();
                }
            );
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
        $validPath = false;
        foreach ($this->routes as $routeArr) {
            foreach ($routeArr as $v) {
                if (is_string($v) && $v == $path) {
                    $validPath = true;
                    break;
                }
            }
        }
        if (false === $validPath) {
            throw new Exception('No such route with name: '. $path);
        }
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
}
