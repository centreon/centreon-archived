<?php
/**
* Smarty PHPunit tests for cache resource file
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for cache resource file tests
*/
class HttpModifiedSinceTests extends PHPUnit_Framework_TestCase
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

    public function testDisabled()
    {
        $_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS'] = true;
        $_SERVER['SMARTY_PHPUNIT_HEADERS'] = array();

        $this->smarty->cache_modified_check = false;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 20;
        ob_start();
        $this->smarty->display('helloworld.tpl');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world', $output);
        $this->assertEquals('', join( "\r\n",$_SERVER['SMARTY_PHPUNIT_HEADERS']));

        unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        unset($_SERVER['SMARTY_PHPUNIT_HEADERS']);
        unset($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS']);
    }

    public function testEnabledUncached()
    {
        $_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS'] = true;
        $_SERVER['SMARTY_PHPUNIT_HEADERS'] = array();

        $this->smarty->cache_modified_check = true;
        $this->smarty->caching = false;
        $this->smarty->cache_lifetime = 20;
        ob_start();
        $this->smarty->display('helloworld.tpl');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world', $output);
        $this->assertEquals('', join( "\r\n",$_SERVER['SMARTY_PHPUNIT_HEADERS']));

        unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        unset($_SERVER['SMARTY_PHPUNIT_HEADERS']);
        unset($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS']);
    }

    public function testEnabledCached()
    {
        $_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS'] = true;
        $_SERVER['SMARTY_PHPUNIT_HEADERS'] = array();

        $this->smarty->cache_modified_check = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 20;

        ob_start();
        $this->smarty->display('helloworld.tpl');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world', $output);
        $header = 'Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT';
        $this->assertEquals($header, join( "\r\n",$_SERVER['SMARTY_PHPUNIT_HEADERS']));

        $_SERVER['SMARTY_PHPUNIT_HEADERS'] = array();
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT';
        ob_start();
        $this->smarty->display('helloworld.tpl');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('hello world', $output);
        $this->assertEquals($header, join( "\r\n",$_SERVER['SMARTY_PHPUNIT_HEADERS']));

        $_SERVER['SMARTY_PHPUNIT_HEADERS'] = array();
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = gmdate('D, d M Y H:i:s', time() + 10) . ' GMT';
        ob_start();
        $this->smarty->display('helloworld.tpl');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('', $output);
        $this->assertEquals('304 Not Modified', join( "\r\n",$_SERVER['SMARTY_PHPUNIT_HEADERS']));

        unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        unset($_SERVER['SMARTY_PHPUNIT_HEADERS']);
        unset($_SERVER['SMARTY_PHPUNIT_DISABLE_HEADERS']);
    }

}
