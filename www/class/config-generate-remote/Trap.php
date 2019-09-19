<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace ConfigGenerateRemote;

use \PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class Trap extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $trapCache = [];
    private $serviceLinkedCache = [];

    protected $table = 'traps';
    protected $generateFilename = 'traps.infile';
    protected $stmtService = null;

    protected $attributesWrite = [
        'traps_id',
        'traps_name',
        'traps_oid',
        'traps_args',
        'traps_status',
        'severity_id',
        'manufacturer_id',
        'traps_reschedule_svc_enable',
        'traps_execution_command',
        'traps_execution_command_enable',
        'traps_submit_result_enable',
        'traps_advanced_treatment',
        'traps_advanced_treatment_default',
        'traps_timeout',
        'traps_exec_interval',
        'traps_exec_interval_type',
        'traps_log',
        'traps_routing_mode',
        'traps_routing_value',
        'traps_routing_filter_services',
        'traps_exec_method',
        'traps_downtime',
        'traps_output_transform',
        'traps_customcode',
        'traps_comments'
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
     * Build cache of traps
     *
     * @return void
     */
    private function cacheTrap()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT * FROM traps
            LEFT JOIN traps_vendor ON traps_vendor.id = traps.manufacturer_id"
        );

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->trapCache[$value['traps_id']] = &$value;
        }
    }

    /**
     * Build cache of relations between service and trap
     *
     * @return void
     */
    private function cacheTrapLinked()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT traps_id, service_id
            FROM traps_service_relation"
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (!isset($this->serviceLinkedCache[$value['service_id']])) {
                $this->serviceLinkedCache[$value['service_id']] = [];
            }
            $this->serviceLinkedCache[$value['service_id']][] = $value['traps_id'];
        }
    }

    /**
     * Build cache
     *
     * @return void|int
     */
    private function buildCache()
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $this->cacheTrap();
        $this->cacheTrapLinked();
        $this->doneCache = 1;
    }

    /**
     * Generate trap and relations
     *
     * @param integer $serviceId
     * @param array $serviceLinkedCache
     * @param array $object
     * @return void
     */
    public function generateObject(int $serviceId, array $serviceLinkedCache, array &$object)
    {
        foreach ($serviceLinkedCache as $trapId) {
            Relations\TrapsServiceRelation::getInstance($this->dependencyInjector)->addRelation($trapId, $serviceId);
            if ($this->checkGenerate($trapId)) {
                continue;
            }
            $this->generateObjectInFile($object[$trapId], $trapId);
            Relations\TrapsVendor::getInstance($this->dependencyInjector)->add(
                $object[$trapId]['id'],
                $object[$trapId]['name'],
                $object[$trapId]['alias'],
                $object[$trapId]['description']
            );
            Relations\TrapsGroup::getInstance($this->dependencyInjector)->getTrapGroupsByTrapId($trapId);
            Relations\TrapsMatching::getInstance($this->dependencyInjector)->getTrapMatchingByTrapId($trapId);
            Relations\TrapsPreexec::getInstance($this->dependencyInjector)->getTrapPreexecByTrapId($trapId);
            ServiceCategory::getInstance($this->dependencyInjector)->generateObject($object[$trapId]['severity_id']);
        }
    }

    /**
     * Get service linked traps
     *
     * @param integer $serviceId
     * @return null|array
     */
    public function getTrapsByServiceId(int $serviceId)
    {
        // Get from the cache
        if (isset($this->serviceLinkedCache[$serviceId])) {
            $this->generateObject($serviceId, $this->serviceLinkedCache[$serviceId], $this->trapCache);
            return $this->serviceLinkedCache[$serviceId];
        } elseif ($this->useCache == 1) {
            return null;
        }

        // We get unitary
        if (is_null($this->stmtService)) {
            $this->stmtService = $this->backendInstance->db->prepare(
                "SELECT traps.*, traps_service_relation.service_id
                FROM traps_service_relation, traps
                LEFT JOIN traps_vendor ON traps_vendor.id = traps.manufacturer_id
                WHERE traps_service_relation.service_id = :service_id
                AND traps_service_relation.traps_id = traps.traps_id"
            );
        }

        $this->stmtService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtService->execute();
        $serviceLinkedCache = [];
        $trapCache = [];
        foreach ($this->stmtService->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $serviceLinkedCache[] = $value['traps_id'];
            $trapCache[$value['traps_id']] = $value;
        }

        $this->generateObject($serviceId, $serviceLinkedCache, $trapCache);
        return $serviceLinkedCache;
    }
}
