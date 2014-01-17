<?php
namespace Test\Centreon\Core;

use \Centreon\Core\Config,
    \Centreon\Core\Di,
    \Centreon\Core\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testAddCss()
    {
        $config = new Config(DATA_DIR . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $tpl->addCss('styles.css');
        $printedResult = $tpl->fetch('template/testAddCss.tpl');
        $this->assertContains('styles.css', $printedResult);
    }
    
    public function testAddJs()
    {
        $config = new Config(DATA_DIR . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
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

        $config = new Config(DATA_DIR . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $printedResult = $tpl->fetch('home/home.tpl');
        $this->assertEquals($expectedTemplate, $printedResult);
    }*/
}