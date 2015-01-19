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
