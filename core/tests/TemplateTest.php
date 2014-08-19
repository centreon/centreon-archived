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
 *
 */

namespace Test\Centreon;

use \Centreon\Internal\Config;
use \Centreon\Internal\Di;
use \Centreon\Internal\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    private $datadir;

    public function setUp()
    {
        $this->datadir = CENTREON_PATH . '/core/tests/data';
        $config = new Config($this->datadir . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $di->setShared('template', $tpl);
        $tpl->setTemplateDir(CENTREON_PATH . '/core/tests/views/');
        parent::setUp();
    }

    public function tearDown()
    {
        Di::reset();
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
