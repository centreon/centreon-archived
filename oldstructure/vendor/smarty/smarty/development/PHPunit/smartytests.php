<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */

define ('SMARTY_DIR', '../../distribution/libs/');

require_once SMARTY_DIR . 'SmartyBC.class.php';

/**
 * class for running test suite
 */
class smartytests extends PHPUnit_Framework_TestSuite
{
    static $smarty = null ;
    static $smartyBC = null ;

    public function __construct()
    {
        SmartyTests::$smarty = new Smarty();
        SmartyTests::$smartyBC = new SmartyBC();
    }

    protected static function _init($smarty)
    {
        $smarty->setTemplateDir('.' . DS . 'templates' . DS);
        $smarty->setCompileDir('.' . DS . 'templates_c' . DS);
        $smarty->setPluginsDir(SMARTY_PLUGINS_DIR);
        $smarty->setCacheDir('.' . DS . 'cache' . DS);
        $smarty->setConfigDir('.' . DS . 'configs' . DS);
        $smarty->template_objects = array();
        $smarty->config_vars = array();
        Smarty::$global_tpl_vars = array();
        $smarty->template_functions = array();
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->force_cache = false;
        $smarty->auto_literal = true;
        $smarty->caching = false;
        $smarty->debugging = false;
        Smarty::$_smarty_vars = array();
        $smarty->registered_plugins = array();
        $smarty->default_plugin_handler_func = null;
        $smarty->registered_objects = array();
        $smarty->default_modifiers = array();
        $smarty->registered_filters = array();
        $smarty->autoload_filters = array();
        $smarty->escape_html = false;
        $smarty->use_sub_dirs = false;
        $smarty->config_overwrite = true;
        $smarty->config_booleanize = true;
        $smarty->config_read_hidden = true;
        $smarty->security_policy = null;
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';
        $smarty->php_handling = Smarty::PHP_PASSTHRU;
        $smarty->enableSecurity();
        $smarty->error_reporting = null;
        $smarty->error_unassigned = true;
        $smarty->caching_type = 'file';
        $smarty->cache_locking = false;
        $smarty->cache_id = null;
        $smarty->compile_id = null;
        $smarty->default_resource_type = 'file';
    }

    static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        Smarty_Resource::$sources = array();
        Smarty_Resource::$compileds = array();
//        Smarty_Resource::$resources = array();
        SmartyTests::$smartyBC->registerPlugin('block','php','smarty_php_tag');
    }
    /**
     * look for test units and run them
     */
    static function suite()
    {
        $testorder = array('CoreTests', 'ClearCompiledTests', 'ClearCacheTests', 'StringResourceTests', 'FileResourceTests' ,'DoubleQuotedStringTests',  'CompileAssignTests', 'AttributeTests');
        $smarty_libs_dir = dirname(__FILE__) . '/../../distribution/libs';
        if (method_exists('PHPUnit_Util_Filter', $smarty_libs_dir)) {
            // Older versions of PHPUnit did not have this function,
            // which is used when determining which PHP files are
            // included in the PHPUnit code coverage result.
            PHPUnit_Util_Filter::addDirectoryToWhitelist($smarty_libs_dir);
            PHPUnit_Util_Filter::removeDirectoryFromWhitelist('./');
            // PHPUnit_Util_Filter::addDirectoryToWhitelist('../libs/plugins');
        }
        $suite = new self('Smarty 3 - Unit Tests Report');
        // load test which should run in specific order
        foreach ($testorder as $class) {
            require_once $class . '.php';
            $suite->addTestSuite($class);
        }

        $_classes = array();
        foreach (new DirectoryIterator(dirname(__FILE__)) as $file) {
            if (!$file->isDot() && !$file->isDir() && !in_array((string) $file, array('smartytests.php','smartytestssingle.php','smartytestsfile.php','smartytestdebug.php','smartytestdebug.inc.php')) && substr((string) $file, -4) === '.php') {
                $class = basename($file, '.php');
                if (!in_array($class, $testorder)) {
                    require_once $file->getPathname();
                    // to have an optional test suite, it should implement a static function isRunnable
                    // that returns true only if all the conditions are met to run it successfully, for example
                    // it can check that an external library is present
                    if (!method_exists($class, 'isRunnable') || call_user_func(array($class, 'isRunnable'))) {
                        $_classes[] = $class;
                    }
                }
            }
        }
        sort($_classes);
        foreach ($_classes as $class) {
            $suite->addTestSuite($class);
        }

        return $suite;
    }
}
