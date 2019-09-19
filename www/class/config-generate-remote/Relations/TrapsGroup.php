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

namespace ConfigGenerateRemote\Relations;

use \PDO;
use ConfigGenerateRemote\Abstracts\AbstractObject;

class TrapsGroup extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $trapGroupCache = [];
    private $trapLinkedCache = [];

    protected $table = 'traps_group';
    protected $generateFilename = 'traps_group.infile';
    protected $stmtTrap = null;

    protected $attributesWrite = [
        'traps_group_id',
        'traps_group_name'
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
     * Build cache of trap groups
     *
     * @return void
     */
    private function cacheTrapGroup()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT *
            FROM traps_group"
        );

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            $this->trapgroupCache[$value['traps_group_id']] = &$value;
        }
    }

    /**
     * Build cache of relations between traps and trap groups
     *
     * @return void
     */
    private function cacheTrapLinked()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT traps_group_id, traps_id
            FROM traps_group_relation"
        );

        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            if (!isset($this->serviceLinkedCache[$value['traps_id']])) {
                $this->trapLinkedCache[$value['traps_id']] = [];
            }
            $this->trapLinkedCache[$value['traps_id']][] = $value['traps_group_id'];
        }
    }

    /**
     * Build cache
     *
     * @return void
     */
    private function buildCache()
    {
        if ($this->doneCache == 1) {
            return 0;
        }

        $this->cacheTrapGroup();
        $this->cacheTrapLinked();
        $this->doneCache = 1;
    }

    /**
     * Generate trap group
     *
     * @param integer $trapId
     * @param array $trapLinkedCache
     * @param array $object
     * @return void
     */
    public function generateObject(int $trapId, array $trapLinkedCache, array &$object)
    {
        foreach ($trapLinkedCache as $trapGroupId) {
            trapsGroupRelation::getInstance($this->dependencyInjector)->addRelation($trapId, $trapGroupId);
            if ($this->checkGenerate($trapGroupId)) {
                continue;
            }
            $this->generateObjectInFile($object[$trapGroupId], $trapGroupId);
        }
    }

    /**
     * Get trap linked trap groups
     *
     * @param integer $trapId
     * @return void
     */
    public function getTrapGroupsByTrapId(int $trapId)
    {
        # Get from the cache
        if (isset($this->trapLinkedCache[$trapId])) {
            $this->generateObject($trapId, $this->trapLinkedCache[$trapId], $this->trapgroupCache);
            return $this->trapLinkedCache[$trapId];
        } elseif ($this->useCache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmtTrap)) {
            $this->stmtTrap = $this->backendInstance->db->prepare(
                "SELECT traps_group.*
                FROM traps_service_relation, traps_group
                WHERE traps_group_relation.traps_id = :trap_id
                AND traps_group_relation.traps_group_id = traps_group.traps_group_id"
            );
        }

        $this->stmtTrap->bindParam(':trap_id', $trapId, PDO::PARAM_INT);
        $this->stmtTrap->execute();
        $trapLinkedCache = [];
        $trapGroupCache = [];
        foreach ($this->stmtTrap->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $trapLinkedCache[] = $value['traps_group_id'];
            $trapGroupCache[$value['traps_id']] = $value;
        }

        $this->generateObject($trapId, $trapLinkedCache, $trapGroupCache);

        return $trapLinkedCache;
    }
}
