<?php
/*
 * Copyright 2005-2019 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
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
global $etc_centreon;

require_once $centreon_path . "/config/centreon.config.php";

set_include_path(
    get_include_path() .
    PATH_SEPARATOR . $centreon_path . "www/class/centreon-knowledge/" .
    PATH_SEPARATOR . $centreon_path . "www/"
);

require_once "include/common/common-Func.php";
require_once "class/centreonLog.class.php";
require_once $centreon_path . '/bootstrap.php';
require_once "class/centreon-knowledge/procedures.class.php";
require_once "class/centreon-knowledge/ProceduresProxy.class.php";

$modules_path = $centreon_path . "www/include/configuration/configKnowledge/";
require_once $modules_path . 'functions.php';

/*
 * DB connexion
 */
$pearDB = $dependencyInjector['configuration_db'];

try {
    $wikiConf = getWikiConfig($pearDB);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit();
}

$wikiURL = $wikiConf['kb_wiki_url'];
$proxy = new ProceduresProxy($pearDB);

/*
 * Check if user want host or service procedures
 */
$url = null;

if (isset($_GET["host_name"])) {
    $hostName = filter_var($_GET['host_name'], FILTER_SANITIZE_STRING);
}
if (isset($_GET["service_description"])) {
    $serviceDescription = filter_var($_GET['service_description'], FILTER_SANITIZE_STRING);
}

if (!empty($hostName) && !empty($serviceDescription)) {
    $url = $proxy->getServiceUrl($hostName, $serviceDescription);
} elseif (!empty($hostName)) {
    $url = $proxy->getHostUrl($hostName);
}

if (!empty($url)) {
    header("Location: " . $url);
} elseif (!empty($hostName) && !empty($serviceDescription)) {
    header("Location: $wikiURL/?title=Service_:_" . $hostName . "_/_" . $serviceDescription);
} elseif (!empty($hostname)) {
    header("Location: $wikiURL/?title=Host_:_" . $hostName);
} else {
    header("Location: " . $wikiURL);
}

exit();
