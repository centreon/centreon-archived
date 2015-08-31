<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

include_once('@CENTREON_ETC@/centreon.conf.php');
require_once $centreon_path . '/www/class/centreonDB.class.php';
require_once $centreon_path . '/www/include/common/common-Func.php';

/*
 * Validate the session
 */
session_start();
$db = new CentreonDB();
if (isset($_GET['sid'])) {
    $res = $db->query('SELECT * FROM session WHERE session_id = \'' . CentreonDB::escape($_GET['sid']) . '\'');
    if (!$res->fetchRow()) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
        exit;
    }
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
    exit;
}

/**
 * Resolving host name
 */
echo gethostbyname($_GET['hostName']);