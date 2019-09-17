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

class TrapsGroupRelation extends AbstractObject
{
    protected $table = 'traps_group_relation';
    protected $generateFilename = 'traps_group_relation.infile';
    protected $attributesWrite = [
        'traps_group_id',
        'traps_id',
    ];

    /**
     * Add relation
     *
     * @param integer $trapsId
     * @param integer $trapsGroupId
     * @return void
     */
    public function addRelation(int $trapsId, int $trapsGroupId)
    {
        $relation = [
            'traps_id' => $trapsId,
            'traps_group_id' => $trapsGroupId,
        ];
        $this->generateObjectInFile($relation);
    }
}
