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
class CacheResourceCustomMemcacheTests extends CacheResourceCustomMysqlTests
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->caching_type = 'memcachetest';
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
    }

    static function isRunnable()
    {
        return class_exists('Memcache');
    }

    protected function doClearCacheAssertion($a, $b)
    {
        $this->assertEquals(-1, $b);
    }

    public function testGetCachedFilepathSubDirs()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $sha1 = $tpl->source->uid . '#helloworld_tpl##';
        $this->assertEquals($sha1, $tpl->cached->filepath);
    }
    /**
    * test getCachedFilepath with cache_id
    */
    public function testGetCachedFilepathCacheId()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl', 'foo|bar');
        $sha1 = $tpl->source->uid . '#helloworld_tpl#foo|bar#';
        $this->assertEquals($sha1, $tpl->cached->filepath);
    }
    /**
    * test getCachedFilepath with compile_id
    */
    public function testGetCachedFilepathCompileId()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl', null, 'blar');
        $sha1 = $tpl->source->uid . '#helloworld_tpl##blar';
        $this->assertEquals($sha1, $tpl->cached->filepath);
    }
    /**
    * test getCachedFilepath with cache_id and compile_id
    */
    public function testGetCachedFilepathCacheIdCompileId()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('helloworld.tpl', 'foo|bar', 'blar');
        $sha1 = $tpl->source->uid . '#helloworld_tpl#foo|bar#blar';
        $this->assertEquals($sha1, $tpl->cached->filepath);
    }
}
