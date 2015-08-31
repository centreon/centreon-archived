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

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $centreon_path . "www/class/centreonWidget.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonUser.class.php";

session_start();

if (!isset($_SESSION['centreon'])) {
    exit;
}

$centreon = $_SESSION['centreon'];
$action = $_REQUEST['action'];
$db = new CentreonDB();
if (CentreonSession::checkSession(session_id(), $db) == 0) {
    exit;
}
$widgetObj = new CentreonWidget($centreon, $db);
try {
    echo $widgetObj->rename($_REQUEST);
} catch (CentreonWidgetException $e) {
    echo $e->getMessage();
}
?>