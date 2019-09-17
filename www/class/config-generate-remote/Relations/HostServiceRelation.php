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

use ConfigGenerateRemote\Abstracts\AbstractObject;

class HostServiceRelation extends AbstractObject
{
    protected $table = 'host_service_relation';
    protected $generateFilename = 'host_service_relation.infile';
    protected $attributesWrite = [
        'host_host_id',
        'hostgroup_hg_id',
        'service_service_id',
    ];

    /**
     * Add relation between host and service
     *
     * @param integer $hostId
     * @param integer $serviceId
     * @return void
     */
    public function addRelationHostService(int $hostId, int $serviceId)
    {
        $relation = [
            'host_host_id' => $hostId,
            'service_service_id' => $serviceId,
        ];
        $this->generateObjectInFile($relation, 'h_s.' . $hostId . '.' . $serviceId);
    }

    /**
     * Add relation between hostgroup and service
     *
     * @param integer $hgId
     * @param integer $serviceId
     * @return void
     */
    public function addRelationHgService(int $hgId, int $serviceId)
    {
        if ($this->checkGenerate('hg_s.' . $hgId . '.' . $serviceId)) {
            return null;
        }

        $relation = [
            'hostgroup_hg_id' => $hgId,
            'service_service_id' => $serviceId,
        ];
        $this->generateObjectInFile($relation, 'hg_s.' . $hgId . '.' . $serviceId);
    }
}
