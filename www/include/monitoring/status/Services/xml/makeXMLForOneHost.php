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

/**
 * Require Centreon Config file
 */
require_once realpath(dirname(__FILE__) . "/../../../../../../config/centreon.config.php");

include_once $centreon_path . "www/class/centreonUtils.class.php";

/**
 * Include Monitoring Classes
 */
include_once _CENTREON_PATH_ . "www/class/centreonXMLBGRequest.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonLang.class.php";

/*
 * Create XML Request Objects
 */
CentreonSession::start(1);
$obj = new CentreonXMLBGRequest(session_id(), 1, 1, 0, 1);

/**
 * Manage Session
 */

$centreon = $_SESSION['centreon'];

/**
 * Check Security
 */
if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    ;
} else {
    print "Bad Session ID";
    exit();
}

/** **************************************************
 * Enable Lang Object
 */
$centreonlang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonlang->bindLang();

/** **************************************************
 * Check Arguments From GET tab
 */
$host_id        = $obj->checkArgument("host_id", $_GET, 0);
$enable         = $obj->checkArgument("enable", $_GET, "");
$disable        = $obj->checkArgument("disable", $_GET, "disable");
$dateFormat     = $obj->checkArgument("date_time_format_status", $_GET, "Y/m/d H:i:s");

/** ***************************************************
 * Get Host status
 */
$rq1 =  " SELECT h.state," .
        " h.address," .
        " h.name," .
        " h.alias," .
        " i.name AS poller, " .
        " h.perfdata," .
        " h.check_attempt," .
        " h.state_type," .
        " h.last_check, " .
        " h.next_check, " .
        " h.latency," .
        " h.execution_time," .
        " h.last_state_change," .
        " h.last_notification," .
        " h.next_host_notification," .
        " h.last_hard_state_change," .
        " h.last_hard_state," .
        " h.last_time_up," .
        " h.last_time_down," .
        " h.last_time_unreachable," .
        " h.notification_number," .
        " h.scheduled_downtime_depth," .
        " h.output," .
        " h.notes," .
        " h.notify," .
        " h.event_handler_enabled," .
        " h.icon_image, " .
        " h.timezone" .
        " FROM hosts h, instances i " .
        " WHERE h.host_id = " . $host_id .
        " AND h.instance_id = i.instance_id " .
        " LIMIT 1";
/*
 * Request
 */
$DBRESULT = $obj->DBC->query($rq1);

/*
 * Start Buffer
 */
