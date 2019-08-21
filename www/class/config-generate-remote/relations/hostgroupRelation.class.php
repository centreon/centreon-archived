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

class hostgroupRelation extends AbstractObject
{
    protected $table = 'hostgroup_relation';
    protected $generate_filename = 'hostgroup_relation.infile';
    protected $attributes_write = [
        'host_host_id',
        'hostgroup_hg_id',
    ];

    public function addRelation($hg_id, $host_id)
    {
        if ($this->checkGenerate($hg_id . '.' . $host_id)) {
            return null;
        }
        $relation = [
            'hostgroup_hg_id' => $hg_id,
            'host_host_id' => $host_id,
        ];
        $this->generateObjectInFile($relation, $hg_id . '.' . $host_id);
    }
}
