<?php
/**
 * Run PHPunit tests without PHPunit for debugging
 *
 * @package PHPunit
 * @author Uwe Tews
 */
include 'smartytestdebug.inc.php';

// place request PHPunit test class here or leave empty for all
$_classes = 'CompileNocacheTests';

// place method name for a singe test here or leave empty for all
$function = array();

// clean up
SmartyTests::$smarty->clearAllCache();
SmartyTests::$smarty->clearCompiledTemplate();

// if no classes have been selected scan PHPunit folder for all
if (empty($_classes)) {
    foreach (new DirectoryIterator(dirname(__FILE__)) as $file) {
        if (!$file->isDot() && !$file->isDir() && !in_array((string) $file, array('smartytests.php', 'smartytestssingle.php', 'smartytestsfile.php', 'smartytestdebug.php', 'smartytestdebug.inc.php')) && substr((string) $file, -4) === '.php') {
            $class = basename($file, '.php');
                 require_once $file->getPathname();
                // to have an optional test suite, it should implement a public static function isRunnable
                // that returns true only if all the conditions are met to run it successfully, for example
                // it can check that an external library is present
                if (!method_exists($class, 'isRunnable') || call_user_func(array($class, 'isRunnable'))) {
                    $_classes[] = $class;
            }
        }
    }
    sort($_classes);

} else {
    require_once $_classes . '.php';
}

// for all selected test classes
foreach ((array) $_classes as $class) {
    if (empty($function)) {
        $c = new ReflectionClass('PHPUnit_Framework_TestCase');
        $m1 = $c->getMethods();
        foreach ($m1 as $m) {
            $remove[] = $m->name;
        }
        $remove[] = 'setUp';
        $remove[] = 'isRunnable';

        $c = new ReflectionClass($class);
        $methods = $c->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->name, 'test') === 0) {
                $function[] = $method->name;
            }
        }
        $function = array_diff($function, $remove);
    }

    // first run of test to collect failures
    $o = new $class;
    foreach ($function as $func) {
        try {
            $o->current_function = $func;
            $o->setUP();
            $o->$func();
        } catch (Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $o->error();
            echo '<br>Exception: ', $e->getMessage(), "<br>";
        }
    }

    echo '<br><br>================   END FIRST PASS  ============<br><br>';


    // repeat tests which did fail
    if (!empty($o->error_functions)) {
    SmartyTests::$smarty->caching_type = 'file';
//    SmartyTests::$smarty->compiled_type = 'file';
    SmartyTests::$smarty->default_resource_type = 'file';
    SmartyTests::$smarty->clearAllCache();
    SmartyTests::$smarty->clearCompiledTemplate();
        $error_functions = $o->error_functions;
        $o->error_functions = array();

        foreach ($error_functions as $func) {
                $o->current_function = $func;
                $o->setUP();
                // You may set a debugger breakpoint below for debugging failing tests
                $o->$func();
         }
    }
    $function = array();

}
