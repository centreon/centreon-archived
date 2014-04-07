<?php
/**
* Smarty PHPunit tests for File resources
*
* @package PHPunit
* @author Uwe Tews
*/

require_once dirname(__FILE__) . '/PHPunitplugins/resource.ambiguous.php';

/**
* class for file resource tests
*/
class CustomResourceAmbiguousTests extends PHPUnit_Framework_TestCase
{
    public $_resource = null;

    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();

        // empty the template dir
        $this->smarty->setTemplateDir(array());

        // kill cache for unit test
        Smarty_Resource::$resources = array();
        $this->smarty->_resource_handlers = array();
    }

    static function isRunnable()
    {
        return true;
    }

    protected function relative($path)
    {
        $path = str_replace( dirname(__FILE__), '.', $path );
        if (DS == "\\") {
            $path = str_replace( "\\", "/", $path );
        }

        return $path;
    }

    public function testNone()
    {
        $resource_handler = new Smarty_Resource_Ambiguous(dirname(__FILE__) . '/templates/ambiguous/');
        $this->smarty->registerResource('ambiguous', $resource_handler);
        $this->smarty->default_resource_type = 'ambiguous';
        $this->smarty->allow_ambiguous_resources = true;

        $tpl = $this->smarty->createTemplate('foobar.tpl');
        $this->assertFalse($tpl->source->exists);
    }

    public function testCase1()
    {
        $resource_handler = new Smarty_Resource_Ambiguous(dirname(__FILE__) . '/templates/ambiguous/');
        $this->smarty->registerResource('ambiguous', $resource_handler);
        $this->smarty->default_resource_type = 'ambiguous';
        $this->smarty->allow_ambiguous_resources = true;

        $resource_handler->setSegment('case1');

        $tpl = $this->smarty->createTemplate('foobar.tpl');
        $this->assertTrue($tpl->source->exists);
        $this->assertEquals('case1', $tpl->source->content);
    }

    public function testCase2()
    {
        $resource_handler = new Smarty_Resource_Ambiguous(dirname(__FILE__) . '/templates/ambiguous/');
        $this->smarty->registerResource('ambiguous', $resource_handler);
        $this->smarty->default_resource_type = 'ambiguous';
        $this->smarty->allow_ambiguous_resources = true;

        $resource_handler->setSegment('case2');

        $tpl = $this->smarty->createTemplate('foobar.tpl');
        $this->assertTrue($tpl->source->exists);
        $this->assertEquals('case2', $tpl->source->content);
    }

    public function testCaseSwitching()
    {
        $resource_handler = new Smarty_Resource_Ambiguous(dirname(__FILE__) . '/templates/ambiguous/');
        $this->smarty->registerResource('ambiguous', $resource_handler);
        $this->smarty->default_resource_type = 'ambiguous';
        $this->smarty->allow_ambiguous_resources = true;

        $resource_handler->setSegment('case1');
        $tpl = $this->smarty->createTemplate('foobar.tpl');
        $this->assertTrue($tpl->source->exists);
        $this->assertEquals('case1', $tpl->source->content);

        $resource_handler->setSegment('case2');
        $tpl = $this->smarty->createTemplate('foobar.tpl');
        $this->assertTrue($tpl->source->exists);
        $this->assertEquals('case2', $tpl->source->content);
    }

    /**
    * final cleanup
    */
    public function testFinalCleanup()
    {
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
        $this->smarty->allow_ambiguous_resources = false;
    }
}
