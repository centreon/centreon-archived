<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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


namespace Test\CentreonBroker\Listeners\CentreonConfiguration;

use \Test\Centreon\DbTestCase;
use CentreonBroker\Listeners\CentreonConfiguration\FormSave;
use CentreonConfiguration\Events\BrokerFormSave;

class FormSaveTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonBrokerModule/tests/data/json/';

    public function testExecute()
    {
        $event = new BrokerFormSave(1, array('event_queue_max_size' => 7000));
        FormSave::execute($event);
        $this->markTestIncomplete("Must finish the function.");
    }
}
