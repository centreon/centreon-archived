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

/**
 * Class for dependency injector
 *
 * $di = new \Centreon\Internal\Di();
 * $di->set('test', function () {
 *   return array("test" => "test");
 * });
 * $di->set('test2', 'MyClass');
 * $di->setShared('test3', $variable);
 * 
 * $test = $di->get('test');
 *
 * @authors Maximilien Bersoult <mbersoult@centreon.com>
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
    public function set($name, $definition, $shared = false)
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
            throw new Exception("No service defined in DI with name: '" . $name . "'");
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
     * @param $di \Centreon\Internal\Di
     */
    public static function setDefault(Di $di)
    {
        self::$instance = $di;
    }

    /**
     * Return the default dependency injector instance
     *
     * @return \Centreon\Internal\Di
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
