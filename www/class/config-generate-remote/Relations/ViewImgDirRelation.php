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

class ViewImgDirRelation extends AbstractObject
{
    protected $table = 'view_img_dir_relation';
    protected $generateFilename = 'view_img_dir_relation.infile';
    protected $attributesWrite = [
        'dir_dir_parent_id',
        'img_img_id',
    ];

    /**
     * Add relation
     *
     * @param integer $mediaId
     * @param integer $dirId
     * @return void
     */
    public function addRelation(int $mediaId, int $dirId)
    {
        $relation = [
            'dir_dir_parent_id' => $dirId,
            'img_img_id' => $mediaId,
        ];
        $this->generateObjectInFile($relation, $mediaId . '.' . $dirId);
    }
}
