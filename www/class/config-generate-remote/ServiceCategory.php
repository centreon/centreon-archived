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

class ServiceCategory extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $serviceSeverityCache = [];
    private $serviceSeverityByNameCache = [];
    private $serviceLinkedCache = [];

    protected $table = 'service_categories';
    protected $generateFilename = 'servicecategories.infile';
    protected $stmtService = null;
    protected $stmtHcName = null;

    protected $attributesWrite = [
        'sc_id',
        'sc_name',
        'sc_description',
        'level',
        'icon_id',
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
     * Build cache of service severity
     *
     * @return void
     */
    private function cacheServiceSeverity()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT sc_name, sc_id, level, icon_id
            FROM service_categories
            WHERE level IS NOT NULL AND sc_activate = '1'"
        );

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->serviceSeverityByNameCache[$value['sc_name']] = &$value;
            $this->serviceSeverityCache[$value['sc_id']] = &$value;
        }
    }

    /**
     * Build cache of relations between service and severity
     *
     * @return void
     */
    private function cacheServiceSeverityLinked()
    {
        $stmt = $this->backendInstance->db->prepare(
            'SELECT service_categories.sc_id, service_service_id ' .
            'FROM service_categories, service_categories_relation ' .
            'WHERE level IS NOT NULL ' .
            'AND sc_activate = "1" ' .
            'AND service_categories_relation.sc_id = service_categories.sc_id'
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (isset($this->serviceLinkedCache[$value['service_service_id']])) {
                if ($this->serviceSeverityCache[$value['sc_id']]['level'] <
                    $this->serviceSeverityCache[$this->serviceLinkedCache[$value['service_service_id']]]
                ) {
                    $this->serviceLinkedCache[$value['service_service_id']] = $value['sc_id'];
                }
            } else {
                $this->serviceLinkedCache[$value['service_service_id']] = $value['sc_id'];
            }
        }
    }

    /**
     * Build cache
     */
    private function buildCache()
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $this->cacheServiceSeverity();
        $this->cacheServiceSeverityLinked();
        $this->doneCache = 1;
    }

    /**
     * Generate object
     *
     * @param null|integer $scId
     * @return void
     */
    public function generateObject(?int $scId)
    {
        if (is_null($scId) || $this->checkGenerate($scId)) {
            return null;
        }

        if (!isset($this->serviceSeverityCache[$scId])) {
            return null;
        }
        $this->generateObjectInFile($this->serviceSeverityCache[$scId], $scId);
        Media::getInstance($this->dependencyInjector)
            ->getMediaPathFromId($this->serviceSeverityCache[$scId]['icon_id']);
    }

    /**
     * Get severity by service id
     *
     * @param integer $serviceId
     * @return void
     */
    public function getServiceSeverityByServiceId(int $serviceId)
    {
        // Get from the cache
        if (isset($this->serviceLinkedCache[$serviceId])) {
            if (!$this->checkGenerate($this->serviceLinkedCache[$serviceId])) {
                $this->generateObjectInFile(
                    $this->serviceSeverityCache[$this->serviceLinkedCache[$serviceId]],
                    $this->serviceLinkedCache[$serviceId]
                );
                Media::getInstance($this->dependencyInjector)
                    ->getMediaPathFromId($this->serviceSeverityCache[$this->serviceLinkedCache[$serviceId]]['icon_id']);
            }
            return $this->serviceLinkedCache[$serviceId];
        }
        if ($this->doneCache == 1) {
            return null;
        }

        // We get unitary
        if (is_null($this->stmtService)) {
            $this->stmtService = $this->backendInstance->db->prepare(
                "SELECT service_categories.sc_id, sc_name, level, icon_id
                FROM service_categories_relation, service_categories
                WHERE service_categories_relation.service_service_id = :service_id
                    AND service_categories_relation.sc_id = service_categories.sc_id
                    AND level IS NOT NULL AND sc_activate = '1'
                ORDER BY level DESC
                LIMIT 1"
            );
        }

        $this->stmtService->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmtService->execute();
        $severity = array_pop($this->stmtService->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($severity)) {
            $this->serviceLinkedCache[$serviceId] = null;
            return null;
        }

        $this->serviceLinkedCache[$serviceId] = $severity['sc_id'];
        $this->serviceSeverityByNameCache[$severity['sc_name']] = &$severity;
        $this->serviceSeverityCache[$severity['sc_id']] = &$severity;

        $this->generateObjectInFile($severity, $severity['sc_id']);
        Media::getInstance($this->dependencyInjector)
            ->getMediaPathFromId($this->serviceSeverityCache[$this->serviceLinkedCache[$serviceId]]['icon_id']);
        return $severity['sc_id'];
    }

    /**
     * Get severity by id
     *
     * @param null|integer $scId
     * @return void
     */
    public function getServiceSeverityById(?int $scId)
    {
        if (is_null($scId)) {
            return null;
        }
        if (!isset($this->serviceSeverityCache[$scId])) {
            return null;
        }

        return $this->serviceSeverityCache[$scId];
    }

    /**
     * Get mapping with host severity name
     *
     * @param string $hcName
     * @return null|integer
     */
    public function getServiceSeverityMappingHostSeverityByName(string $hcName)
    {
        if (isset($this->serviceSeverityByNameCache[$hcName])) {
            return $this->serviceSeverityByNameCache[$hcName];
        }
        if ($this->doneCache == 1) {
            return null;
        }

        // We get unitary
        if (is_null($this->stmtHcName)) {
            $this->stmtHcName = $this->backendInstance->db->prepare(
                "SELECT sc_name, sc_id, level
                FROM service_categories
                WHERE sc_name = :sc_name AND level IS NOT NULL AND sc_activate = '1'"
            );
        }

        $this->stmtHcName->bindParam(':sc_name', $hcName, PDO::PARAM_STR);
        $this->stmtHcName->execute();
        $severity = array_pop($this->stmtHcName->fetchAll(PDO::FETCH_ASSOC));
        if (is_null($severity)) {
            $this->serviceSeverityByNameCache[$hcName] = null;
            return null;
        }

        $this->serviceSeverityByNameCache[$hcName] = &$severity;
        $this->serviceSeverityCache[$hcName] = &$severity;

        return $severity['sc_id'];
    }
}
