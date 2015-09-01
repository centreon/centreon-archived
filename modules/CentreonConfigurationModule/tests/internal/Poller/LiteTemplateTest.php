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

namespace Test\CentreonConfiguration\Internal\Poller;

use \Test\Centreon\SimpleTestCase;
use CentreonConfiguration\Internal\Poller\LiteTemplate;


/**
 * Description of TemplateTest
 *
 * @author lionel
 */
class LiteTemplateTest extends SimpleTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/pollertemplates/central.json';
    
    public function testSetLiteTemplateName()
    {
        $myTestLiteTemplate = new LiteTemplate('test');
        $myTestLiteTemplate->setName('myTestLiteTemplate');
        $this->assertEquals('myTestLiteTemplate', $myTestLiteTemplate->getName());
    }
    
    public function testGetLiteTemplateName()
    {
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate');
        $this->assertEquals('myTestLiteTemplate', $myTestLiteTemplate->getName());
    }
    
    public function testSetEnginePath()
    {
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate');
        $this->assertEquals('', $myTestLiteTemplate->getEnginePath());
        
        $myTestLiteTemplate->setEnginePath(CENTREON_PATH . $this->dataPath);
        $this->assertEquals(CENTREON_PATH . $this->dataPath, $myTestLiteTemplate->getEnginePath());
    }
    
    public function testGetEnginePath()
    {
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate', CENTREON_PATH . $this->dataPath);
        $this->assertEquals(CENTREON_PATH . $this->dataPath, $myTestLiteTemplate->getEnginePath());
    }
    
    public function testSetBrokerPath()
    {
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate');
        $this->assertEquals('', $myTestLiteTemplate->getBrokerPath());
        
        $myTestLiteTemplate->setBrokerPath(CENTREON_PATH . $this->dataPath);
        $this->assertEquals(CENTREON_PATH . $this->dataPath, $myTestLiteTemplate->getBrokerPath());
    }
    
    public function testGetBrokerPath()
    {
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate', '', CENTREON_PATH . $this->dataPath);
        $this->assertEquals(CENTREON_PATH . $this->dataPath, $myTestLiteTemplate->getBrokerPath());
    }
    
    public function testToFullTemplate()
    {
        $templatePath = CENTREON_PATH . $this->dataPath;
        $myTestLiteTemplate = new LiteTemplate('myTestLiteTemplate', $templatePath, $templatePath);
        $this->assertInstanceOf('\CentreonConfiguration\Internal\Poller\Template', $myTestLiteTemplate->toFullTemplate());
    }
}
