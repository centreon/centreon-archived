<?php
/**
 * Copyright 2019 Centreon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!function_exists('loadDependencyInjector')) {

    class DependencyInjector implements ArrayAccess
    {

        protected static $instance;
        protected $container;

        public static function getInstance(): self
        {
            if (!static::$instance instanceof DependencyInjector) {
                static::$instance = new DependencyInjector;
            }

            return static::$instance;
        }

        public function offsetSet($offset, $value)
        {
            if (is_null($offset)) {
                $this->container[] = $value;
            } else {
                $this->container[$offset] = $value;
            }
        }

        public function offsetExists($offset)
        {
            return isset($this->container[$offset]);
        }

        public function offsetUnset($offset)
        {
            unset($this->container[$offset]);
        }

        public function offsetGet($offset)
        {
            return isset($this->container[$offset]) ? $this->container[$offset] : null;
        }
    }

    // Mock DB manager
    DependencyInjector::getInstance()[Centreon\ServiceProvider::CENTREON_DB_MANAGER] = new Centreon\Test\Mock\CentreonDBManagerService;

    function loadDependencyInjector()
    {
        return DependencyInjector::getInstance();
    }
}