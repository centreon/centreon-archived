<?php
/**
* Smarty PHPunit tests of config  variables
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for config variable tests
*/
class ConfigVarTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
        SmartyTests::init();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test config varibales loading all sections
    */
    public function testConfigNumber()
    {
        $this->smarty->configLoad('test.conf');
        $this->assertEquals("123.4", $this->smarty->fetch('eval:{#Number#}'));
    }
    public function testConfigText()
    {
        $this->smarty->configLoad('test.conf');
        $this->assertEquals("123bvc", $this->smarty->fetch('eval:{#text#}'));
    }
    public function testConfigLine()
    {
        $this->smarty->configLoad('test.conf');
        $this->assertEquals("123 This is a line", $this->smarty->fetch('eval:{#line#}'));
    }
    public function testConfigVariableGlobalSections()
    {
        $this->smarty->configLoad('test.conf');
        $this->assertEquals("Welcome to Smarty! Global Section1 Global Section2", $this->smarty->fetch('eval:{#title#} {#sec1#} {#sec2#}'));
    }
    /**
    * test config variables loading section2
    */
    public function testConfigVariableSection2()
    {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smarty->configLoad('test.conf', 'section2');
        $this->assertEquals("Welcome to Smarty! Global Section1 Hello Section2", $this->smarty->fetch('eval:{#title#} {#sec1#} {#sec2#}'));
    }
    /**
    * test config variables loading section special char
    */
    public function testConfigVariableSectionSpecialChar()
    {
        $this->smarty->configLoad('test.conf', '/');
        $this->assertEquals("Welcome to Smarty! special char", $this->smarty->fetch('eval:{#title#} {#sec#}'));
    }
    /**
    * test config variables loading section foo/bar
    */
    public function testConfigVariableSectionFooBar()
    {
        $this->smarty->configLoad('test.conf', 'foo/bar');
        $this->assertEquals("Welcome to Smarty! section foo/bar", $this->smarty->fetch('eval:{#title#} {#sec#}'));
    }
    /**
    * test config variables loading indifferent scopes
    */
    public function testConfigVariableScope()
    {
        $this->smarty->configLoad('test.conf', 'section2');
        $tpl = $this->smarty->createTemplate('eval:{#title#} {#sec1#} {#sec2#}');
        $tpl->configLoad('test.conf', 'section1');
        $this->assertEquals("Welcome to Smarty! Global Section1 Hello Section2", $this->smarty->fetch('eval:{#title#} {#sec1#} {#sec2#}'));
        $this->assertEquals("Welcome to Smarty! Hello Section1 Global Section2", $this->smarty->fetch($tpl));
    }
    /**
    * test config variables loading section2 from template
    */
    public function testConfigVariableSection2Template()
    {
        $this->assertEquals("Welcome to Smarty! Global Section1 Hello Section2", $this->smarty->fetch('eval:{config_load file=\'test.conf\' section=\'section2\'}{#title#} {#sec1#} {#sec2#}'));
    }
    public function testConfigVariableSection2TemplateShorttags()
    {
       $this->assertEquals("Welcome to Smarty! Global Section1 Hello Section2", $this->smarty->fetch('eval:{config_load \'test.conf\' \'section2\'}{#title#} {#sec1#} {#sec2#}'));
    }
    /**
    * test config varibales loading local
    */
    public function testConfigVariableLocal()
    {
        $this->assertEquals("Welcome to Smarty!", $this->smarty->fetch('eval:{config_load file=\'test.conf\' scope=\'local\'}{#title#}'));
        // global must be empty
        $this->assertEquals("", $this->smarty->getConfigVars('title'));
    }
    /**
    * test config varibales loading parent
    */
    public function testConfigVariableParent()
    {
        $this->assertEquals("Welcome to Smarty!", $this->smarty->fetch('eval:{config_load file=\'test.conf\' scope=\'parent\'}{#title#}'));
        // global is parent must not be empty
        $this->assertEquals("Welcome to Smarty!", $this->smarty->getConfigVars('title'));
    }
    /**
    * test config varibales loading global
    */
    public function testConfigVariableGlobal()
    {
        $this->assertEquals("Welcome to Smarty!", $this->smarty->fetch('eval:{config_load file=\'test.conf\' scope=\'global\'}{#title#}'));
        // global is parent must not be empty
        $this->assertEquals("Welcome to Smarty!", $this->smarty->getConfigVars('title'));
    }
    /**
    * test config variables of hidden sections
    * shall display variables from hidden section
    */
    public function testConfigVariableHidden()
    {
        $this->smarty->config_read_hidden = true;
        $this->smarty->configLoad('test.conf','hidden');
        $this->assertEquals("Welcome to Smarty!Hidden Section", $this->smarty->fetch('eval:{#title#}{#hiddentext#}'));
    }
    /**
    * test config variables of disabled hidden sections
    * shall display not variables from hidden section
    */
    public function testConfigVariableHiddenDisable()
    {
        $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $this->smarty->config_read_hidden = false;
        $this->smarty->configLoad('test.conf','hidden');
        $this->assertEquals("Welcome to Smarty!", $this->smarty->fetch('eval:{#title#}{#hiddentext#}'));
    }
    /**
    * test config varibales loading all sections from template
    */
    public function testConfigVariableAllSectionsTemplate()
    {
        $this->smarty->config_overwrite = true;
        $this->assertEquals("Welcome to Smarty! Global Section1 Global Section2", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{#title#} {#sec1#} {#sec2#}'));
    }
    /**
    * test config varibales overwrite
    */
    public function testConfigVariableOverwrite()
    {
        $this->assertEquals("Overwrite2", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{#overwrite#}'));
    }
    public function testConfigVariableOverwrite2()
    {
        $this->assertEquals("Overwrite3", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{config_load file=\'test2.conf\'}{#overwrite#}'));
    }
    /**
    * test config varibales overwrite false
    */
    public function testConfigVariableOverwriteFalse()
    {
        $this->smarty->config_overwrite = false;
        $this->assertEquals("Overwrite1Overwrite2Overwrite3Welcome to Smarty! Global Section1 Global Section2", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{config_load file=\'test2.conf\'}{foreach #overwrite# as $over}{$over}{/foreach}{#title#} {#sec1#} {#sec2#}'));
    }
    /**
    * test config varibales array
    */
    public function testConfigVariableArray1()
    {
        $this->smarty->config_overwrite = false;
        $this->smarty->assign('foo',1);
        $this->assertEquals("Overwrite2", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{config_load file=\'test2.conf\'}{$smarty.config.overwrite[$foo]}'));
    }
    public function testConfigVariableArray2()
    {
        $this->smarty->config_overwrite = false;
        $this->smarty->assign('foo',2);
        $this->assertEquals("Overwrite3", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{config_load file=\'test2.conf\'}{#overwrite#.$foo}'));
    }
    /**
    * test config varibales booleanize on
    */
    public function testConfigVariableBooleanizeOn()
    {
        $this->smarty->config_booleanize = true;
        $this->assertEquals("passed", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{if #booleanon# === true}passed{/if}'));
    }
    /**
    * test config varibales booleanize off
    */
    public function testConfigVariableBooleanizeOff()
    {
        $this->smarty->config_booleanize = false;
        $this->assertEquals("passed", $this->smarty->fetch('eval:{config_load file=\'test.conf\'}{if #booleanon# == \'on\'}passed{/if}'));
    }
    /**
    * test config file syntax error
    */
    public function testConfigSyntaxError()
    {
        try {
            $this->smarty->fetch('eval:{config_load file=\'test_error.conf\'}');
        } catch (Exception $e) {
            $this->assertContains('Syntax error in config file', $e->getMessage());

            return;
        }
        $this->fail('Exception for syntax errors in config files has not been raised.');
    }
    /**
    * test getConfigVars
    */
    public function testConfigGetSingleConfigVar()
    {
        $this->smarty->configLoad('test.conf');
        $this->assertEquals("Welcome to Smarty!", $this->smarty->getConfigVars('title'));
    }
    /**
    * test getConfigVars return all variables
    */
    public function testConfigGetAllConfigVars()
    {
        $this->smarty->configLoad('test.conf');
        $vars = $this->smarty->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertEquals("Welcome to Smarty!", $vars['title']);
        $this->assertEquals("Global Section1", $vars['sec1']);
    }
    /**
    * test clearConfig for single variable
    */
    public function testConfigClearSingleConfigVar()
    {
        $this->smarty->configLoad('test.conf');
        $this->smarty->clearConfig('title');
        $this->assertEquals("", $this->smarty->getConfigVars('title'));
    }
    /**
    * test clearConfig for all variables
    */
    public function testConfigClearConfigAll()
    {
        $this->smarty->configLoad('test.conf');
        $this->smarty->clearConfig();
        $vars = $this->smarty->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertTrue(empty($vars));
    }
    /**
    * test config vars on data object
    */
    public function testConfigTextData()
    {
        $data = $this->smarty->createData();
        $data->configLoad('test.conf');
        $this->assertEquals("123bvc", $this->smarty->fetch('eval:{#text#}', $data));
    }
    /**
    * test getConfigVars on data object
    */
    public function testConfigGetSingleConfigVarData()
    {
        $data = $this->smarty->createData();
        $data->configLoad('test.conf');
        $this->assertEquals("Welcome to Smarty!", $data->getConfigVars('title'));
    }
    /**
    * test getConfigVars return all variables on data object
    */
    public function testConfigGetAllConfigVarsData()
    {
        $data = $this->smarty->createData();
        $data->configLoad('test.conf');
        $vars = $data->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertEquals("Welcome to Smarty!", $vars['title']);
        $this->assertEquals("Global Section1", $vars['sec1']);
    }
    /**
    * test clearConfig for single variable on data object
    */
    public function testConfigClearSingleConfigVarData()
    {
        $data = $this->smarty->createData();
        $data->configLoad('test.conf');
        $data->clearConfig('title');
        $this->assertEquals("", $data->getConfigVars('title'));
        $this->assertEquals("Global Section1", $data->getConfigVars('sec1'));
    }
    /**
    * test clearConfig for all variables on data object
    */
    public function testConfigClearConfigAllData()
    {
        $data = $this->smarty->createData();
        $data->configLoad('test.conf');
        $data->clearConfig();
        $vars = $data->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertTrue(empty($vars));
    }
    /**
    * test config vars on template object
    */
    public function testConfigTextTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:{#text#}');
        $tpl->configLoad('test.conf');
        $this->assertEquals("123bvc", $this->smarty->fetch($tpl));
    }
    /**
    * test getConfigVars on template object
    */
    public function testConfigGetSingleConfigVarTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:{#text#}');
        $tpl->configLoad('test.conf');
        $this->assertEquals("Welcome to Smarty!", $tpl->getConfigVars('title'));
    }
    /**
    * test getConfigVars return all variables on template object
    */
    public function testConfigGetAllConfigVarsTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:{#text#}');
        $tpl->configLoad('test.conf');
        $vars = $tpl->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertEquals("Welcome to Smarty!", $vars['title']);
        $this->assertEquals("Global Section1", $vars['sec1']);
    }
    /**
    * test clearConfig for single variable on template object
    */
    public function testConfigClearSingleConfigVarTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:{#text#}');
        $tpl->configLoad('test.conf');
        $tpl->clearConfig('title');
        $this->assertEquals("", $tpl->getConfigVars('title'));
        $this->assertEquals("Global Section1", $tpl->getConfigVars('sec1'));
    }
    /**
    * test clearConfig for all variables on template object
    */
    public function testConfigClearConfigAllTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:{#text#}');
        $tpl->configLoad('test.conf');
        $tpl->clearConfig();
        $vars = $tpl->getConfigVars();
        $this->assertTrue(is_array($vars));
        $this->assertTrue(empty($vars));
    }

    /**
    * test config varibales loading from absolute file path
    */
    public function testConfigAbsolutePath()
    {
        $file = realpath($this->smarty->getConfigDir(0) . 'test.conf');
        $this->smarty->configLoad($file);
        $this->assertEquals("123.4", $this->smarty->fetch('eval:{#Number#}'));
    }
}
