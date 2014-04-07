<?php
/**
 * Smarty PHPunit tests resource plugins
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for resource plugins tests
 */
class ResourcePluginTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        // reset cache for unit test
        Smarty_Resource::$resources = array();
        SmartyTests::init();
    }

    static function isRunnable()
    {
        return true;
    }
    /**
     * test resource plugin rendering
     */
    public function testResourcePlugin()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->assertEquals('hello world', $this->smarty->fetch('db:test'));
    }
    /**
     * test resource plugin rendering
     */
    public function testResourcePluginObject()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->assertEquals('hello world', $this->smarty->fetch('db2:test'));
    }
    /**
     * test resource plugin rendering of a registered object
     */
    public function testResourcePluginRegisteredInstance()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->smarty->loadPlugin('Smarty_Resource_Db2');
        $this->smarty->registerResource( 'db2a', new Smarty_Resource_Db2() );
        $this->assertEquals('hello world', $this->smarty->fetch('db2a:test'));
    }
    /**
     * test resource plugin rendering of a recompiling resource
     */
    public function testResourcePluginRecompiled()
    {
        return;
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        try {
            $this->assertEquals('hello world', $this->smarty->fetch('db3:test'));
        } catch (Exception $e) {
            $this->assertContains('not return a destination', $e->getMessage());

            return;
        }
        $this->fail('Exception for empty filepath has not been thrown.');
    }
    /**
     * test resource plugin non-existent compiled cache of a recompiling resource
     */
    public function testResourcePluginRecompiledCompiledFilepath()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $tpl = $this->smarty->createTemplate('db2:test.tpl');
        $expected = realpath('./templates_c/'.sha1('db2:test.tpl').'.db2.test.tpl.php');
        $this->assertFalse(!!$expected);
        $this->assertFalse($tpl->compiled->filepath);
    }
    /**
     * test resource plugin rendering of a custom resource
     */
    public function testResourcePluginMysql()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->assertEquals('hello world', $this->smarty->fetch('mysqltest:test.tpl'));
    }
    /**
     * test resource plugin timestamp of a custom resource
     */
    public function testResourcePluginMysqlTimestamp()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $tpl = $this->smarty->createTemplate('mysqltest:test.tpl');
        $this->assertEquals(strtotime("2010-12-25 22:00:00"), $tpl->source->timestamp);
    }
    /**
     * test resource plugin timestamp of a custom resource with only fetch() implemented
     */
    public function testResourcePluginMysqlTimestampWithoutFetchTimestamp()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $tpl = $this->smarty->createTemplate('mysqlstest:test.tpl');
        $this->assertEquals(strtotime("2010-12-25 22:00:00"), $tpl->source->timestamp);
    }
    /**
     * test resource plugin compiledFilepath of a custom resource
     */
    public function testResourcePluginMysqlCompiledFilepath()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $tpl = $this->smarty->createTemplate('mysqltest:test.tpl');
        $expected = realpath('./templates_c/'.sha1('mysqltest:test.tpl').'.mysqltest.test.tpl.php');
        $this->assertTrue(!!$expected);
        $this->assertEquals($expected, realpath($tpl->compiled->filepath));
    }
    public function testResourcePluginMysqlCompiledFilepathCache()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->force_compile = true;
        $this->smarty->fetch('mysqltest:test.tpl');
        $tpl = $this->smarty->createTemplate('mysqltest:test.tpl');
        $expected = realpath('./templates_c/'.sha1('mysqltest:test.tpl').'.mysqltest.test.tpl.cache.php');
        $this->assertTrue(!!$expected);
        $this->assertEquals($expected, realpath($tpl->compiled->filepath));
        $this->smarty->caching = false;
    }
    /**
     * test resource plugin timesatmp
     */
    public function testResourcePluginTimestamp()
    {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $tpl = $this->smarty->createTemplate('db:test');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }

    public function testResourcePluginExtendsall()
    {
        $this->smarty->addPluginsDir( dirname(__FILE__)."/../../distribution/demo/plugins/");
        $this->smarty->setTemplateDir( array(
            'root' => './templates',
            './templates_2',
            './templates_3',
            './templates_4',
        ));

        $expected = "templates\n\ntemplates_3\ntemplates\n\ntemplates_4";
        $this->assertEquals($expected, $this->smarty->fetch('extendsall:extendsall.tpl'));
    }

    public function testResourcePluginExtendsallOne()
    {
        $this->smarty->addPluginsDir( dirname(__FILE__)."/../../distribution/demo/plugins/");
        $this->smarty->setTemplateDir( array(
            'root' => './templates',
            './templates_2',
            './templates_3',
            './templates_4',
        ));

        $expected = "templates\ntemplates";
        $this->assertEquals($expected, $this->smarty->fetch('extendsall:extendsall2.tpl'));
    }

    public function testSharing()
    {
        $smarty = new Smarty();
        $smarty->_resource_handlers = array();
        $_smarty = clone $smarty;
        $smarty->fetch('eval:foo');
        $_smarty->fetch('eval:foo');

        $this->assertTrue($smarty->_resource_handlers['eval'] === $_smarty->_resource_handlers['eval']);
    }

    public function testExplicit()
    {
        $smarty = new Smarty();
        $smarty->_resource_handlers = array();
        $_smarty = clone $smarty;
        $smarty->fetch('eval:foo');
        $_smarty->registerResource('eval', new Smarty_Internal_Resource_Eval());
        $_smarty->fetch('eval:foo');

        $this->assertFalse($smarty->_resource_handlers['eval'] === $_smarty->_resource_handlers['eval']);
    }
}
