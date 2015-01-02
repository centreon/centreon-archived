<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
