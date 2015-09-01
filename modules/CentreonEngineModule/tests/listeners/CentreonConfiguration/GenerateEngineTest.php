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

namespace Test\CentreonEngine\Listeners\CentreonConfiguration;

use \Test\Centreon\DbTestCase;
use Centreon\Internal\Di;
use CentreonEngine\Listeners\CentreonConfiguration\GenerateEngine;
use CentreonConfiguration\Events\GenerateEngine as GenerateEngineEvent;
use Centreon\Internal\Utils\Filesystem\Directory;

class GenerateEngineTest extends DbTestCase
{
    protected $dataPath = '/modules/CentreonEngineModule/tests/data/json/';
    protected static $bootstrapExtraSteps = array('events');

    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $genPath = Di::getDefault()->get('config')->get('global', 'centreon_generate_tmp_dir');
        if ($genPath != ""  && !is_dir($genPath)) {
            mkdir($genPath);
        }
    }

    static public function tearDownAfterClass()
    {
        $genPath = Di::getDefault()->get('config')->get('global', 'centreon_generate_tmp_dir');
        if ($genPath != "" && is_dir($genPath)) {
            Directory::delete($genPath, true);
        }
        parent::tearDownAfterClass();
    }

    public function testExecute()
    {
        $eventParams = new GenerateEngineEvent(1);
        GenerateEngine::execute($eventParams);
        $genPath = Di::getDefault()->get('config')->get('global', 'centreon_generate_tmp_dir');
        $this->assertFileExists($genPath . "/1/centengine.cfg");
        $this->assertFileExists($genPath . "/1/centengine-testing.cfg");
    }
}
