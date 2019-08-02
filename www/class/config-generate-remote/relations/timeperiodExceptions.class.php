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

class timeperiodExceptions extends AbstractObject
{
    protected $table = 'timeperiod_exceptions';
    protected $generate_filename = 'timeperiod_exceptions.infile';
    protected $attributes_write = array(
        'timeperiod_id',
        'days',
        'timerange',
    );

    public function add($object, $tp_id)
    {
        $this->generateObjectInFile($object, $tp_id);
    }
}
