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

include_once "@CENTREON_ETC@/centreon.conf.php";
include_once $centreon_path . "www/class/centreonDuration.class.php";
include_once $centreon_path . "www/class/centreonGMT.class.php";
include_once $centreon_path . "www/class/centreonXML.class.php";
include_once $centreon_path . "www/class/centreonDB.class.php";
include_once $centreon_path . "www/class/centreonSession.class.php";
include_once $centreon_path . "www/class/centreon.class.php";
include_once $centreon_path . "www/class/centreonLang.class.php";
include_once $centreon_path . "www/include/common/common-Func.php";

session_start();
$oreon = $_SESSION['centreon'];

$db = new CentreonDB();
$pearDB = $db;
$dbb = new CentreonDB("ndo");

$centreonlang = new CentreonLang($centreon_path, $oreon);
$centreonlang->bindLang();

if (isset($_GET["sid"])){
    $sid = $_GET["sid"];
    $res = $db->query("SELECT * FROM session WHERE session_id = '".CentreonDB::escape($sid)."'");
    if (!$session = $res->fetchRow()) {
        get_error('bad session id');
    }
} else {
    get_error('need session id !');
}

(isset($_GET["hid"])) ? $host_id = CentreonDB::escape($_GET["hid"]) : $host_id = 0;
(isset($_GET["svc_id"])) ? $service_id = CentreonDB::escape($_GET["svc_id"]) : $service_id = 0;

if ($service_id) {
    $objectId = $service_id;
} else {
    $objectId = $host_id;
}

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession($sid, $pearDB);

$prefix = "";
$query = "SELECT db_prefix FROM cfg_ndo2db WHERE activate = '1' LIMIT 1";
$res = $db->query($query);
if ($res->numRows()) {
    $row = $res->fetchRow();
    $prefix = $row['db_prefix'];
}

/**
 * Start Buffer
 */
$xml = new CentreonXML();
$xml->startElement("response");

$xml->startElement("label");
$xml->writeElement('author', _('Author'));
$xml->writeElement('fixed', _('Fixed'));
$xml->writeElement('start', _('Start Time'));
$xml->writeElement('end', _('End Time'));
$xml->writeElement('comment', _('Comment'));
$xml->endElement();

/**
 * Retrieve info
 */
if ($objectId) {
    $query = "SELECT author_name,
    				 UNIX_TIMESTAMP(scheduled_start_time) as start_time,
    				 UNIX_TIMESTAMP(scheduled_end_time) as end_time,
    				 comment_data,
    				 duration,
    				 is_fixed
    		  FROM ".$prefix."downtimehistory
    		  WHERE object_id = " . CentreonDB::escape($objectId) . "
    		  AND was_cancelled = 0
    		  AND UNIX_TIMESTAMP(scheduled_end_time) > UNIX_TIMESTAMP(NOW())
    		  ORDER BY scheduled_start_time";
}
$res = $dbb->query($query);
$rowClass = "list_one";
while ($row = $res->fetchRow()) {
    $row['comment_data'] = strip_tags($row['comment_data']);
    $xml->startElement('dwt');
    $xml->writeAttribute('class', $rowClass);
    $xml->writeElement('author', $row['author_name']);
    $xml->writeElement('start', $centreonGMT->getDate('d/m/Y H:i:s', $row['start_time']));
    $xml->writeElement('end', $centreonGMT->getDate('d/m/Y H:i:s', $row['end_time']));
    $xml->writeElement('comment', $row['comment_data']);
    $xml->writeElement('duration', CentreonDuration::toString($row['duration']));
    $xml->writeElement('fixed', $row['is_fixed'] ? _('Yes') : _('No'));
    $xml->endElement();
    $rowClass == "list_one" ? $rowClass = "list_two" : $rowClass = "list_one";
}

/*
 * End buffer
 */
$xml->endElement();
header('Content-type: text/xml; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

/*
 * Print Buffer
 */
$xml->output();
?>