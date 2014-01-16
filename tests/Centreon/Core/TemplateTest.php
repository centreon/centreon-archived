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
        $printedResult = $tpl->fetch('home/home.tpl');
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
        $printedResult = $tpl->fetch('home/home.tpl');
        $this->assertContains('jquery.min.js', $printedResult);
        $this->assertContains('bootstrap.min.js', $printedResult);
    }
    
    public function testTmpl()
    {
        
    }
}