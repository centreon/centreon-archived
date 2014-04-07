<?php
/**
 * Smarty PHPunit tests compilation of {php} and <?php...?> tag
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for {php} and <?php...?> tag tests
 */
class CompilePhpTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smartyBC->disableSecurity();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
     * test <?php...\> tag
     * default is PASSTHRU
     */
    public function testPhpTag()
    {
        $tpl = $this->smartyBC->createTemplate("eval:<?php echo 'hello world'; ?>");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("<?php echo 'hello world'; ?>", $content);
    }
    // ALLOW
    public function testPhpTagAllow()
    {
        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:<?php echo 'hello world'; ?>");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('hello world', $content);
    }
    /**
     * test <?=...\> shorttag
     * default is PASSTHRU
     */
    public function testShortTag()
    {
        $this->smartyBC->assign('foo', 'bar');
        $content = $this->smartyBC->fetch('eval:<?=$foo?>');
        $this->assertEquals('<?=$foo?>', $content);
    }

    public function testEndTagInStrings1()
    {
        $str = <<< STR
<?php
\$a = Array("?>" => 3 );
\$b = Array("?>" => "?>");
\$c = Array("a" => Array("b" => 7));
class d_class
{
  public \$d_attr = 8;
}
\$d = new d_class();
\$e = Array("f" => \$d);

// '"
# '"

echo '{\$a["?>"]}';
echo "{\$a['?>']}";
echo '{\$a["{\$b["?>"]}"]}';
echo "{\$c['a']['b']}";
echo "a{\$e['f']->d_attr}a"
?>
STR;

        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('{$a["?>"]}3{$a["{$b["?>"]}"]}7a8a', $content);
    }

    public function testEndTagInStrings2()
    {
        $str = <<< STR
<?php
\$a = Array("?>" => 3 );
\$b = Array("?>" => "?>");

echo "{\$a["?>"]}";
echo "{\$a["{\$b["?>"]}"]}";
?>
STR;

        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('33', $content);
    }

    public function testEndTagInStrings3()
    {
        $str = <<< STR
<?php
echo 'a?>a';
echo '?>\\\\';
echo '\\\\\\'?>a';
echo '/*'; // */
echo 1+1;
?>
STR;

        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('a?>a?>\\\\\'?>a/*2', $content);
    }

    public function testEndTagInStrings4()
    {
        $str = <<< STR
<?php
echo "a?>a";
echo "?>\\\\";
echo "\\"?>";
echo "\\\\\\"?>a";
echo "/*";
echo 1+1;
?>
STR;

        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('a?>a?>\\"?>\\"?>a/*2', $content);
    }

    public function testEndTagInHEREDOC()
    {
        $str = <<< STR
<?php
echo <<< LALA
  LALA
 ?>

 "! ?> /*
 LALA
LALA ;
LALA;1+1;
LALA;
echo <<<LALA2
LALA2;1+1;
LALA2
;
?>
STR;
        // " Fix emacs highlighting which chokes on preceding open quote
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("  LALA\n ?>\n\n \"! ?> /*\n LALA\nLALA ;\nLALA;1+1;LALA2;1+1;", str_replace("\r", '', $content));
    }

    public function testEmbeddingsInHEREDOC1()
    {
        $str = <<< STR
<?php
\$a = Array("EOT?>'" => 1);

echo <<< EOT
{\$a["EOT?>'"]}
EOT;
?>
STR;
        // ' Fix emacs highlighting which chokes on preceding open quote
        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("1", $content);
    }

    public function testEmbeddingsInHEREDOC2()
    {
        $str = <<< STR
<?php
\$a = Array("\nEOT\n?>'" => 1);

echo <<< EOT
{\$a[<<<EOT2

EOT
?>'
EOT2
]}
EOT
;
?>
STR;
        // ' Fix emacs highlighting which chokes on preceding open quote
        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        /* Disabled due to bug in PHP easiest illustrated by:
       http://bugs.php.net/bug.php?id=50654

<?php
$a = Array("b" => 1);

echo <<<ZZ
{$a[<<<B
b
B
]}
ZZ;
?>
        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->security = false;
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("11", $content);
*/
    }

    public function testEmbeddedHEREDOC()
    {
        $str = <<< STR
<?php
\$a = Array("4\"" => 3);
\$b = Array("aa\"?>" => 4);

echo "{\$a[<<<EOT
{\$b["aa\"?>"]}"
EOT
  ]}";
