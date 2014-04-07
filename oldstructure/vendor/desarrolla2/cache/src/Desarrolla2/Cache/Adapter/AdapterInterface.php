<?php

/**
 * This file is part of the Cache project.
 *
 * Description of AdapterInterface
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 * @file   : AdapterInterface.php , UTF-8
 * @date   : Sep 4, 2012 , 12:49:07 AM
 */

namespace Desarrolla2\Cache\Adapter;

/**
 * Interface AdapterInterface
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
interface AdapterInterface
{
    /**
     * Delete a value from the cache
     *
     * @param string $key
     */
    public function delete($key);

    /**
     * Retrieve the value corresponding to a provided key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Retrieve the if value corresponding to a provided key exist
     *
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * * Add a value to the cache under a unique key
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     */
    public function set($key, $value, $ttl = null);

    /**
     * Set option for Adapter
     *
     * @param string $key
     * @param string $value
     */
    public function setOption($key, $value);

    /**
     * clean all expired records from cache
     */
    public function clearCache();

    /**
     * clear all cache
     */
    public function dropCache();
}
