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

use Evenement\EventEmitter;
use Centreon\Internal\Module\Informations as Module;

/**
 * Class Bootstrap
 * In charge of initializing the application context.
 * Loaded early during request processing by either front controller (index.php) or centreonConsole
 * Will init the service container (Di) thru Di->init() to init all services or Di->init(array of services to init) to only
 * init a subset of services...
 * @package Centreon\Internal
 */
class Bootstrap
{
    /**
     *
     * @var \Centreon\Internal\Di
     */
    private $di;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->di = new Di();
    }

    /**
     * Init a selection of methods
     *
     * @param array $sections
     */    
    private function customInit(array $sections)
    {
        foreach($sections as $section) {
            $method = "init" . ucfirst($section);
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    /**
     * Init method
     */
    public function init($sectionToInit = null)
    {
        if (isset($sectionToInit)) {
            $this->customInit($sectionToInit);
        } else {
            $class = new \ReflectionClass(__CLASS__);
            $methods = $class->getMethods(\ReflectionMethod::IS_PRIVATE);
            foreach ($methods as $method) {
                if (preg_match('/^init/', $method->name)) {
                    $this->{$method->name}();
                }
            }
        }
    }

    /**
     * Init configuration object
     */
    private function initConfiguration()
    {
        $this->config = new Config(CENTREON_ETC . '/centreon.ini');
        $this->di->setShared('config', $this->config);
    }

    /**
     * Init the logger
     */
    private function initLogger()
    {
        Logger::load($this->config);
    }

    /**
     * Init database objects
     *
     * @todo add profiler
     */
    private function initDatabase()
    {
        $config = $this->config;
        $this->di->set(
            'db_centreon',
            function () use ($config) {
                return new Db(
                    $config->get('db_centreon', 'dsn'),
                    $config->get('db_centreon', 'username'),
                    $config->get('db_centreon', 'password'),
                    array(
                        \PDO::MYSQL_ATTR_FOUND_ROWS => true
                    )
                );
            }
        );
    }

    /**
     * Init cache
     */
    private function initCache()
    {
        $cache = Cache::load($this->config);
        $this->di->setShared('cache', $cache);
    }

    /**
     * Load configuration from database
     */
    private function initConfigFromDb()
    {
        $this->di->get('config')->loadFromDb();
    }

    /**
     * Load application constant
     *
     * Load module constant if database is loaded
     */
    private function initConstants()
    {
        require $this->di->get('config')->get('global', 'centreon_path') . '/core/internal/Constant.php';

        try {
            $this->di->get('db_centreon');
            foreach (Module::getModuleList() as $moduleName) {
                $modulePath = Module::getModulePath($moduleName);
                if (file_exists($modulePath . '/config/Constant.php')) {
                    require $modulePath . '/config/Constant.php';
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Init action hooks
     */
    private function initEvents()
    {
        $this->di->set(
            'events',
            function () {
                return new EventEmitter();
            }
        );
        Event::initEventListeners();
    }

    /**
     * Init template object
     */
    private function initTemplate()
    {
        $this->di->set(
            'template',
            function () {
                $tmpl = new Template();
                $tmpl->initStaticFiles();
                return $tmpl;
            }
        );
    }

    /**
     * Init menus
     */
    private function initMenus()
    {
        $this->di->set(
            'menu',
            function () {
                return new Menu();
            }
        );
    }
    
    /**
     * Init routes
     */
    private function initRoutes()
    {
        $this->di->set(
            'router',
            function () {
                $router = new Router();
                /* Add middleroute for CSRF token */
                $router->respond(function ($request, $response, $service, $app)
                    {
                        /* Get the token */
                        $headers = $request->headers();
                        $tokenValue = '';
                        foreach (Csrf::getHeaderNames() as $headerName) {
                            if ($headers->exists($headerName)) {
                                $tokenValue = $headers[$headerName];
                                break;
                            }
                        }
                        $toSend = false;
                        /*
                         * Test if must test the token 
                         * @todo better management with middleware global implementation
                         */
                        $excludeRoute = array(
                            '/api'
                        );
                        $matchingRoute = array_filter($excludeRoute, function ($route) use ($request) {
                            $route = rtrim(Di::getDefault()->get('config')->get('global', 'base_url'), '/') . $route;
                            if ($route == substr($request->pathname(), 0, strlen($route))) {
                                return true;
                            }
                            return false;
                        });
                        if (count($matchingRoute) == 0) {
                            if (false === Csrf::checkToken($tokenValue, $request->method())) {
                                $toSend = true;
                                $response->code(403)->json(array("message" => "CSRF Token is no valid"));
                                $response->send();
                                // @todo Exception
                                exit();
                            } else {
                                if (Csrf::mustBeGenerate($request->method())) {
                                    /* Generate and send a new csrf cookie */
                                    $response->cookie(Csrf::getCookieName(), Csrf::generateToken(), 0);
                                    $response->sendCookies(true);
                                }
                            }
                        }
                    }
                );
                /* Parsing route */
                $router->parseRoutes();
                return $router;
            }
        );
    }

    /**
     * Init organization
     */
    private function initOrganization()
    {
        $this->di->setShared('organization', 1);
    }
}
