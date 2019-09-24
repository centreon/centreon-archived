<?php
/**
 * Checks if line is sql comment
 *
 * @param string $str
 * @return bool
 */
function isSqlComment($str)
{
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
function getTemplate($dir)
{
    $libDir = __DIR__ . '/../../../GPL_LIB';
    $smartyDir = __DIR__ . '/../../../vendor/smarty/smarty/';
    require_once $smartyDir . 'libs/Smarty.class.php';
    $template = new \Smarty();
    $template->compile_dir = $libDir . '/SmartyCache/compile';
    $template->config_dir = $libDir . '/SmartyCache/config';
    $template->cache_dir = $libDir . '/SmartyCache/cache';
    $template->plugins_dir[] = $libDir . "/smarty-plugins";
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
function myConnect()
{
    $user = "root";
    if (!empty($_SESSION['root_user'])) {
        $user = $_SESSION['root_user'];
    }
    $pass = "";
    if (!empty($_SESSION['root_password'])) {
        $pass = $_SESSION['root_password'];
    }
    $host = "localhost";
    if (isset($_SESSION['ADDRESS']) && $_SESSION['ADDRESS']) {
        $host = $_SESSION['ADDRESS'];
    }
    $port = "3306";
    if (isset($_SESSION['DB_PORT']) && $_SESSION['DB_PORT']) {
        $port = $_SESSION['DB_PORT'];
    }
    return new \PDO('mysql:host=' . $host . ';port=' . $port, $user, $pass);
}

/**
 * Replace macros
 *
 * @param string $query
 * @return string
 */
function replaceInstallationMacros($query, $macros = array())
{
    while (preg_match('/@([a-zA-Z0-9_]+)@/', $query, $matches)) {
        $macroValue = "";
        if ($matches[1] == 'MAILER') {
            $macroValue = '-MAILER-';
        } elseif (isset($macros[$matches[1]])) {
            $macroValue = $macros[$matches[1]];
        } elseif (isset($_SESSION[$matches[1]])) {
            $macroValue = $_SESSION[$matches[1]];
        }

        $query = preg_replace('/@' . $matches[1] . '@/', $macroValue, $query);
    }

    $query = str_replace('-MAILER-', '@MAILER@', $query);

    return $query;
}

/**
 * Split queries
 *
 * @param string $file
 * @param string $delimiter
 * @param CentreonDB $connector
 * @param string $tmpFile | $tmpFile will store the number of executed queries sql script
 * @return string | returns "0" if everything is ok, or returns error message
 */
function splitQueries($file, $delimiter = ';', $connector = null, $tmpFile = "", $macros = array())
{
    if (is_null($connector)) {
        $connector = myConnect();
    }

    set_time_limit(0);
    $count = 0;
    $start = 0;
    $fileName = basename($file);
    if ($tmpFile != '' && is_file($tmpFile)) {
        $start = file_get_contents($tmpFile);
    }
    if (is_file($file) === true) {
        $file = fopen($file, 'r');
        if (is_resource($file) === true) {
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
                    $query = replaceInstallationMacros($query, $macros);
                    $count++;
                    if ($count > $start) {
                        try {
                            $result = $connector->query($query);
                            if (!$result) {
                                throw new \Exception('Cannot execute query : ' . $query);
                            }
                        } catch (\Exception $e) {
                            return "$fileName Line $line:" . $e->getMessage();
                        }
                        while (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                        flush();
                        if ($tmpFile != '') {
                            file_put_contents($tmpFile, $count);
                        }
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
function importFile($db, $file)
{
    $db->beginTransaction();
    try {
        splitQueries($db, $file);
        $db->commit();
    } catch (\PDOException $e) {
        $db->rollBack();
        exitProcess(PROCESS_ID, 1, $e->getMessage());
    }
}

/**
 * Exit process
 *
 * @param string $id | name of the process
 * @param int $result | 0 = ok, 1 = nok
 * @param string $msg | error message
 */
function exitProcess($id, $result, $msg)
{
    $msg = str_replace('"', '\"', $msg);
    $msg = str_replace('\\', '\\\\', $msg);

    echo '{
        "id" : "' . $id . '",
        "result" : "' . $result . '",
        "msg" : "' . $msg . '"
        }';

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
function exitUpgradeProcess($result, $current, $next, $msg)
{
    $msg = str_replace('"', '\"', $msg);
    $msg = str_replace('\\', '\\\\', $msg);
    echo '{
        "result" : "' . $result . '",
        "current" : "' . $current . '",
        "next" : "' . $next . '",
        "msg" : "' . $msg . '"
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
function getParamLines($varPath, $objectType)
{
    $contents = "";
    if ($handle = opendir($varPath)) {
        while (false !== ($object = readdir($handle))) {
            if ($object == $objectType) {
                $contents = file_get_contents($varPath . '/' . $object);
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
function setSessionVariables($conf_centreon)
{
    $_SESSION['INSTALL_DIR_CENTREON'] = $conf_centreon['centreon_dir'];
    $_SESSION['CENTREON_ETC'] = $conf_centreon['centreon_etc'];
    $_SESSION['BIN_MAIL'] = $conf_centreon['mail'];
    $_SESSION['BIN_RRDTOOL'] = $conf_centreon['rrdtool_dir'];
    $_SESSION['MONITORINGENGINE_USER'] = $conf_centreon['monitoring_user'];
    $_SESSION['MONITORINGENGINE_GROUP'] = $conf_centreon['monitoring_group'];
    $_SESSION['MONITORINGENGINE_ETC'] = $conf_centreon['monitoring_etc'];
    $_SESSION['MONITORINGENGINE_PLUGIN'] = $conf_centreon['plugin_dir'];
    $_SESSION['CENTREON_LOG'] = $conf_centreon['centreon_log'];
    $_SESSION['CENTREON_RRD_DIR'] = $conf_centreon['centreon_dir_rrd'];
    $_SESSION['MONITORING_BINARY'] = $conf_centreon['monitoring_binary'];
    $_SESSION['CENTREON_VARLIB'] = $conf_centreon['centreon_varlib'];
    $_SESSION['MONITORING_VAR_LOG'] = $conf_centreon['monitoring_varlog'];
    $_SESSION['CENTREON_ENGINE_CONNECTORS'] = $conf_centreon['centreon_engine_connectors'];
    $_SESSION['CENTREON_ENGINE_LIB'] = $conf_centreon['centreon_engine_lib'];
    $_SESSION['CENTREONBROKER_CBMOD'] = $conf_centreon['centreonbroker_cbmod'];
    $_SESSION['CENTREONPLUGINS'] = $conf_centreon['centreon_plugins'];
}

function getDatabaseVariable($db, $variable)
{
    $query = "SHOW VARIABLES LIKE '" . $variable . "'";
    $result = $db->query($query);

    $value = null;
    while ($row = $result->fetch()) {
        $value = $row['Value'];
    }
    $result->closeCursor();

    return $value;
}
