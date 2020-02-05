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

class ContactGroupHostRelation extends AbstractObject
{
    protected $table = 'contactgroup_host_relation';
    protected $generateFilename = 'contactgroup_host_relation.infile';
    protected $attributesWrite = [
        'host_host_id',
        'contactgroup_cg_id',
    ];

    /**
     * Add relation
     *
     * @param integer $hostId
     * @param integer $cgId
     * @return void
     */
    public function addRelation(int $hostId, int $cgId)
    {
        $relation = [
            'host_host_id' => $hostId,
            'contactgroup_cg_id' => $cgId,
        ];
        $this->generateObjectInFile($relation);
    }
}
