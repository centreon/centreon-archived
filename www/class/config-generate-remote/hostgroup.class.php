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

class Hostgroup extends AbstractObject
{
    private $hg = array();
    protected $table = 'hostgroup';
    protected $generate_filename = 'hostgroups.infile';
    protected $attributes_select = '
        hg_id,
        hg_name,
        hg_alias,
        hg_notes,
        hg_notes_url,
        hg_action_url,
        hg_icon_image,
        hg_map_icon_image,
        geo_coords,
        hg_rrd_retention
    ';
    protected $attributes_write = array(
        'hg_id',
        'hg_name',
        'hg_alias',
        'hg_notes',
        'hg_notes_url',
        'hg_action_url',
        'hg_icon_image',
        'hg_map_icon_image',
        'geo_coords',
        'hg_rrd_retention'
    );
    protected $stmt_hg = null;

    private function getHostgroupFromId($hg_id)
    {
        if (is_null($this->stmt_hg)) {
            $this->stmt_hg = $this->backend_instance->db->prepare("SELECT 
                    $this->attributes_select
                FROM hostgroup
                WHERE hg_id = :hg_id AND hg_activate = '1'
                ");
        }
        $this->stmt_hg->bindParam(':hg_id', $hg_id, PDO::PARAM_INT);
        $this->stmt_hg->execute();
        $results = $this->stmt_hg->fetchAll(PDO::FETCH_ASSOC);
        $this->hg[$hg_id] = array_pop($results);
        if (is_null($this->hg[$hg_id])) {
            return null;
        }
        $this->hg[$hg_id]['members'] = array();
    }

    public function addHostInHg($hg_id, $host_id, $host_name)
    {
        if (!isset($this->hg[$hg_id])) {
            $this->getHostgroupFromId($hg_id);
            $this->generateObjectInFile($this->hg[$hg_id], $hg_id);
            Media::getInstance($this->dependencyInjector)->getMediaPathFromId($this->hg[$hg_id]['hg_icon_image']);
            Media::getInstance($this->dependencyInjector)->getMediaPathFromId($this->hg[$hg_id]['hg_map_icon_image']);
        }
        if (is_null($this->hg[$hg_id]) || isset($this->hg[$hg_id]['members'][$host_id])) {
            return 1;
        }

        $this->hg[$hg_id]['members'][$host_id] = $host_name;
        return 0;
    }

    public function generateObjects()
    {
        foreach ($this->hg as $id => &$value) {
            if (count($value['members']) == 0) {
                continue;
            }
            $value['hostgroup_id'] = $value['hg_id'];

            $this->generateObjectInFile($value, $id);
        }
    }

    public function getHostgroups()
    {
        $result = array();
        foreach ($this->hg as $id => &$value) {
            if (is_null($value) || count($value['members']) == 0) {
                continue;
            }
            $result[$id] = &$value;
        }
        return $result;
    }

    public function reset($createfile=false)
    {
        parent::reset($createfile);
        foreach ($this->hg as &$value) {
            if (!is_null($value)) {
                $value['members'] = array();
            }
        }
    }

    public function getString($hg_id, $attr)
    {
        if (isset($this->hg[$hg_id][$attr])) {
            return $this->hg[$hg_id][$attr];
        }
        return null;
    }
}
