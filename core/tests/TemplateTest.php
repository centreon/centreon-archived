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

namespace Test\Centreon;

use Centreon\Internal\Config;
use Centreon\Internal\Di;
use Centreon\Internal\Template;

class TemplateTest extends DbTestCase
{
    private $datadir;
    protected static $bootstrapExtraSteps = array('template');

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $di = Di::getDefault();

        /* config */
        $config = new Config(CENTREON_PATH . '/core/tests/data/test-template.ini');
        $di->setShared('config', $config);

        /* router */
        ob_start();
        $di->get('router')->dispatch();
        ob_end_clean();

        /* template */
        $tpl = new Template();
        $di->setShared('template', $tpl);
        $tpl->setTemplateDir(CENTREON_PATH . '/core/tests/views/');
    }

    public function setUp()
    {
        parent::setUp();
/*
        $di = Di::getDefault();

        $this->datadir = CENTREON_PATH . '/core/tests/data';
        $config = new Config($this->datadir . '/test-template.ini');

        $di->setShared('config', $config);

        ob_start();
        $di->get('router')->dispatch();
        ob_end_clean();
        $tpl = new Template();
        $di->setShared('template', $tpl);
        $tpl->setTemplateDir(CENTREON_PATH . '/core/tests/views/');
 */
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testAddCss()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addCss('styles.css');
        $printedResult = $tpl->fetch('template/testAddCss.tpl');
        $this->assertContains('styles.css', $printedResult);
    }
    
    public function testAddJs()
    {
        $tpl = Di::getDefault()->get('template');
        $tpl->addJs('jquery.min.js')
            ->addJs('bootstrap.min.js');
        $printedResult = $tpl->fetch('template/testAddJs.tpl');
        $this->assertContains('jquery.min.js', $printedResult);
        $this->assertContains('bootstrap.min.js', $printedResult);
    }
    
    /*public function testTmpl()
    {
        $expectedTemplate = <<<'EOD'
        <html>

            <head>
                <title>Centreon - Home</title>
            </head>

            <body>

                <div id="appLayout">

                    <div id="appHeader">
                        My Header
                    </div>

                    <div id="appBody">
                        <div id="appLeftPanel">
                            My Menu
                        </div>
                        <div id="appRightPanel">
                            My Content
                        </div>
                    </div>

                    <div id="appFooter">
                        My Footer
                    </div>

                </div>

            </body>

        </html>
        EOD;

        $config = new Config($this->datadir . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $printedResult = $tpl->fetch('home/home.tpl');
        $this->assertEquals($expectedTemplate, $printedResult);
    }*/
}
