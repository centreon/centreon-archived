<?php
namespace Test\Centreon;

use \Centreon\Internal\Config,
    \Centreon\Internal\Di,
    \Centreon\Internal\Template;

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
