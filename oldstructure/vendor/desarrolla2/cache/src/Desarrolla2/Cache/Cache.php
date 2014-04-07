<?php

/**
 * This file is part of the Cache project.
 *
 * Description of Cache
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 * @file   : Cache.php , UTF-8
 * @date   : Sep 4, 2012 , 12:45:14 AM
 */

namespace Desarrolla2\Cache;

use Desarrolla2\Cache\CacheInterface;
use Desarrolla2\Cache\Exception\AdapterNotSetException;
use Desarrolla2\Cache\Adapter\AdapterInterface;

/**
 * Class Cache
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class Cache implements CacheInterface
{
    const VERSION = 1.7;

    /**
     *
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter = null)
    {
        if ($adapter) {
            $this->setAdapter($adapter);
        }
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     */
    public function delete($key)
    {
        $this->getAdapter()->delete($key);
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     */
    public function get($key)
    {
        return $this->getAdapter()->get($key);
    }

    /**
     * {@inheritdoc }
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            throw new AdapterNotSetException('Required Adapter');
        }

        return $this->adapter;
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     */
    public function has($key)
    {
        return $this->getAdapter()->has($key);
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     * @param mixed  $value
     * @param null   $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        $this->getAdapter()->set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc }
     *
     * @param Adapter\AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function setOption($key, $value)
    {
        return $this->adapter->setOption($key, $value);
    }

    /**
     * {@inheritdoc }
     */
    public function clearCache()
    {
        $this->adapter->clearCache();
    }

    /**
     * {@inheritdoc }
     */
    public function dropCache()
    {
        $this->adapter->dropCache();
    }
}
