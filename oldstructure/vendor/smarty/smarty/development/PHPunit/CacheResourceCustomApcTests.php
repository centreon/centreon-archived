<?php
/**
* Smarty PHPunit tests for cache resource file
*
* @package PHPunit
* @author Uwe Tews
*/

require_once( dirname(__FILE__) . "/CacheResourceCustomMemcacheTests.php" );

/**
* class for cache resource file tests
*/
class CacheResourceCustomApcTests extends CacheResourceCustomMemcacheTests
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->caching_type = 'apctest';
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
    }

    static function isRunnable()
    {
        return function_exists('apc_cache_info') && ini_get('apc.enable_cli');
    }
}