$obj->XML->startElement("reponse");
if ($data = $DBRESULT->fetchRow()) {
    /* Split the plugin_output */
    $outputLines = explode("\n", $data['output']);
    $pluginShortOuput = $outputLines[0];

    $duration = "";
    if ($data["last_state_change"] > 0) {
        $duration = CentreonDuration::toString(time() - $data["last_state_change"]);
    }

    if ($data["icon_image"] == "") {
        $data["icon_image"] = "./img/icons/host.png";
    } else {
        $data["icon_image"] = "./img/media/" . $data["icon_image"];
    }

    $last_notification = "N/A";
    if ($data["last_notification"] > 0) {
        $last_notification = $data["last_notification"];
    }

    $next_notification = "N/A";
    if ($data["next_host_notification"] > 0) {
        $next_notification = $data["next_host_notification"];
    }

    $obj->XML->writeElement("hostname", CentreonUtils::escapeSecure($data["name"]), false);
    $obj->XML->writeElement("hostalias", CentreonUtils::escapeSecure($data["alias"]), false);
    $obj->XML->writeElement("address", CentreonUtils::escapeSecure($data["address"]));
    $obj->XML->writeElement("poller_name", _("Polling instance"), 0);
    $obj->XML->writeElement("poller", $data["poller"]);
    $obj->XML->writeElement("color", $obj->backgroundHost[$data["state"]]);
    $obj->XML->startElement("current_state");
    $obj->XML->writeAttribute("color", $obj->colorHost[$data["state"]]);
    $obj->XML->text(_($obj->statusHost[$data["state"]]), false);
    $obj->XML->endElement();
    $obj->XML->writeElement("current_state_name", _("Host Status"), 0);
    $obj->XML->startElement("plugin_output");
    $obj->XML->writeAttribute("name", _("Status Information"));
    $obj->XML->text(CentreonUtils::escapeSecure($pluginShortOuput), 0);
    $obj->XML->endElement();
    $obj->XML->startElement("current_attempt");
    $obj->XML->writeAttribute("name", _("Current Attempt"));
    $obj->XML->text($data["check_attempt"]);
    $obj->XML->endElement();
    $obj->XML->writeElement("state_type", $obj->stateTypeFull[$data["state_type"]]);
    $obj->XML->writeElement("state_type_name", _("State Type"), 0);
    $obj->XML->writeElement("last_check", $obj->GMT->getDate($dateFormat, $data["last_check"]));
    $obj->XML->writeElement("last_check_name", _("Last Check"), 0);
    $obj->XML->writeElement("last_state_change", $obj->GMT->getDate($dateFormat, $data["last_state_change"]));
    $obj->XML->writeElement("last_state_change_name", _("Last State Change"), 0);
    $obj->XML->writeElement("duration", $duration);
    $obj->XML->writeElement("duration_name", _("Current State Duration"), 0);
    $obj->XML->writeElement("last_notification", $obj->GMT->getDate($dateFormat, $last_notification));
    $obj->XML->writeElement("last_notification_name", _("Last Notification"), 0);
    $obj->XML->writeElement("current_notification_number", $data["notification_number"]);
    $obj->XML->writeElement("current_notification_number_name", _("Current Notification Number"), 0);
    $obj->XML->writeElement("is_downtime", ($data["scheduled_downtime_depth"] > 0 ? $obj->en[1] : $obj->en[0]));
    $obj->XML->writeElement("is_downtime_name", _("In Scheduled Downtime?"), 0);
    $obj->XML->writeElement("last_update", $obj->GMT->getDate($dateFormat, time()));
    $obj->XML->writeElement("last_update_name", _("Last Update"), 0);
    $obj->XML->writeElement("ico", $data["icon_image"]);
    $obj->XML->writeElement("timezone_name", _("Timezone"));
    $obj->XML->writeElement("timezone", str_replace(':', '', $data["timezone"]));

    /* Last State Info */
    if ($data["state"] == 0) {
        $status = _('DOWN');
        $status_date = 0;
        if (isset($data["last_time_down"]) && $status_date < $data["last_time_down"]) {
            $status_date = $obj->GMT->getDate($dateFormat, $data["last_time_down"]);
            $status = _('DOWN');
        }
        if (isset($data["last_time_unreachable"]) && $status_date < $data["last_time_unreachable"]) {
            $status_date = $obj->GMT->getDate($dateFormat, $data["last_time_unreachable"]);
            $status = _('UNREACHABLE');
        }
    } else {
        $status = _('OK');
        $status_date = 0;
        if ($data["last_time_up"]) {
            $status_date = $obj->GMT->getDate($dateFormat, $data["last_time_up"]);
        }
    }
    if ($status_date == 0) {
        $status_date = '-';
    }
    $obj->XML->writeElement("last_time_name", _("Last time in "), 0);
    $obj->XML->writeElement("last_time", $status_date, 0);
    $obj->XML->writeElement("last_time_status", $status, 0);

    $obj->XML->startElement("notes");
    $obj->XML->writeAttribute("name", _("Notes"));
    $obj->XML->text(CentreonUtils::escapeSecure($data['notes']));
    $obj->XML->endElement();
} else {
    $obj->XML->writeElement("infos", "none");
}
$DBRESULT->free();

/*
 * Translations
 */
$obj->XML->writeElement("tr1", _("Check information"), 0);
$obj->XML->writeElement("tr2", _("Notification information"), 0);
$obj->XML->writeElement("tr3", _("Last Status Change"), 0);
$obj->XML->writeElement("tr4", _("Extended information"), 0);
$obj->XML->writeElement("tr5", _("Status Information"), 0);

/*
 * End buffer
 */
$obj->XML->endElement();


/*
 * Send Header
 */
$obj->header();

/*
 * Send XML
 */
$obj->XML->output();
