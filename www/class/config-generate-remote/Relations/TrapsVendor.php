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

class TrapsVendor extends AbstractObject
{
    protected $table = 'traps_vendor';
    protected $generateFilename = 'traps_vendor.infile';
    protected $attributesWrite = [
        'id',
        'name',
        'alias',
        'description'
    ];

    /**
     * Add relation
     *
     * @param int $id
     * @param string $name
     * @param string $alias
     * @param string|null $description
     * @return void
     */
    public function add(int $id, string $name, string $alias, ?string $description = '')
    {
        if ($this->checkGenerate($id)) {
            return null;
        }
        $relation = [
            'id' => $id,
            'name' => $name,
            'alias' => $alias,
            'description' => $description,
        ];
        $this->generateObjectInFile($relation, $id);
    }
}
