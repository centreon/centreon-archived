<?php

/**
 * This file is part of the Cache project.
 *
 * Description of Apc
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 */

namespace Desarrolla2\Cache\Adapter;

use Desarrolla2\Cache\Adapter\AbstractAdapter;
use Desarrolla2\Cache\Exception\ApcCacheException;

/**
 * Class Apc
 *
 * @author Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class Apc extends AbstractAdapter
{

    /**
     * Delete a value from the cache
     *
     * @param string $key
     * @throws \Desarrolla2\Cache\Exception\ApcCacheException
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            $tKey = $this->getKey($key);
            if (!\apc_delete($tKey)) {
                throw new ApcCacheException('Error deleting data with the key "' . $key . '" from the APC cache.');
            }
        }
    }

    /**
     * {@inheritdoc }
     */
    public function get($key)
    {
        if ($this->has($key)) {
            $tKey = $this->getKey($key);
            if (!$data = \apc_fetch($tKey)) {
                throw new ApcCacheException('Error fetching data with the key "' . $key . '" from the APC cache.');
            }

            return $this->unserialize($data);
        }

        return null;
    }

    /**
     * {@inheritdoc }
     */
    public function has($key)
    {
        $tKey = $this->getKey($key);
        if (function_exists("\apc_exists")) {
            return (boolean)\apc_exists($tKey);
        } else {
            \apc_fetch($tKey, $result);

            return (boolean)$result;
        }
    }

    /**
     * {@inheritdoc }
     */
    public function set($key, $value, $ttl = null)
    {
        $tKey = $this->getKey($key);
        $tValue = $this->serialize($value);
        if (!$ttl) {
            $ttl = $this->ttl;
        }
        if (!\apc_store($tKey, $tValue, $ttl)) {
            throw new ApcCacheException('Error saving data with the key "' . $key . '" to the APC cache.');
        }
    }

    /**
     * {@inheritdoc }
     */
    public function setOption($key, $value)
    {
        switch ($key) {
            case 'ttl':
                $value = (int)$value;
                if ($value < 1) {
                    throw new ApcCacheException('ttl cant be lower than 1');
                }
                $this->ttl = $value;
                break;
            default:
                throw new ApcCacheException('option not valid ' . $key);
        }

        return true;
    }
}
