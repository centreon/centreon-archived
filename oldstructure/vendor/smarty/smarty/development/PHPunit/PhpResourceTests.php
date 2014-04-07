<?php
/**
* Smarty PHPunit tests for PHP resources
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for PHP resource tests
*/
class PhpResourceTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
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

    /**
    * test getTemplateFilepath
    */
    public function testGetTemplateFilepath()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertEquals('./templates/phphelloworld.php', str_replace('\\', '/', $tpl->source->filepath));
    }
    /**
    * test getTemplateTimestamp
    */
    public function testGetTemplateTimestamp()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }
    /**
    * test getTemplateSource
    *-/
    public function testGetTemplateSource()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $tpl->source->content);
    }
    /**
    * test usesCompiler
    */
    public function testUsesCompiler()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($tpl->source->uncompiled);
    }
    /**
    * test isEvaluated
    */
    public function testIsEvaluated()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->source->recompiled);
    }
    /**
    * test getCompiledFilepath
    */
    public function testGetCompiledFilepath()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->compiled->filepath);
    }
    /**
    * test getCompiledTimestamp
    */
    public function testGetCompiledTimestamp()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->compiled->timestamp);
    }
    /**
    * test mustCompile
    */
    public function testMustCompile()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->mustCompile());
    }
    /**
    * test getCachedFilepath
    */
    public function testGetCachedFilepath()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $expected = './cache/'.sha1($this->smarty->getTemplateDir(0) . 'phphelloworld.php').'.phphelloworld.php.php';
        $this->assertEquals($expected, $this->relative($tpl->cached->filepath));
    }
    /**
    * test create cache file used by the following tests
    */
    public function testCreateCacheFile()
    {
        // create dummy cache file
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $this->smarty->fetch($tpl));
    }
    /**
    * test getCachedTimestamp caching enabled
    */
    public function testGetCachedTimestamp()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue(is_integer($tpl->cached->timestamp));
        $this->assertEquals(10, strlen($tpl->cached->timestamp));
    }
    /**
    * test isCached
    */
    public function testIsCached()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($tpl->isCached());
    }
    /**
    * test isCached caching disabled
    */
    public function testIsCachedCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->isCached());
    }
    /**
    * test isCached on touched source
    */
    public function testIsCachedTouchedSourcePrepare()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        sleep(1);
        touch ($tpl->source->filepath);
    }
    public function testIsCachedTouchedSource()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->isCached());
    }
    /**
    * test is cache file is written
    */
    public function testWriteCachedContent()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->smarty->fetch($tpl);
        $this->assertTrue(file_exists($tpl->cached->filepath));
    }
    /**
    * test getRenderedTemplate
    */
    public function testGetRenderedTemplate()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $tpl->fetch());
    }
    /**
    * test $smarty->is_cached
    */
    public function testSmartyIsCachedPrepare()
    {
        $this->smarty->allow_php_templates = true;
        // prepare files for next test
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        // clean up for next tests
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->smarty->fetch($tpl);
    }
    public function testSmartyIsCached()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($this->smarty->isCached($tpl));
    }
    /**
    * test $smarty->is_cached  caching disabled
    */
    public function testSmartyIsCachedCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($this->smarty->isCached($tpl));
    }

    public function testGetTemplateFilepathName()
    {
        $this->smarty->addTemplateDir('./templates_2', 'foo');
        $tpl = $this->smarty->createTemplate('php:[foo]helloworld.php');
        $this->assertEquals('./templates_2/helloworld.php', $this->relative($tpl->source->filepath));
    }

    public function testGetCachedFilepathName()
    {
        $this->smarty->addTemplateDir('./templates_2', 'foo');
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:[foo]helloworld.php');
        $expected = './cache/'.sha1($this->smarty->getTemplateDir('foo') .'helloworld.php').'.helloworld.php.php';
        $this->assertEquals($expected, $this->relative($tpl->cached->filepath));
    }

    /**
    * final cleanup
    */
    public function testFinalCleanup()
    {
        $this->smarty->clearAllCache();
    }
}
