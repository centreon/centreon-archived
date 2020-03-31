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

session_start();
require_once __DIR__ . '/../../../../bootstrap.php';
require_once '../functions.php';
require_once '../../../../config/centreon.config.php';
require_once '../../../class/centreonDB.class.php';
require_once '../../../class/centreon-partition/partEngine.class.php';
require_once '../../../class/centreon-partition/config.class.php';
require_once '../../../class/centreon-partition/mysqlTable.class.php';
require_once '../../../class/centreon-partition/options.class.php';

$return = array(
    'id' => 'dbpartitioning',
    'result' => 1,
    'msg' => ''
);

/* Create partitioned tables */
$database = new CentreonDB('centstorage');
$centreonDb = new CentreonDB('centreon');
$partEngine = new PartEngine();

if (!$partEngine->isCompatible($database)) {
    $return['msg'] = "[" . date(DATE_RFC822) . "] " .
        "CRITICAL: MySQL server is not compatible with partitionning. MySQL version must be greater or equal to 5.1\n";
    echo json_encode($return);
    exit;
}

$tables = array(
    'data_bin',
    'logs',
    'log_archive_host',
    'log_archive_service'
);

try {
    foreach ($tables as $table) {
        $config = new Config(
            $database,
            _CENTREON_PATH_ . '/config/partition.d/partitioning-' . $table . '.xml',
            $centreonDb
        );
        $mysqlTable = $config->getTable($table);

        // past partitions do not need to be created
        // it optimizes the time for partition process
        $partEngine->createParts($mysqlTable, $database, false);
    }
} catch (\Exception $e) {
    $return['msg'] = preg_replace('/\n/', "", $e->getMessage());
    echo json_encode($return);
    exit;
}

$return['result'] = 0;
echo json_encode($return);
exit;
