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

class ViewImageDir extends AbstractObject
{
    protected $table = 'view_img_dir';
    protected $generateFilename = 'view_image_dir.infile';
    protected $attributesWrite = [
        'dir_id',
        'dir_name',
        'dir_alias',
        'dir_comment',
    ];

    /**
     * Add relation
     *
     * @param array $object
     * @param integer $dirId
     * @return void
     */
    public function add(array $object, int $dirId)
    {
        if ($this->checkGenerate($dirId)) {
            return null;
        }

        $this->generateObjectInFile($object, $dirId);
    }
}
