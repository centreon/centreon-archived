<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
 *
 * For more information : contact@centreon.com
 *
 */

namespace ConfigGenerateRemote\Relations;

use \PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class BrokerInfo extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $brokerInfoCache = [];

    protected $table = 'cfg_centreonbroker_info';
    protected $generateFilename = 'cfg_centreonbroker_info.infile';
    protected $stmtBrokerInfo = null;

    protected $attributesWrite = [
        'config_id',
        'config_key',
        'config_value',
        'config_group',
        'config_group_id',
        'grp_level',
        'subgrp_id',
        'parent_grp_id',
        'fieldIndex'
    ];

    /**
     * Constructor
     *
     * @param \Pimple\Container $dependencyInjector
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    /**
     * Build cache of broker info
     *
     * @return void
     */
    private function cacheBrokerInfo()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT *
             FROM cfg_centreonbroker_info"
        );

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            if (!isset($this->brokerInfoCache[$value['config_id']])) {
                $this->brokerInfoCache[$value['config_id']] = [];
            }
            $this->brokerInfoCache[$value['config_id']][] = $value;
        }
    }

    /**
     * Build cache
     *
     * @return void
     */
    private function buildCache()
    {
        if ($this->doneCache === 0) {
            $this->cacheBrokerInfo();
            $this->doneCache = 1;
        }
    }

    /**
     * Generate broker info configs
     *
     * @param integer $configId
     * @param array $brokerInfoCache
     * @return void
     */
    public function generateObject(int $configId, array $brokerInfoCache)
    {
        foreach ($brokerInfoCache[$configId] as $value) {
            $this->generateObjectInFile($value);
        }
    }

    /**
     * Get broker information config
     *
     * @param  integer $configId
     * @return array
     */
    public function getBrokerInfoByConfigId(int $configId)
    {
        // Get from the cache
        if (isset($this->brokerInfoCache[$configId])) {
            $this->generateObject($configId, $this->brokerInfoCache);
            return $this->brokerInfoCache[$configId];
        } elseif ($this->useCache === 1) {
            return [];
        }

        // We get unitary
        if (is_null($this->stmtBrokerInfo)) {
            $this->stmtBrokerInfo = $this->backendInstance->db->prepare(
                "SELECT *
                FROM cfg_centreonbroker_info
                WHERE config_id = :config_id
                AND config_group"
            );
        }

        $this->stmtBrokerInfo->bindParam(':config_id', $configId, PDO::PARAM_INT);
        $this->stmtBrokerInfo->execute();
        $brokerInfoCache = [ $config_id => [] ];
        foreach ($this->stmtBrokerInfo->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $brokerInfoCache[$config_id] = $value;
        }

        $this->generateObject($configId, $brokerInfoCache);

        return $brokerInfoCache;
    }
}
