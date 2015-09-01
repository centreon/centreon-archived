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

use Desarrolla2\Cache\Cache as DesarrollaCache;
use Desarrolla2\Cache\Adapter\Apc as ApcCache;
use Desarrolla2\Cache\Adapter\MemCache;
use Desarrolla2\Cache\Adapter\Memcached;
use Desarrolla2\Cache\Adapter\NotCache;

/**
 * Class for loading cache informations
 *
 * @see http://www.php.net/manual/en/class.pdo.php
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Cache
{
    /**
     * Create cache object
     *
     * @param $config \Centreon\Config The application configuration
     * @return \Desarrolla2\Cache\Cache
     */
    public static function load($config)
    {
        $cacheType = null;
        if ($config->get('cache', 'enabled')) {
            $cacheType = $config->get('cache', 'type');
        }
        
        
        switch ($cacheType) {
            case 'apc':
                $driver = new ApcCache();
                break;
            case 'memcache':
                $driver = new MemCache();
                foreach ($config->get('cache', 'servers') as $server) {
                    list($serverHost, $serverPort) = explode(':', $server);
                    $driver->addServer($serverHost, $serverPort);
                }
                break;
            case 'memcached':
                $driver = new Memcached();
                foreach ($config->get('cache', 'servers') as $server) {
                    list($serverHost, $serverPort) = explode(':', $server);
                    $driver->addServer($serverHost, $serverPort);
                }
                break;
            case null:
            default:
                $driver = new NotCache();
                break;
        }
        
        $ttl = $config->get('cache', 'ttl', 3600);
        $driver->setOption('ttl', $ttl);
        
        return new DesarrollaCache($driver);
    }
}
