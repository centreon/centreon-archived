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
namespace Centreon\Core;

class Bootstrap
{
    /**
     *
     * @var \Centreon\Core\Di
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
     * Init method
     */
    public function init()
    {
        $class = new \ReflectionClass(__CLASS__);
        $methods = $class->getMethods(\ReflectionMethod::IS_PRIVATE);
        foreach ($methods as $method) {
            if (preg_match('/^init/', $method->name)) {
                $this->{$method->name}();
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
                return new \Centreon\Core\Db(
                    $config->get('db_centreon', 'dsn'),
                    $config->get('db_centreon', 'username'),
                    $config->get('db_centreon', 'password')
                );
            }
        );
        $this->di->set(
            'db_storage',
            function () use ($config) {
                return new \Centreon\Core\Db(
                    $config->get('db_storage', 'dsn'),
                    $config->get('db_storage', 'username'),
                    $config->get('db_storage', 'password')
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
     * Init action hooks
     */
    private function initActionHooks()
    {
        $this->di->set(
            'action_hooks',
            function () {
                return new \Evenement\EventEmitter();
            }
        );
        Hook::initActionListeners();
    }

    /**
     * Init template object
     */
    private function initTemplate()
    {
        $this->di->set(
            'template',
            function () {
                return new Template();
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
     *
     */
    private function initRoutes()
    {
        $router = new \Centreon\Core\Router();
        $this->di->set('router', $router);
        $router->parseRoutes(
            '\\Controllers',
            '../application/controllers/'
        );
        $router->dispatch();
    }
}
