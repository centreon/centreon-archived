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

class viewImageDir extends AbstractObject
{
    protected $table = 'view_img_dir';
    protected $generate_filename = 'view_image_dir.infile';
    protected $attributes_write = [
        'dir_id',
        'dir_name',
        'dir_alias',
        'dir_comment',
    ];

    public function add($object, $dir_id)
    {
        if ($this->checkGenerate($dir_id)) {
            return null;
        }

        $this->generateObjectInFile($object, $dir_id);
    }
}
