<?php
/**
 * Checks if line is sql comment
 * 
 * @param string $str
 * @return bool
 */
function isSqlComment($str) {
    if (substr(trim($str), 0, 2) == "--") {
        return true;
    }
    return false;
}

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
 * @param string $tmpFile | $tmpFile will store the number of executed queries sql script can be resumed from last failure
 * @return string | returns "0" if everything is ok, or returns error message
 */
function splitQueries($file, $delimiter = ';', $connector = null, $tmpFile = "") {
    set_time_limit(0);
    $count = 0;
    $start = 0;
    $fileName = basename($file);
    if (is_file($tmpFile)) {
        $start = file_get_contents($tmpFile);
    }
    if (is_file($file) === true) {
        $file = fopen($file, 'r');
        if (is_resource($file) === true)
        {
            $query = array();
            $line = 0;
            while (feof($file) === false) {
                $line++;
                $currentLine = fgets($file);
                if (false == isSqlComment($currentLine)) {
                    $query[] = $currentLine;
                }
                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                    $query = trim(implode('', $query));
                    $query = replaceInstallationMacros($query);
                    $count++;
                    if ($count > $start) {
                        if (is_null($connector)) {
                            if (mysql_query($query) === false) {
                                fclose($file);
                                return "$fileName Line $line:".mysql_error();
                            }
                        } else {
                            $res = $connector->query($query);
                            if (PEAR::isError($res)) {
                                return "$fileName Line $line:".$res->getMessage();
                            }
                        }
                        while (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                        flush();
                        file_put_contents($tmpFile, $count);
                    }
                }
                if (is_string($query) === true) {
                    $query = array();
                }
            }
            fclose($file);
            return "0";
        }
    }
    return _('File not found');
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
 * @return void
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

/**
 * Set session variables
 * 
 * @param array $conf_centreon
 * @return void
 */
function setSessionVariables($conf_centreon) {
    $_SESSION['INSTALL_DIR_CENTREON'] = $conf_centreon['centreon_dir'];
    $_SESSION['CENTREON_ETC'] = $conf_centreon['centreon_etc'];
    $_SESSION['BIN_MAIL'] = $conf_centreon['mail'];
    $_SESSION['MONITORINGENGINE_USER'] = $conf_centreon['monitoring_user'];
    $_SESSION['MONITORINGENGINE_GROUP'] = $conf_centreon['monitoring_group'];
    $_SESSION['MONITORINGENGINE_ETC'] = $conf_centreon['monitoring_etc'];
    $_SESSION['MONITORINGENGINE_PLUGIN'] = $conf_centreon['plugin_dir'];
    $_SESSION['CENTREON_LOG'] = $conf_centreon['centreon_log'];
    $_SESSION['CENTREON_RRD_DIR'] = $conf_centreon['centreon_dir_rrd'];
    $_SESSION['MONITORING_INIT_SCRIPT'] = $conf_centreon['monitoring_init_script'];
    $_SESSION['MONITORING_BINARY'] = $conf_centreon['monitoring_binary'];
    $_SESSION['CENTREON_VARLIB'] = $conf_centreon['centreon_varlib'];
}
?>
