<?php
/**
 * Smarty PHPunit tests  of the <?xml...> tag handling
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for <?xml...> tests
 */
class XmlTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->force_compile = true;
    }

    static function isRunnable()
    {
        return true;
    }

    /**
     * test standard xml
     */
    public function testXml()
    {
        $tpl = $this->smarty->createTemplate('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $this->smarty->fetch($tpl));
    }
    /**
     * test standard xml Smarty::PHP_QUOTE
     */
    public function testXmlPhpQuote()
    {
        $this->smarty->security_policy->php_handling = Smarty::PHP_QUOTE;
        $tpl = $this->smarty->createTemplate('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $this->smarty->fetch($tpl));
    }
    /**
     * test standard xml Smarty::PHP_ALLOW
     */
    public function testXmlPhpAllow()
    {
        $this->smarty->security_policy->php_handling = Smarty::PHP_ALLOW;
        $tpl = $this->smarty->createTemplate('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $this->smarty->fetch($tpl));
    }
    /**
     * test standard xml
     */
    public function testXmlCaching()
    {
        $this->smarty->security_policy->php_handling = Smarty::PHP_PASSTHRU;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $content = $this->smarty->fetch('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $content);
    }
    /*
    * test standard xml
    */
    public function testXmlCachingPhpQuote()
    {
        $this->smarty->security_policy->php_handling = Smarty::PHP_QUOTE;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $content = $this->smarty->fetch('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $content);
    }

    /*
    * test standard xml
    */
    public function testXmlCachingPhpAllow()
    {
        $this->smarty->security_policy->php_handling = Smarty::PHP_ALLOW;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $content = $this->smarty->fetch('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $content);
    }
}
