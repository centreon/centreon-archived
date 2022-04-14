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

final class ServiceCategory extends AbstractObject
{
    private const TAG_TYPE = 'servicecategory';

    /** @var array<int,mixed> */
    private array $serviceCategories = [];
    /** @var array<int,int[]>|null */
    private array|null $serviceCategoriesRelationsCache = null;
    /** @var string */
    protected string $object_name = 'tag';

    /**
     * @param \Pimple\Container $dependencyInjector
     */
    protected function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->generate_filename = 'tags.cfg';
        $this->attributes_write =  [
            'id',
            'name',
            'type',
        ];
    }
    /**
     * Build cache for service categories
     */
    private function buildCache(): void
    {
        $stmt = $this->backend_instance->db->prepare(
            "SELECT service_categories.sc_id, service_service_id
            FROM service_categories, service_categories_relation
            WHERE level IS NULL
            AND sc_activate = '1'
            AND service_categories_relation.sc_id = service_categories.sc_id"
        );
        $stmt->execute();

        $this->serviceCategoriesRelationsCache = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $this->serviceCategoriesRelationsCache[(int) $value['service_service_id']][] = (int) $value['sc_id'];
        }
    }

    /**
     * Get service categories linked to service
     *
     * @param int $serviceId
     * @return int[]
     */
    public function getServiceCategoriesByServiceId(int $serviceId): array
    {
        if ($this->serviceCategoriesRelationsCache === null) {
            $this->buildCache();
        }

        return $this->serviceCategoriesRelationsCache[$serviceId] ?? [];
    }

    /**
     * Retrieve a categorie from its id
     *
     * @param int $serviceCategoryId
     * @return self
     */
    private function addServiceCategoryToList(int $serviceCategoryId): self
    {
        $stmt = $this->backend_instance->db->prepare(
            "SELECT sc_id as id, sc_name as name
            FROM service_categories
            WHERE sc_id = :serviceCategoryId AND level IS NULL AND sc_activate = '1'"
        );
        $stmt->bindParam(':serviceCategoryId', $serviceCategoryId, PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->serviceCategories[$serviceCategoryId] = $row;
            $this->serviceCategories[$serviceCategoryId]['members'] = [];
        }

        return $this;
    }

    /**
     * Add a service to members of a service category
     *
     * @param int $serviceCategoryId
     * @param int $serviceId
     * @param string $serviceDescription
     */
    public function insertServiceToServiceCategoryMembers(
        int $serviceCategoryId,
        int $serviceId,
        string $serviceDescription
    ): void {
        if (! isset($this->serviceCategories[$serviceCategoryId])) {
            $this->addServiceCategoryToList($serviceCategoryId);
        }
        if (
            isset($this->serviceCategories[$serviceCategoryId])
            && ! isset($this->serviceCategories[$serviceCategoryId]['members'][$serviceId])
        ) {
            $this->serviceCategories[$serviceCategoryId]['members'][$serviceId] = $serviceDescription;
        }
    }

    /**
     * Write servicecategories in configuration file
     */
    public function generateObjects(): void
    {
        $this->seekFileEnd();
        foreach ($this->serviceCategories as $id => &$value) {
            if (! isset($value['members']) || count($value['members']) === 0) {
                continue;
            }

            $value['type'] = self::TAG_TYPE;

            $this->generateObjectInFile($value, $id);
        }
    }

    /**
     * Reset instance
     */
    public function reset(): void
    {
        parent::reset();
        foreach ($this->serviceCategories as &$value) {
            if (! is_null($value)) {
                $value['members'] = [];
            }
        }
    }
 
    /**
     * @param int $serviceId
     * @return int[]
     */
    public function getIdsByServiceId(int $serviceId): array
    {
        $serviceCategoryIds = [];
        foreach ($this->serviceCategories as $id => $value) {
            if (isset($value['members']) && in_array($serviceId, array_keys($value['members']))) {
                $serviceCategoryIds[] = (int) $id;
            }
        }

        return $serviceCategoryIds;
    }
}