?>
STR;
        // " Fix emacs highlighting which chokes on preceding open quote
        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("3", $content);
    }

    public function testEmbeddedNOWDOC()
    {
        $str = <<< STR
<?php
\$a = Array("aa\"?>" => 3);

echo "{\$a[<<<'EOT'
aa"?>
EOT
  ]}";
?>
STR;
        // " Fix emacs highlighting which chokes on preceding open quote
        $this->smartyBC->left_delimiter = '{{';
        $this->smartyBC->right_delimiter = '}}';
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            return;
        }
        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("3", $content);
    }

    public function testEndTagInNOWDOC()
    {
        $str = <<< STR
<?php
echo <<< 'LALA'
aa ?> bb
LALA;
echo <<<'LALA2'
LALA2;1+1;?>
LALA2
;
?>
STR;

        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            return;
        }
        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("aa ?> bbLALA2;1+1;?>", $content);
    }

    public function testNewlineHEREDOC()
    {
        $sprintf_str = "<?php echo <<<STR%sa%1\$sSTR;%1\$s?>";
        foreach (Array("\n", "\r\n") as $newline_chars) {
            $str = sprintf($sprintf_str, $newline_chars);

            $this->smartyBC->php_handling = Smarty::PHP_PASSTHRU;
            $this->smartyBC->enableSecurity();
            $tpl = $this->smartyBC->createTemplate("eval:$str");
            $content = $this->smartyBC->fetch($tpl);
            // For some reason $content doesn't preserve newline format. Not a big problem, I think.
            $this->assertEquals(preg_replace("/\r\n/", "\n", $str),
                preg_replace("/\r\n/", "\n", $content)
                );

            $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
            $this->smartyBC->disableSecurity();
            $tpl = $this->smartyBC->createTemplate("eval:$str");
            $content = $this->smartyBC->fetch($tpl);
            $this->assertEquals("a", $content);
        }
    }

    public function testNewlineNOWDOC()
    {
        $sprintf_str = "<?php echo <<<'STR'%sa%1\$sSTR;%1\$s?>";
        foreach (Array("\n", "\r\n") as $newline_chars) {
            $str = sprintf($sprintf_str, $newline_chars);

            $this->smartyBC->php_handling = Smarty::PHP_PASSTHRU;
            $this->smartyBC->enableSecurity();
            $tpl = $this->smartyBC->createTemplate("eval:$str");
            $content = $this->smartyBC->fetch($tpl);
            // For some reason $content doesn't preserve newline format. Not a big problem, I think.
            $this->assertEquals(preg_replace("/\r\n/", "\n", $str),
                preg_replace("/\r\n/", "\n", $content)
                );

            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
                $this->smartyBC->disableSecurity();
                $tpl = $this->smartyBC->createTemplate("eval:$str");
                $content = $this->smartyBC->fetch($tpl);
                $this->assertEquals("a", $content);
            }
        }
    }

    public function testEndTagInComment()
    {
        $str = <<< STR
<?php

/*
d?>dd "' <<< EOT
*/
echo 1+1;
?>
STR;

        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals(str_replace("\r", '', $str), str_replace("\r", '', $content));

        $this->smartyBC->php_handling = Smarty::PHP_ALLOW;
        $this->smartyBC->disableSecurity();
        $tpl = $this->smartyBC->createTemplate("eval:$str");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals('2', $content);
    }
}
