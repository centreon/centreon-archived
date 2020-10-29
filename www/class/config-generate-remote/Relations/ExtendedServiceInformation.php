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

class ExtendedServiceInformation extends AbstractObject
{
    protected $table = 'extended_service_information';
    protected $generateFilename = 'extended_service_information.infile';
    protected $attributesWrite = [
        'service_service_id',
        'esi_notes',
        'esi_notes_url',
        'esi_action_url',
        'esi_icon_image',
        'esi_icon_image_alt',
        'graph_id',
    ];

    /**
     * Add relation
     *
     * @param array $object
     * @param integer $serviceId
     * @return void
     */
    public function add(array $object, int $serviceId)
    {
        if ($this->checkGenerate($serviceId)) {
            return null;
        }

        $object['service_service_id'] = $serviceId;
        $this->generateObjectInFile($object, $serviceId);
    }
}
