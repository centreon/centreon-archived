<?php
/*
 * Copyright 2005-2011 MERETHIS
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

/**
 * Class for dependency injector
 *
 * $di = new \Centreon\Core\Di();
 * $di->set('test', function () {
 *   return array("test" => "test");
 * });
 * $di->set('test2', 'MyClass');
 * $di->setShared('test3', $variable);
 * 
 * $test = $di->get('test');
 *
 * @authors Maximilien Bersoult <mbersoult@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Di
{
    private static $instance = null;

    private $shared;
    private $services;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (is_null(self::$instance)) {
            self::$instance = $this;
        }
    }

    /**
     * Add a service into DI
     *
     * @param $name string The name of the service
     * @param $definition string|\Closure The service
     *        string: The class name
     *        \Closure: A function which return a variable
     * @param $shared bool If the service is shared, the definition can be mixed
     */
    public function set($name, $definition, $shared=false)
    {
        if ($shared) {
            $this->setShared($name, $definition);
        }
        $this->services[$name] = $definition;
    }

    /**
     * Add a shared service
     *
     * @param $name string The name of the service
     * @param $definition mixed A value for the service
     */
    public function setShared($name, $definition)
    {
        $this->shared[$name] = $definition;
    }

    /**
     * Test if a service is registred
     *
     * @param $name string The name of the service
     * @return bool
     */
    public function has($name)
    {
        if (isset($this->shared[$name])) {
            return true;
        }
        if (isset($this->services[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Get a value of a service
     *
     * @param $name string The name of the service
     * @return mixed
     * @throws \Centreon\Exception If the service is not defined
     */
    public function get($name)
    {
        if (false === $this->has($name)) {
            throw new Exception("The service injector is not defined.");
        }
        if (false === isset($this->shared[$name])) {
            if (is_string($this->services[$name])) {
                $this->shared[$name] = new $this->services[$name]();
            } elseif (is_a($this->services[$name], '\Closure')) {
                $this->shared[$name] = $this->services[$name]();
            } else {
                throw new Exception("Bad type of service");
            }
        }
        return $this->shared[$name];
    }

    /**
     * Set the default instance of dependency injector
     *
     * @param $di \Centreon\Core\Di
     */
    public static function setDefault(\Centreon\Core\Di $di)
    {
        self::$instance = $di;
    }

    /**
     * Return the default dependency injector instance
     *
     * @return \Centreon\Core\Di
     */
    public static function getDefault()
    {
        return self::$instance;
    }

    /**
     * Reset the instance of dependency injector
     */
    public static function reset()
    {
        self::$instance = null;
    }
}
