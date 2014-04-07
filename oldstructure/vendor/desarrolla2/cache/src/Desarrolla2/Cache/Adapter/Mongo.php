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

use Desarrolla2\Cache\Adapter\AbstractAdapter;
use Desarrolla2\Cache\Exception\MongoCacheException;
use Mongo as MongoBase;

/**
 *
 * Description of Mongo
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 */
class Mongo extends AbstractAdapter
{

    /**
     * @var \MongoDB
     */
    protected $database;

    /**
     * @var \Mongo
     */
    protected $mongo;

    /**
     *
     * @param  string $server
     * @param  array  $options
     * @param  string $database
     * @throws \Desarrolla2\Cache\Exception\MongoCacheException
     */
    public function __construct(
        $server = 'mongodb://localhost:27017',
        $options = array('connect' => true),
        $database = '__cache'
    ) {
        $this->mongo = new MongoBase($server, $options);
        if (!$this->mongo) {
            throw new MongoCacheException(' Mongo connection fails ');
        }
        $this->database = $this->mongo->selectDB($database);
    }

    /**
     * Delete a value from the cache
     *
     * @param string $key
     */
    public function delete($key)
    {
        $tKey = $this->getKey($key);
        $this->database->items->remove(array('key' => $tKey));
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     */
    public function get($key)
    {
        if ($data = $this->getData($key)) {
            return $data;
        }

        return false;
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        if ($this->getData($key)) {
            return true;
        }

        return false;
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
        $tKey = $this->getKey($key);
        $tValue = $this->serialize($value);
        if (!$ttl) {
            $ttl = $this->ttl;
        }
        $item = array(
            'key' => $tKey,
            'value' => $tValue,
            'ttl' => (int)$ttl + time(),
        );
        $this->delete($key);
        $this->database->items->insert($item);
    }

    /**
     * {@inheritdoc }
     *
     * @param string $key
     * @param string $value
     * @return bool
     * @throws \Desarrolla2\Cache\Exception\MongoCacheException
     */
    public function setOption($key, $value)
    {
        switch ($key) {
            case 'ttl':
                $value = (int)$value;
                if ($value < 1) {
                    throw new MongoCacheException('ttl cant be lower than 1');
                }
                $this->ttl = $value;
                break;
            default:
                throw new MongoCacheException('option not valid ' . $key);
        }

        return true;
    }

    /**
     * Get data value from file cache
     *
     * @param  string $key
     * @param bool    $delete
     * @return mixed
     */
    protected function getData($key, $delete = true)
    {
        $tKey = $this->getKey($key);
        $data = $this->database->items->findOne(array('key' => $tKey));
        if (count($data)) {
            $data = array_values($data);
            if (time() > $data[3]) {
                if ($delete) {
                    $this->delete($key);
                }

                return false;
            }

            return $this->unserialize($data[2]);
        }

        return false;
    }
}
