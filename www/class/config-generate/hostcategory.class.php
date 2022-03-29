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

class HostCategory extends AbstractObject
{
    /** @var array<int,mixed> */
    private $hostCategories = [];

    /** @var string */
    protected $generate_filename = 'tags.cfg';
    /** @var string */
    protected $object_name = 'tag';
    /** @var string */
    protected $attributesSelect = '
        hc_id as id,
        hc_name as name
    ';
    /** @var string[] */
    protected $attributes_write = [
        'id',
        'name',
        'type',
    ];

    /**
     * @param int $hostCategoryId
     */
    private function getHostcategoryFromId(int $hostCategoryId): void
    {
        $stmt = $this->backend_instance->db->prepare(
            "SELECT {$this->attributesSelect}
            FROM hostcategories
            WHERE hc_id = :hc_id
            AND level IS NULL
            AND hc_activate = '1'"
        );
        $stmt->bindParam(':hc_id', $hostCategoryId, PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->hostCategories[$hostCategoryId] = $row;
            $this->hostCategories[$hostCategoryId]['members'] = [];
        }
    }

    /**
     * Add a host to members of a host category
     *
     * @param int $hostCategoryId
     * @param int $hostId
     * @param string $hostName
     */
    public function addHostInHostCategories(int $hostCategoryId, int $hostId, string $hostName): void
    {
        if (!isset($this->hostCategories[$hostCategoryId])) {
            $this->getHostcategoryFromId($hostCategoryId);
        }
        if (
            ! isset($this->hostCategories[$hostCategoryId])
            || isset($this->hostCategories[$hostCategoryId]['members'][$hostId])
        ) {
            return;
        }

        $this->hostCategories[$hostCategoryId]['members'][$hostId] = $hostName;
    }

    /**
     * Write hostcategories in configuration file
     */
    public function generateObjects(): void
    {
        foreach ($this->hostCategories as $id => &$value) {
            if (! isset($value['members']) || count($value['members']) === 0) {
                continue;
            }
            $value['type'] = 'hostcategory';

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
        foreach ($this->hostCategories as &$value) {
            if (!is_null($value)) {
                $value['members'] = [];
            }
        }
    }
}
