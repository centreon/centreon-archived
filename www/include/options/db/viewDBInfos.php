<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit();
}

require_once './class/centreonDB.class.php';
require_once './class/centreon-partition/partEngine.class.php';
require_once './class/centreon-partition/config.class.php';
require_once './class/centreon-partition/mysqlTable.class.php';
require_once './class/centreon-partition/options.class.php';

/*
 * Get Properties
 */
$dataCentreon = $pearDB->getProperties();
$dataCentstorage = $pearDBO->getProperties();

/*
 * Get partitioning informations
 */
$partEngine = new PartEngine();

$tables = array(
    'data_bin',
    'logs',
    'log_archive_host',
    'log_archive_service'
);

$partitioningInfos = array();
foreach ($tables as $table) {
    $mysqlTable = new MysqlTable($pearDBO, $table, $conf_centreon['dbcstg']);
    $partitioningInfos[$table] = $partEngine->listParts($mysqlTable, $pearDBO, false);
}

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl("./include/options/db/", $tpl);

$tpl->assign('conf_centreon', $conf_centreon);
$tpl->assign('dataCentreon', $dataCentreon);
$tpl->assign('dataCentstorage', $dataCentstorage);
$tpl->assign('partitioning', $partitioningInfos);

$tpl->display("viewDBInfos.ihtml");
