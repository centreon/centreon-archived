<?php

/**
 * This file is part of the Cache project.
 *
 * Copyright (c)
 * Daniel González <daniel.gonzalez@freelancemadrid.es>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */

namespace Desarrolla2\Cache\Adapter;

use Desarrolla2\Cache\Exception\CacheException;
use Desarrolla2\Cache\Adapter\AdapterInterface;

/**
 *
 * Description of AbstractAdapter
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * @var int
     */
    protected $ttl = 3600;

    /**
     * {@inheritdoc }
     */
    public function __construct()
    {
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
                    throw new CacheException('ttl cant be lower than 1');
                }
                $this->ttl = $value;
                break;
            default:
                throw new CacheException('option not valid ' . $key);
        }

        return true;
    }

    /**
     * {@inheritdoc }
     */
    public function clearCache()
    {
        throw new Exception('not ready yet');
    }

    /**
     * {@inheritdoc }
     */
    public function dropCache()
    {
        throw new Exception('not ready yet');
    }

    /**
     *
     * @param  string $key
     * @return string
     */
    protected function getKey($key)
    {
        //return md5($key);
        return $key;
    }

    protected function serialize($value)
    {
        return serialize($value);
    }

    protected function unserialize($value)
    {
        return unserialize($value);
    }
}
