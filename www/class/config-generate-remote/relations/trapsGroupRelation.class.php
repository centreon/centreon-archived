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

class trapsGroupRelation extends AbstractObject
{
    protected $table = 'traps_group_relation';
    protected $generate_filename = 'traps_group_relation.infile';
    protected $attributes_write = array(
        'traps_group_id',
        'traps_id',
    );

    public function addRelation($traps_id, $traps_group_id)
    {
        $relation = array(
            'traps_id' => $traps_id,
            'traps_group_id' => $traps_group_id,
        );
        $this->generateObjectInFile($relation);
    }
}
