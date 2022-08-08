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

class ExtendedHostInformation extends AbstractObject
{
    protected $table = 'extended_host_information';
    protected $generateFilename = 'extended_host_information.infile';
    protected $attributesWrite = [
        'host_host_id',
        'ehi_notes',
        'ehi_notes_url',
        'ehi_action_url',
        'ehi_icon_image',
        'ehi_icon_image_alt',
        'ehi_2d_coords',
        'ehi_3d_coords',
    ];

    /**
     * Add relation
     *
     * @param array $object
     * @param integer $hostId
     * @return void
     */
    public function add(array $object, int $hostId)
    {
        if ($this->checkGenerate($hostId)) {
            return null;
        }

        $object['host_host_id'] = $hostId;
        $this->generateObjectInFile($object, $hostId);
    }
}
