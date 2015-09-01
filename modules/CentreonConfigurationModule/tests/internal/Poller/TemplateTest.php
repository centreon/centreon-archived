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

use Test\Centreon\SimpleTestCase;
use CentreonConfiguration\Internal\Poller\Template;
use CentreonConfiguration\Internal\Poller\Template\Engine;
use CentreonConfiguration\Internal\Poller\Template\Broker;
use Centreon\Internal\Di;
use CentreonAdministration\Internal\User;


/**
 * Description of TemplateTest
 *
 * @author lionel
 */
class TemplateTest extends SimpleTestCase
{
    protected $dataPath = '/modules/CentreonConfigurationModule/tests/data/pollertemplates/central.json';
    
    public function testSetTemplateName()
    {
        $myTestTemplate = new Template('test');
        $myTestTemplate->setName('myTestTemplate');
        $this->assertEquals('myTestTemplate', $myTestTemplate->getName());
    }
    
    public function testGetTemplateName()
    {
        $myTestTemplate = new Template('myTestTemplate');
        $this->assertEquals('myTestTemplate', $myTestTemplate->getName());
    }
    
    public function testSetEnginePart()
    {
        $myTestTemplate = new Template('myTestTemplate');
        $myTestTemplate->setEnginePart(new Engine(CENTREON_PATH . $this->dataPath));
        $this->assertInstanceOf('\CentreonConfiguration\Internal\Poller\Template\Engine', $myTestTemplate->getEnginePart());
    }
    
    public function testGetEnginePart()
    {
        $myTestTemplate = new Template('myTestTemplate', CENTREON_PATH . $this->dataPath);
        $this->assertInstanceOf('\CentreonConfiguration\Internal\Poller\Template\Engine', $myTestTemplate->getEnginePart());
    }
    
    public function testSetBrokerPart()
    {
        $myTestTemplate = new Template('myTestTemplate');
        $myTestTemplate->setBrokerPart(new Broker(CENTREON_PATH . $this->dataPath));
        $this->assertInstanceOf('\CentreonConfiguration\Internal\Poller\Template\Broker', $myTestTemplate->getBrokerPart());
    }
    
    public function testGetBrokerPart()
    {
        $myTestTemplate = new Template('myTestTemplate', '', CENTREON_PATH . $this->dataPath);
        $this->assertInstanceOf('\CentreonConfiguration\Internal\Poller\Template\Broker', $myTestTemplate->getBrokerPart());
    }
    
    public function testGenForm()
    {
        $_SESSION['user'] = new User(1);
        $router = Di::getDefault()->get('router');
        $router->dispatch();

        $expectedField = '<div class="form-group ">';
            $expectedField .= '<div class="col-sm-2" style="text-align:right">';
                $expectedField .= '<label class="label-controller" for="TemporayFilePath">Temporay File Path</label>';
                $expectedField .= ' <span style="color:red">*</span>';
            $expectedField .= '</div>';
            $expectedField .= '<div class="col-sm-9">';
                $expectedField .= '<span>';
                    $expectedField .= '<input id="TemporayFilePath" type="text" name="TemporayFilePath" value="" class="form-control input-sm mandatory-field " placeholder="Temporay File Path" />';
                $expectedField .= '<span>';
            $expectedField .= '</div>';
        $expectedField .= '</div>';
        $expectedForms = array(
            'engine' => true,
            'broker' => true,
            'steps' => array(
                array(
                    'name' => 'MyStep',
                    'html' => '<div>' . $expectedField . '</div>'
                )
            )
        );
        
        $templatePath = CENTREON_PATH . $this->dataPath;
        $myTestTemplate = new Template('myTestTemplate', $templatePath, $templatePath);
        $this->assertEquals($expectedForms, $myTestTemplate->genForm());
    }
}
