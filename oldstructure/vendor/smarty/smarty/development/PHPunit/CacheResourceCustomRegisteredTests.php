<?php
/**
* Smarty PHPunit tests for cache resource file
*
* @package PHPunit
* @author Uwe Tews
*/

require_once( dirname(__FILE__) . "/CacheResourceCustomMysqlTests.php" );

/**
* class for cache resource file tests
*/
class CacheResourceCustomRegisteredTests extends CacheResourceCustomMysqlTests
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();

        if (!class_exists('Smarty_CacheResource_Mysqltest', false)) {
            require_once( dirname(__FILE__) . "/PHPunitplugins/CacheResource.Mysqltest.php" );
        }
        $this->smarty->caching_type = 'foobar';
        $this->smarty->registerCacheResource('foobar', new Smarty_CacheResource_Mysqltest());
    }
}
