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

namespace Test\CentreonEngine\Repository;

use Test\Centreon\DbTestCase;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Filesystem\Directory;
use CentreonEngine\Repository\EngineRepository;
use CentreonEngine\Models\Engine;

class EngineRepositoryTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonEngineModule/tests/data/json/';

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSave()
    {
        $params['log_initial_states'] = 1;
        EngineRepository::save(1, $params);
        $value = Engine::get(1, 'log_initial_states');
        $this->assertEquals(1, $value['log_initial_states']);
    }
}
