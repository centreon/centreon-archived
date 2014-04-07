<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

/**
* class for modifier tests
*/
class PluginModifierUnescapeTests extends PHPUnit_Framework_TestCase
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

    public function testHtml()
    {
        $encoded = "a&#228;&#1047;&#1076;&#1088;&#1072;&gt;&lt;&amp;amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $result = "a&#228;&#1047;&#1076;&#1088;&#1072;><&amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|unescape:"html"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testHtmlWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $encoded = "a&#228;&#1047;&#1076;&#1088;&#1072;&gt;&lt;&amp;amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $result = "a&#228;&#1047;&#1076;&#1088;&#1072;><&amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|unescape:"html"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testHtmlall()
    {
        $encoded = "a&#228;&#1047;&#1076;&#1088;&#1072;&gt;&lt;&amp;amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $result = "aäЗдра><&amp;ääв�?�?твуйте";
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|unescape:"htmlall"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testHtmlallWithoutMbstring()
    {
        Smarty::$_MBSTRING = false;
        $encoded = "a&#228;&#1047;&#1076;&#1088;&#1072;&gt;&lt;&amp;amp;&auml;&#228;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $result = "aäЗдра><&amp;ääв�?�?твуйте";
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|unescape:"htmlall"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        Smarty::$_MBSTRING = true;
    }

    public function testUrl()
    {
        $encoded = "a%C3%A4%D0%97%D0%B4%D1%80%D0%B0%3E%3C%26amp%3B%C3%A4%C3%A4%D0%B2%D1%81%D1%81%D1%82%D0%B2%3F%3D%2B%D1%83%D0%B9%D1%82%D0%B5";
        $result = "aäЗдра><&amp;ääв�?�?тв?=+уйте";
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|unescape:"url"}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }
}
