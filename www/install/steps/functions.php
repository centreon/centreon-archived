<?php
/**
 * Get template
 *  
 * @param string $dir directory of templates
 * @return Smarty
 */
function getTemplate($dir) {
    require_once '../../../GPL_LIB/Smarty/libs/Smarty.class.php';
    $template = new Smarty();
    $template->compile_dir = "../../../GPL_LIB/SmartyCache/compile";
    $template->config_dir = "../../../GPL_LIB/SmartyCache/config";
    $template->cache_dir = "../../../GPL_LIB/SmartyCache/cache";
    $template->template_dir = $dir;
    $template->caching = 0;
    $template->compile_check = true;
    $template->force_compile = true;
    return $template;
}

/**
 * Connect to database with user root
 * 
 * @return mixed
 */
function myConnect() {
    $pass = "";
    if (isset($_SESSION['root_password']) && $_SESSION['root_password']) {
        $pass = $_SESSION['root_password'];
    }
    $host = "localhost";
    if (isset($_SESSION['ADDRESS']) && $_SESSION['ADDRESS']) {
        $host = $_SESSION['ADDRESS'];
    }
    return mysql_connect($host, 'root', $pass);
}

/**
 * Replace macros
 * 
 * @param string $query
 * @return string
 */
function replaceInstallationMacros($query) {
    while (preg_match('/@([a-zA-Z0-9_]+)@/', $query, $matches)) {
        $macroValue = "";
        if (isset($_SESSION[$matches[1]])) {
            $macroValue = $_SESSION[$matches[1]];
        }
        $query = preg_replace('/@'.$matches[1].'@/', $macroValue, $query);
    }
    return $query;
}

/**
 * Split queries
 * 
 * @param string $file
 * @param string $delimiter 
 * @param CentreonDB $connector
 * @return boolean
 */
function splitQueries($file, $delimiter = ';', $connector = null) {
    set_time_limit(0);
    if (is_file($file) === true) {
        $file = fopen($file, 'r');
        if (is_resource($file) === true)
        {
            $query = array();
            while (feof($file) === false) {
                $query[] = fgets($file);
                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                    $query = trim(implode('', $query));
                    $query = replaceInstallationMacros($query);
                    if (is_null($connector)) {
                        if (mysql_query($query) === false) {
                            fclose($file);
                            return false;
                        }
                    } else {
                        $connector->query($query);
                    }
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                }
                if (is_string($query) === true) {
                    $query = array();
                }
            }
            return fclose($file);
        }
    }
    return false;
}

/**
 * Import file, mainly INSERT clauses
 * 
 * @param string $file
 * @return void
 */
function importFile($file) {
    mysql_query('BEGIN');
    if (false == splitQueries($file)) {
        $error = mysql_error();
        mysql_query('ROLLBACK');
        exitProcess(PROCESS_ID, 1, $error);
    }
    mysql_query('COMMIT');
}

/**
 * Exit process
 * 
 * @param string $id | name of the process
 * @param int $result | 0 = ok, 1 = nok
 * @param string $msg | error message
 */
function exitProcess($id, $result, $msg) {
    echo '{
        "id" : "'.$id.'",
        "result" : "'.$result.'",
        "msg" : "'.$msg.'"
        }';
    @mysql_close();
    exit;
}

/**
 * Exit upgrade process
 * 
 * @param int $result | 0 = ok, 1 = nok
 * @param string $current
 * @param string $next
 * @param string $msg | error message
 */
function exitUpgradeProcess($result, $current, $next, $msg) {
    echo '{
        "result" : "'.$result.'",
        "current" : "'.$current.'",
        "next" : "'.$next.'",
        "msg" : "'.$msg.'"
        }';
    exit;
}

/**
 * Get param lines from file
 * 
 * @param string $varPath
 * @param string $objectType
 * @return array
 */
function getParamLines($varPath, $objectType) {
    $contents = "";
    if ($handle = opendir($varPath)) {
        while (false !== ($object = readdir($handle))) {        
            if ($object == $objectType) {
                $contents = file_get_contents($varPath.'/'.$object);
            }
        }
        closedir($handle);
    }
    $lines = explode("\n", $contents);
    return $lines;
}
?>
