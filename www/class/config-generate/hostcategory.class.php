<?php

/*
 * Copyright 2005-2015 Centreon
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

class Hostcategory extends AbstractObject
{
    private $hc = array();
    protected $generate_filename = 'tags.cfg';
    protected $object_name = 'tag';
    protected $attributes_select = '
        hc_id as id,
        hc_name as name
    ';
    protected $attributes_write = array(
        'id',
        'name',
        'type',
    );
    protected $stmt_hc = null;

    /**
     * @param int $hc_id
     */
    private function getHostcategoryFromId(int $hc_id): void
    {
        if (is_null($this->stmt_hc)) {
            $this->stmt_hc = $this->backend_instance->db->prepare(
                "SELECT {$this->attributes_select}
                FROM hostcategories
                WHERE hc_id = :hc_id
                AND level IS NULL
                AND hc_activate = '1'"
            );
        }
        $this->stmt_hc->bindParam(':hc_id', $hc_id, PDO::PARAM_INT);
        $this->stmt_hc->execute();
        $results = $this->stmt_hc->fetchAll(PDO::FETCH_ASSOC);
        $this->hc[$hc_id] = array_pop($results);
        if (is_null($this->hc[$hc_id])) {
            return;
        }
        $this->hc[$hc_id]['members'] = array();
    }

    /**
     * Add hostcategory to list and add host to its members
     *
     * @param int $hc_id
     * @param int $host_id
     * @param string $host_name
     */
    public function addHostInHc(int $hc_id, int $host_id, string $host_name): void
    {
        if (!isset($this->hc[$hc_id])) {
            $this->getHostcategoryFromId($hc_id);
        }
        if (is_null($this->hc[$hc_id]) || isset($this->hc[$hc_id]['members'][$host_id])) {
            return;
        }

        $this->hc[$hc_id]['members'][$host_id] = $host_name;
    }

    /**
     * Write hostcategories in configuration file
     */
    public function generateObjects(): void
    {
        foreach ($this->hc as $id => &$value) {
            if (! isset($value['members']) || count($value['members']) === 0) {
                continue;
            }
            $value['type'] = 'hostcategory';

            $this->generateObjectInFile($value, $id);
        }
    }

    /**
     * Reset instance
     */
    public function reset(): void
    {
        parent::reset();
        foreach ($this->hc as &$value) {
            if (!is_null($value)) {
                $value['members'] = array();
            }
        }
    }
}
