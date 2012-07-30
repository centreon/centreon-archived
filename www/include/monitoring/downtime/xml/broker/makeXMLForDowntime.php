<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/xml/ndo/makeXMLForOneHost.php $
 * SVN : $Id: makeXMLForOneHost.php 12188 2011-05-04 15:45:01Z shotamchay $
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
$dbb = new CentreonDB("centstorage");

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

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession($sid, $pearDB);

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
if (!$service_id) {
    $query = "SELECT author, start_time, end_time, comment_data, duration, fixed
    		  FROM downtimes
    		  WHERE host_id = " . CentreonDB::escape($host_id) . "
    		  AND service_id IS NULL
    		  AND cancelled = 0
    		  AND end_time > UNIX_TIMESTAMP(NOW())
    		  ORDER BY start_time";
} else {
    $query = "SELECT author, start_time, end_time, comment_data, duration, fixed
    		  FROM downtimes
    		  WHERE host_id = " . CentreonDB::escape($host_id) . "
    		  AND service_id = " . CentreonDB::escape($service_id) . "
    		  AND cancelled = 0
    		  AND end_time > UNIX_TIMESTAMP(NOW())
    		  ORDER BY start_time";
}
$res = $dbb->query($query);
$rowClass = "list_one";
while ($row = $res->fetchRow()) {
    $row['comment_data'] = strip_tags($row['comment_data']);
    $xml->startElement('dwt');
    $xml->writeAttribute('class', $rowClass);
    $xml->writeElement('author', $row['author']);
    $xml->writeElement('start', $centreonGMT->getDate('d/m/Y H:i:s', $row['start_time']));
    $xml->writeElement('end', $centreonGMT->getDate('d/m/Y H:i:s', $row['end_time']));
    $xml->writeElement('comment', $row['comment_data']);
    $xml->writeElement('duration', CentreonDuration::toString($row['duration']));
    $xml->writeElement('fixed', $row['fixed'] ? _('Yes') : _('No'));
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