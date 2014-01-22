<?php

namespace Centreon\Core;

class Router extends \Klein\Klein
{
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
                            $this->respond(
                                $data['method_type'],
                                $baseUrl.$data['route'],
                                function ($request, $response) use ($controllerName, $action) {
                                    $obj = new $controllerName($request);
                                    $obj->$action();
                                }
                            );
                        }
                    } elseif (is_dir($dir.$dirname)) {
                        $this->parseRoutes($pref.'\\'.ucfirst($dirname), $dir.$dirname);
                    }
                }
            }
        }
        closedir($handle);
    }
}
