<?php

/*
 * Copyright 2005-2022 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

class Servicecategory extends AbstractObject
{
    private $done_cache = 0;

    private $sc = [];
    private $sc_relation_cache = [];
    protected $generate_filename = 'tags.cfg';
    protected $object_name = 'tag';
    protected $attributes_select = '
        sc_id as id,
        sc_name as name
    ';
    protected $attributes_write = [
        'id',
        'name',
        'type',
    ];

    /**
     * @param \Pimple\Container $dependencyInjector
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    /**
     * Build cache for service categories
     */
    private function buildCache(): void
    {
        if ($this->done_cache === 1) {
            return;
        }

        $stmt = $this->backend_instance->db->prepare(
            "SELECT service_categories.sc_id, service_service_id
            FROM service_categories, service_categories_relation
            WHERE level IS NULL
            AND sc_activate = '1'
            AND service_categories_relation.sc_id = service_categories.sc_id"
        );
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->sc_relation_cache[$value['service_service_id']][] = $value['sc_id'];
        }

        $this->done_cache = 1;
    }

    /**
     * Get categories linked to service template
     *
     * @param int $serviceId
     * @return int[]
     */
    public function getServicecategoriesForStpl(int $serviceId): array
    {
        # Get from the cache
        if (isset($this->sc_relation_cache[$serviceId])) {
            return $this->sc_relation_cache[$serviceId];
        }
        if ($this->done_cache === 1) {
            return array();
        }

        # We get unitary
        $stmt = $this->backend_instance->db->prepare(
            "SELECT service_categories.sc_id, service_service_id
            FROM service_categories, service_categories_relation
            WHERE level IS NULL
            AND sc_activate = '1'
            AND service_categories_relation.sc_id = service_categories.sc_id
            AND service_categories_relation.service_service_id = :serviceId"
        );
        $stmt->bindParam(':serviceId', $serviceId, PDO::PARAM_INT);
        $stmt->execute();

        $categories = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $categories[] = $value['sc_id'];
        }
        $this->sc_relation_cache[$serviceId] = $categories;

        return $categories;
    }

    /**
     * Retrieve a categorie from its id
     *
     * @param int $scId
     * @return void
     */
    private function getServicecategoryFromId(int $scId): void
    {
        $stmt = $this->backend_instance->db->prepare(
            "SELECT {$this->attributes_select}
            FROM service_categories
            WHERE sc_id = :scId AND level IS NULL AND sc_activate = '1'"
        );
        $stmt->bindParam(':scId', $scId, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->sc[$scId] = array_pop($results);
        if (is_null($this->sc[$scId])) {
            return;
        }
        $this->sc[$scId]['members'] = array();
    }

    /**
     * Add a service to members of a servicecategory
     *
     * @param int $scId
     * @param int $serviceId
     * @param string $serviceDescription
     */
    public function addServiceInSc(int $scId, int $serviceId, string $serviceDescription): void
    {
        if (! isset($this->sc[$scId])) {
            $this->getServicecategoryFromId($scId);
        }
        if (is_null($this->sc[$scId]) || isset($this->sc[$scId]['members'][$serviceId])) {
            return;
        }

        $this->sc[$scId]['members'][$serviceId] = $serviceDescription;
    }

    /**
     * Write servicecategories in configuration file
     */
    public function generateObjects(): void
    {
        foreach ($this->sc as $id => &$value) {
            if (! isset($value['members']) || count($value['members']) === 0) {
                continue;
            }

            $value['type'] = 'servicecategory';

            $this->seekFileEnd();
            $this->generateObjectInFile($value, $id);
        }
    }

    /**
     * Reset instance
     */
    public function reset(): void
    {
        parent::reset();
        foreach ($this->sc as &$value) {
            if (! is_null($value)) {
                $value['members'] = array();
            }
        }
    }
}
