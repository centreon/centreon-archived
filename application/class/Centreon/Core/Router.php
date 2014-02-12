<?php

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
    protected $routes = array();

    /**
     * Parse routes
     *
     * @param string $pref | class prefix
     * @param string $dir | directory to parse
     */
    public function parseRoutes($pref, $dir)
    {
        if ($handle = opendir($dir)) {
            $baseUrl = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/');
            while (false !== ($dirname = readdir($handle))) {
                if ($dirname != "." && $dirname != "..") {
                    if (preg_match('/(.+)Controller\.php$/', $dirname, $matches)) {
                        $controllerName = $pref.'\\'.$matches[1].'Controller';
                        $routesData = $controllerName::getRoutes();
                        foreach ($routesData as $action => $data) {
                            $this->routes[] = $data;
                            $acl = Di::getDefault()->getDefault()->get('acl');
                            if ($acl->routeAllowed($data['route'], $data['acl']) {
                                $this->respond(
                                    $data['method_type'],
                                    $baseUrl.$data['route'],
                                    function ($request, $response) use ($controllerName, $action) {
                                        $obj = new $controllerName($request);
                                        $obj->$action();
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
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
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
