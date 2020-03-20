<?php

/*
 * Copyright 2020 Centreon
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

namespace Tests\Centreon;

use ArrayAccess;

class DependencyInjector implements ArrayAccess
{
    /**
     * @var \DependencyInjector
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $container;

    /**
     * Get instance of the object
     *
     * @return \self
     */
    public static function getInstance(): self
    {
        if (!static::$instance instanceof DependencyInjector) {
            static::$instance = new DependencyInjector();
        }

        return static::$instance;
    }

    /**
     * Setter
     *
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param string|int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Getter
     *
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
