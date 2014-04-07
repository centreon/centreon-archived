<?php
/**
* Smarty PHPunit tests for File resources
*
* @package PHPunit
* @author Rodney Rehm
*/

class IndexedFileResourceTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->addTemplateDir(dirname(__FILE__) .'/templates_2');
        // note that 10 is a string!
        $this->smarty->addTemplateDir(dirname(__FILE__) .'/templates_3', '10');
        $this->smarty->addTemplateDir(dirname(__FILE__) .'/templates_4', 'foo');
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

    public function testGetTemplateFilepath()
    {
        $tpl = $this->smarty->createTemplate('dirname.tpl');
        $this->assertEquals('./templates/dirname.tpl', $this->relative($tpl->source->filepath));
    }
    public function testGetTemplateFilepathNumber()
    {
        $tpl = $this->smarty->createTemplate('[1]dirname.tpl');
        $this->assertEquals('./templates_2/dirname.tpl', $this->relative($tpl->source->filepath));
    }
    public function testGetTemplateFilepathNumeric()
    {
        $tpl = $this->smarty->createTemplate('[10]dirname.tpl');
        $this->assertEquals('./templates_3/dirname.tpl', $this->relative($tpl->source->filepath));
    }
    public function testGetTemplateFilepathName()
    {
        $tpl = $this->smarty->createTemplate('[foo]dirname.tpl');
        $this->assertEquals('./templates_4/dirname.tpl', $this->relative($tpl->source->filepath));
    }

    public function testFetch()
    {
        $tpl = $this->smarty->createTemplate('dirname.tpl');
        $this->assertEquals('templates', $this->smarty->fetch($tpl));
    }
    public function testFetchNumber()
    {
        $tpl = $this->smarty->createTemplate('[1]dirname.tpl');
        $this->assertEquals('templates_2', $this->smarty->fetch($tpl));
    }
    public function testFetchNumeric()
    {
        $tpl = $this->smarty->createTemplate('[10]dirname.tpl');
        $this->assertEquals('templates_3', $this->smarty->fetch($tpl));
    }
    public function testFetchName()
    {
        $tpl = $this->smarty->createTemplate('[foo]dirname.tpl');
        $this->assertEquals('templates_4', $this->smarty->fetch($tpl));
    }

    public function testGetCompiledFilepath()
    {
        $tpl = $this->smarty->createTemplate('[foo]dirname.tpl');
        $expected = './templates_c/'.sha1($this->smarty->getTemplateDir('foo').'dirname.tpl').'.file.dirname.tpl.php';
        $this->assertEquals($expected, $this->relative($tpl->compiled->filepath));
    }

    public function testGetCachedFilepath()
    {
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('[foo]dirname.tpl');
        $expected = './cache/'.sha1($this->smarty->getTemplateDir('foo').'dirname.tpl').'.dirname.tpl.php';
        $this->assertEquals($expected, $this->relative($tpl->cached->filepath));
    }

    public function testFinalCleanup()
    {
        $this->smarty->clearCompiledTemplate();
      $this->smarty->clearAllCache();
    }
}
