<?php

/**
 * This file is part of the Cache project.
 *
 * Description of NoCache
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 * @file   : NoCache.php , UTF-8
 * @date   : Sep 4, 2012 , 1:00:01 AM
 */

namespace Desarrolla2\Cache\Adapter;

use Desarrolla2\Cache\Adapter\AbstractAdapter;

/**
 * Class NotCache
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class NotCache extends AbstractAdapter
{

    /**
     * Delete a value from the cache
     *
     * @param string $key
     */
    public function delete($key)
    {
    }

    /**
     * {@inheritdoc }
     */
    public function get($key)
    {
        return false;
    }

    /**
     * {@inheritdoc }
     */
    public function has($key)
    {
        return false;
    }

    /**
     * {@inheritdoc }
     */
    public function set($key, $value, $ttl = null)
    {
        return false;
    }

    /**
     * {@inheritdoc }
     */
    public function setOption($key, $value)
    {
        return false;
    }

}
