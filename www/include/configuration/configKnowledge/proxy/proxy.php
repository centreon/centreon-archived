<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/service.php $
 * SVN : $Id: service.php 8549 2009-07-01 16:20:26Z shotamchay $
 *
 */


ini_set("display_errors", "On");
$centreon_path = realpath(dirname(__FILE__) . '/../../../../../');
global $etc_centreon, $db_prefix;

require_once $centreon_path . "/config/centreon.config.php";

set_include_path(
    get_include_path() .
    PATH_SEPARATOR . $centreon_path . "www/class/centreon-knowledge/" .
    PATH_SEPARATOR . $centreon_path . "www/"
);

require_once "DB.php";
require_once "include/common/common-Func.php";
require_once "class/centreonLog.class.php";
require_once "class/centreonDB.class.php";
require_once "class/centreon-knowledge/procedures.class.php";
require_once "class/centreon-knowledge/procedures_DB_Connector.class.php";
require_once "class/centreon-knowledge/procedures_Proxy.class.php";

$modules_path = $centreon_path . "www/include/configuration/configKnowledge/";
require_once $modules_path . 'functions.php';

/*
 * DB connexion
 */
$pearDB = new CentreonDB();

$conf = getWikiConfig($pearDB);
$WikiURL = $conf['kb_wiki_url'];

/*
 * Check if user want host or service procedures
 */
if (isset($_GET["host_name"]) && isset($_GET["service_description"])) {
    $proxy = new procedures_Proxy($pearDB, $conf['kb_db_prefix'], $_GET["host_name"], $_GET["service_description"]);
} elseif (isset($_GET["host_name"])) {
    $proxy = new procedures_Proxy($pearDB, $conf['kb_db_prefix'], $_GET["host_name"], null);
}

if ($proxy->url != "") {
    header("Location: " . $proxy->url);
} else {
    if (isset($_GET["host_name"]) && isset($_GET["service_description"])) {
        header("Location: $WikiURL/?title=Service_:_" . $_GET["host_name"] . "_/_" . $_GET["service_description"]);
    } else {
        header("Location: $WikiURL/?title=Host_:_" . $_GET["host_name"]);
    }
}
exit();
