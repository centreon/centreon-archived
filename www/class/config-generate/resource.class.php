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

class Resource extends AbstractObject
{
    private $connectors = null;
    protected $generate_filename = 'resource.cfg';
    protected $object_name = null;
    protected $stmt = null;
    protected $attributes_hash = array(
        'resources'
    );

    public function generateFromPollerId($poller_id)
    {
        if (is_null($poller_id)) {
            return 0;
        }

        if (is_null($this->stmt)) {
            $query = "SELECT resource_name, resource_line FROM cfg_resource_instance_relations, cfg_resource " .
                "WHERE instance_id = :poller_id AND cfg_resource_instance_relations.resource_id = " .
                "cfg_resource.resource_id AND cfg_resource.resource_activate = '1'";
            $this->stmt = $this->backend_instance->db->prepare($query);
        }
        $this->stmt->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt->execute();

        $object = array('resources' => array());
        foreach ($this->stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $object['resources'][$value['resource_name']] = $value['resource_line'];
        }

        $this->generateFile($object);
    }
}
