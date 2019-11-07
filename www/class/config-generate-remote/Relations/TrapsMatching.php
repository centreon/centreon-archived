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
use ConfigGenerateRemote\ServiceCategory;

class TrapsMatching extends AbstractObject
{
    private $useCache = 1;
    private $doneCache = 0;

    private $trapMatchCache = [];

    protected $table = 'traps_matching_properties';
    protected $generateFilename = 'traps_matching_properties.infile';
    protected $stmtTrap = null;

    protected $attributesWrite = [
        'tmo_id',
        'trap_id',
        'tmo_order',
        'tmo_regexp',
        'tmo_string',
        'tmo_status',
        'severity_id'
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
     * Build cache for trap matches
     *
     * @return void
     */
    private function cacheTrapMatch()
    {
        $stmt = $this->backendInstance->db->prepare(
            "SELECT *
            FROM traps_matching_properties"
        );

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            if (!isset($this->trapMatchCache[$value['trap_id']])) {
                $this->trapMatchCache[$value['trap_id']] = [];
            }
            $this->trapMatchCache[$value['trap_id']][] = &$value;
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

        $this->cacheTrapMatch();
        $this->doneCache = 1;
    }

    /**
     * Generate object
     *
     * @param integer $trapId
     * @param array $trapMatchCache
     * @return void
     */
    public function generateObject($trapId, $trapMatchCache)
    {
        foreach ($trapMatchCache as $value) {
            if ($this->checkGenerate($value['tmo_id'])) {
                continue;
            }
            $this->generateObjectInFile($value, $value['tmo_id']);
            ServiceCategory::getInstance($this->dependencyInjector)->generateObject($value['severity_id']);
        }
    }

    /**
     * Get trap matching from trap id
     *
     * @param integer $trapId
     * @return void
     */
    public function getTrapMatchingByTrapId(int $trapId)
    {
        # Get from the cache
        if (isset($this->trapMatchCache[$trapId])) {
            $this->generateObject($trapId, $this->trapMatchCache[$trapId]);
            return $this->trapMatchCache[$trapId];
        } elseif ($this->useCache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmtTrap)) {
            $this->stmtTrap = $this->backendInstance->db->prepare(
                "SELECT *
                FROM traps_matching_properties
                WHERE trap_id = :trap_id"
            );
        }

        $this->stmtTrap->bindParam(':trap_id', $trapId, PDO::PARAM_INT);
        $this->stmtTrap->execute();
        $trapMatchCache = [];
        foreach ($this->stmtTrap->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $trapMatchCache[$value['traps_id']] = $value;
        }

        $this->generateObject($trapId, $trapMatchCache[$trapId]);

        return $trapMatchCache;
    }
}
