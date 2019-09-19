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

class HostTemplateRelation extends AbstractObject
{
    protected $table = 'host_template_relation';
    protected $generateFilename = 'host_template_relation.infile';
    protected $attributesWrite = [
        'host_host_id',
        'host_tpl_id',
        'order',
    ];

    /**
     * Add relation
     *
     * @param integer $hostId
     * @param integer $hostTplId
     * @param integer $order
     * @return void
     */
    public function addRelation(int $hostId, int $hostTplId, $order)
    {
        $relation = [
            'host_host_id' => $hostId,
            'host_tpl_id' => $hostTplId,
            'order' => $order,
        ];
        $this->generateObjectInFile($relation, $hostId . '.' . $hostTplId . '.' . $order);
    }
}
