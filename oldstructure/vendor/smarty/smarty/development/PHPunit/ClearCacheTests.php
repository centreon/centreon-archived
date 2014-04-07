<?php
/**
* Smarty PHPunit tests for clearing the cache
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for clearing the cache tests
*/
class ClearCacheTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test cache->clear_all method
    */
    public function testClearCacheAll()
    {
        $this->smarty->clearAllCache();
        file_put_contents($this->smarty->getCacheDir() . 'dummy.php', 'test');
        $this->assertEquals(1, $this->smarty->clearAllCache());
    }
    /**
    * test cache->clear_all method not expired
    */
    public function testClearCacheAllNotExpired()
    {
        file_put_contents($this->smarty->getCacheDir() . 'dummy.php', 'test');
        touch($this->smarty->getCacheDir() . 'dummy.php', time()-1000);
        $this->assertEquals(0, $this->smarty->clearAllCache(2000));
    }
    public function testSmarty2ClearCacheAllNotExpired()
    {
        file_put_contents($this->smartyBC->getCacheDir() . 'dummy.php', 'test');
        touch($this->smartyBC->getCacheDir() . 'dummy.php', time()-1000);
        $this->smartyBC->clear_all_cache(2000);
        $this->assertEquals(1, $this->smartyBC->clearAllCache());
    }
    /**
    * test cache->clear_all method expired
    */
    public function testClearCacheAllExpired()
    {
        file_put_contents($this->smarty->getCacheDir() . 'dummy.php', 'test');
        touch($this->smarty->getCacheDir() . 'dummy.php', time()-1000);
        $this->assertEquals(1, $this->smarty->clearAllCache(500));
    }
    public function testSmarty2ClearCacheAllExpired()
    {
        file_put_contents($this->smartyBC->getCacheDir() . 'dummy.php', 'test');
        touch($this->smartyBC->getCacheDir() . 'dummy.php', time()-1000);
        $this->smartyBC->clear_all_cache(500);
        $this->assertEquals(0, $this->smartyBC->clearAllCache());
    }
}
